<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use DB;
use Storage;
use App\Models\User;

class AdminController extends Controller
{
    public function send_message()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $this->auth();
        $admin = auth()->user();

        $id = request()->id ?? 0;
        $message = request()->message ?? "";
        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            $date_time_zone = new \DateTimeZone($time_zone);
        }

        $user = DB::table("users")
            ->where("id", "=", $id)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User not found."
            ]);
        }

        $message_arr = [
            "message" => $message,
            "sender_id" => $admin->id,
            "receiver_id" => $user->id,
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];
        
        $message_arr["id"] = DB::table("messages")
            ->insertGetId($message_arr);

        if (!empty($time_zone))
        {
            $date_time = new \DateTime($message_arr["created_at"]);
            $date_time->setTimezone($date_time_zone);
            $message_arr["created_at"] = $date_time->format("d M, Y h:i:s a");
        }

        $message_arr["attachments"] = [];
        if (request()->file("attachments"))
        {
            foreach (request()->file("attachments") as $attachment)
            {
                $file_path = "messages/" . $message_arr["id"] . "/" . time() . "-" . $attachment->getClientOriginalName();
                $attachment->storeAs("/public", $file_path);

                DB::table("message_attachments")
                    ->insertGetId([
                        "message_id" => $message_arr["id"],
                        "path" => $file_path,
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);

                array_push($message_arr["attachments"], url("/storage/" . $file_path));
            }
        }

        DB::table("notifications")
            ->insertGetId([
                "user_id" => $user->id,
                "title" => "New message",
                "content" => "A new message has been received.",
                "type" => "new_message",
                "table_id" => $message_arr["id"],
                "is_read" => 0,
                "created_at" => now()->utc(),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Message has been sent.",
            "message_obj" => (object) $message_arr
        ]);
    }

    public function fetch_messages()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $this->auth();
        $admin = auth()->user();
        $id = request()->id ?? 0;
        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            $date_time_zone = new \DateTimeZone($time_zone);
        }

        $messages = DB::table("messages")
            ->leftJoin("message_attachments", "message_attachments.message_id", "=", "messages.id")
            ->join("users AS sender", "sender.id", "=", "messages.sender_id")
            ->where("sender_id", "=", $id)
            ->select("messages.*", "sender.name AS sender_name", "message_attachments.path")
            ->orWhere("receiver_id", "=", $id)
            ->orderBy("messages.id", "desc")
            ->get();

        $messages_arr = [];
        $message_ids = [];
        foreach ($messages as $message)
        {
            if (!empty($time_zone))
            {
                $date_time = new \DateTime($message->created_at);
                $date_time->setTimezone($date_time_zone);
                $message->created_at = $date_time->format("d M, Y h:i:s a");
            }

            $message_obj = [
                "id" => $message->id,
                "sender_id" => $message->sender_id,
                "sender_name" => $message->sender_name,
                "message" => $message->message ?? "",
                "attachments" => [],
                "created_at" => $message->created_at
            ];

            $index = -1;
            for ($a = 0; $a < count($messages_arr); $a++)
            {
                if ($messages_arr[$a]["id"] == $message->id)
                {
                    $index = $a;
                    break;
                }
            }

            if ($message->path && Storage::exists("/public/" . $message->path))
            {
                array_push($message_obj["attachments"], url("/storage/" . $message->path));
            }

            if ($index > -1)
            {
                if ($message->path && Storage::exists("/public/" . $message->path))
                {
                    array_push($messages_arr[$index]["attachments"], url("/storage/" . $message->path));
                }
            }
            else
            {
                array_push($messages_arr, (array) $message_obj);
                array_push($message_ids, $message->id);
            }
        }

        $notifications_count = 0;
        $notifications = DB::table("notifications")
            ->where("user_id", "=", $admin->id)
            ->where("type", "=", "new_message")
            ->whereIn("table_id", $message_ids)
            ->where("is_read", "=", 0);

        $notifications_count = $notifications->count();

        $notifications->update([
                "is_read" => 1,
                "updated_at" => now()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "messages" => $messages_arr,
            "notifications_count" => $notifications_count
        ]);
    }

    public function fetch_contacts()
    {
        $this->auth();
        $admin = auth()->user();
        $search = request()->search ?? "";

        $new_messages = DB::table("notifications")
            ->where("user_id", "=", $admin->id)
            ->where("is_read", "=", 0)
            ->where("type", "=", "new_message")
            ->get();

        $messages = DB::table("messages")
            ->where("sender_id", "=", $admin->id)
            ->orWhere("receiver_id", "=", $admin->id)
            ->get();

        $user_ids = [];
        $last_message = [];
        $last_message_date = [];
        $user_notifications = [];

        foreach ($messages as $message)
        {
            if ($message->sender_id == $admin->id)
            {
                $last_message[$message->receiver_id] = $message->message ?? "";
                $last_message_date[$message->receiver_id] = date("d M", strtotime($message->created_at)) ?? "";
                array_push($user_ids, $message->receiver_id);
            }
            else
            {
                $last_message[$message->sender_id] = $message->message ?? "";
                $last_message_date[$message->sender_id] = date("d M", strtotime($message->created_at)) ?? "";
                array_push($user_ids, $message->sender_id);
            }

            $user_notifications[$message->sender_id] = 0;
            foreach ($new_messages as $new_message)
            {
                if ($new_message->table_id == $message->id)
                {
                    $user_notifications[$message->sender_id]++;
                    break;
                }
            }
        }
        $user_ids = array_unique($user_ids);

        $users = DB::table("users")
            ->whereIn("id", $user_ids);

        if (!empty($search))
        {
            $users = $users->where(function ($query) use ($search) {
                $query->where("name", "LIKE", "%" . $search . "%")
                    ->orWhere("email", "LIKE", "%" . $search . "%")
                    ->orWhere("type", "=", $search);
            });
        }
        $users = $users->get();

        $users_arr = [];
        foreach ($users as $user)
        {
            $user_obj = [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "profile_image" => url("/storage/" . $user->profile_image),
                "last_message" => "",
                "last_message_date" => "",
                "user_notifications" => 0
            ];

            if (isset($last_message[$user->id]))
            {
                $user_obj["last_message"] = $last_message[$user->id];
            }

            if (isset($last_message_date[$user->id]))
            {
                $user_obj["last_message_date"] = $last_message_date[$user->id];
            }

            if (isset($user_notifications[$user->id]))
            {
                $user_obj["user_notifications"] = $user_notifications[$user->id];
            }

            array_push($users_arr, $user_obj);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "users" => $users_arr
        ]);
    }

    public function add_user()
    {
        $validator = Validator::make(request()->all(), [
            "name" => "required",
            "email" => "required",
            "password" => "required",
            "type" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth();
        $name = request()->name ?? "";
        $email = request()->email ?? "";
        $password = request()->password ?? "";
        $type = request()->type ?? "";

        $user = DB::table("users")
            ->where("email", "=", $email)
            ->first();

        if ($user != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User already exists."
            ]);
        }

        DB::table("users")
            ->insertGetId([
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "type" => $type,
                "email_verified_at" => now()->utc(),
                "created_at" => now()->utc(),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "User has been added."
        ]);
    }

    public function change_user_password()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "password" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth();
        $id = request()->id ?? 0;
        $password = request()->password ?? "";

        $user = DB::table("users")
            ->where("id", "=", $id)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User not found."
            ]);
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Password has been set."
        ]);
    }

    public function update_user()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "name" => "required",
            "type" => "required",
            "storage_unit" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth();
        $id = request()->id ?? 0;
        $name = request()->name ?? "";
        $type = request()->type ?? "";
        $storage_total = request()->storage_total ?? 0;
        $storage_unit = request()->storage_unit ?? "";

        if (!in_array($storage_unit, ["kb", "mb", "gb"]))
        {
            return response()->json([
                "status" => "error",
                "message" => "In-valid storage unit."
            ]);
        }

        if ($storage_unit == "kb")
        {
            $storage_total = $storage_total * 1024;
        }
        else if ($storage_unit == "mb")
        {
            $storage_total = $storage_total * 1048576;
        }
        else if ($storage_unit == "gb")
        {
            $storage_total = $storage_total * 1073741824;
        }

        $user = DB::table("users")
            ->where("type", "!=", "super_admin")
            ->where("id", "=", $id)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User not found."
            ]);
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "name" => $name,
                "type" => $type,
                "storage_total" => $storage_total,
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "User has been updated."
        ]);
    }

    public function fetch_single_user()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $admin = $this->auth();
        $id = request()->id ?? 0;

        $user = DB::table("users")
            ->where("id", "=", $id)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User not found."
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "type" => $user->type ?? "user",
                "storage_used" => $user->storage_used ?? 0,
                "storage_total" => $user->storage_total ?? 0
            ]
        ]);
    }

    public function delete_user()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function fetch_users()
    {
        $admin = $this->auth();
        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            $date_time_zone = new \DateTimeZone($time_zone);
        }

        $users = DB::table("users")
            ->where("type", "!=", "super_admin")
            ->orderBy("id", "desc")
            ->paginate();

        $users_arr = [];
        foreach ($users as $user)
        {
            if (!empty($time_zone))
            {
                $date_time = new \DateTime($user->created_at);
                $date_time->setTimezone($date_time_zone);
                $user->created_at = $date_time->format("d M, Y h:i:s a");
            }

            array_push($users_arr, [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "profile_image" => url("/storage/" . $user->profile_image),
                "type" => $user->type,
                "created_at" => $user->created_at,
                "test" => now()->utc()
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "users" => $users_arr
        ]);
    }

    public function fetch_settings()
    {
        $admin = $this->auth();
        $settings = DB::table("settings")->get();
        $settings_obj = new \stdClass();

        foreach ($settings as $setting)
        {
            $settings_obj->{$setting->key} = $setting->value;
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "settings" => $settings_obj
        ]);
    }

    public function save_settings()
    {
        $admin = $this->auth();
        $host = request()->host ?? "";
        $port = request()->port ?? "";
        $encryption = request()->encryption ?? "";
        $username = request()->username ?? "";
        $password = request()->password ?? "";
        $from = request()->from ?? "";
        $from_name = request()->from_name ?? "";
        $verify_email = request()->verify_email ?? "";

        $this->set_setting("verify_email", $verify_email);
        $this->set_setting("smtp_host", $host);
        $this->set_setting("smtp_port", $port);
        $this->set_setting("smtp_encryption", $encryption);
        $this->set_setting("smtp_username", $username);
        $this->set_setting("smtp_password", $password);
        $this->set_setting("smtp_from", $from);
        $this->set_setting("smtp_from_name", $from_name);

        return response()->json([
            "status" => "success",
            "message" => "Settings has been saved."
        ]);
    }

    private function set_setting($key, $value)
    {
        $setting = DB::table("settings")
            ->where("key", "=", $key)
            ->first();

        if ($setting == null)
        {
            DB::table("settings")
                ->insertGetId([
                    "key" => $key,
                    "value" => $value,
                    "created_at" => now()->utc(),
                    "updated_at" => now()->utc()
                ]);
        }
        else
        {
            DB::table("settings")
                ->where("id", "=", $setting->id)
                ->update([
                    "value" => $value,
                    "updated_at" => now()->utc()
                ]);
        }
    }

    private function auth()
    {
        if (auth()->user()->type != "super_admin")
        {
            return response()->json([
                "status" => "error",
                "message" => "Un-authorized."
            ])->throwResponse();
        }
    }

    public function login()
    {
        $validator = Validator::make(request()->all(), [
            "email" => "required",
            "password" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $email = request()->email ?? "";
        $password = request()->password ?? "";

        $user = User::where("email", "=", $email)->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Email does not exist."
            ]);
        }

        if (!password_verify($password, $user->password))
        {
            return response()->json([
                "status" => "error",
                "message" => "In-correct password."
            ]);
        }

        if (is_null($user->email_verified_at))
        {
            return response()->json([
                "status" => "error",
                "message" => "Email not verified."
            ]);
        }

        if ($user->type != "super_admin")
        {
            return response()->json([
                "status" => "error",
                "message" => "Un-authorized."
            ]);
        }

        $token = $user->createToken($this->admin_token_secret)->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Login successfully.",
            "access_token" => $token
        ]);
    }
}
