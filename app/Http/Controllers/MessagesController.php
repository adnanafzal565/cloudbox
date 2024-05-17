<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Storage;
use Validator;

class MessagesController extends Controller
{
    public function fetch()
    {
        $user = auth()->user();
        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            $date_time_zone = new \DateTimeZone($time_zone);
        }

        $messages = DB::table("messages")
            ->leftJoin("message_attachments", "message_attachments.message_id", "=", "messages.id")
            ->where("messages.sender_id", "=", $user->id)
            ->orWhere("messages.receiver_id", "=", $user->id)
            ->select("messages.*", "message_attachments.path")
            ->orderBy("messages.id", "desc")
            ->paginate();

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
                "message" => $message->message ?? "",
                "sender_id" => $message->sender_id,
                "receiver_id" => $message->receiver_id,
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
            ->where("user_id", "=", $user->id)
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

    public function send()
    {
        $user = auth()->user();
        $message = request()->message ?? "";
        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            $date_time_zone = new \DateTimeZone($time_zone);
        }

        $admin = DB::table("users")
            ->where("type", "=", "super_admin")
            ->first();

        if ($admin == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Admin not available."
            ]);
        }

        $message_arr = [
            "message" => $message,
            "sender_id" => $user->id,
            "receiver_id" => $admin->id,
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
                "user_id" => $admin->id,
                "title" => "New message",
                "content" => "You have received a new message from: " . $user->name,
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
}
