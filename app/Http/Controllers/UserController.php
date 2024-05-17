<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Storage;
use Validator;
use Str;
use App\Models\User;

class UserController extends Controller
{
    public function verify_email()
    {
        $validator = Validator::make(request()->all(), [
            "email" => "required",
            "code" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $email = request()->email ?? "";
        $code = request()->code ?? "";

        $user = DB::table("users")
            ->where("email", "=", $email)
            ->where("verification_code", "=", $code)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Verification code expired."
            ]);
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "verification_code" => null,
                "email_verified_at" => now()->utc(),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Account has been verified. You can login now."
        ]);
    }

    public function reset_password()
    {
        $validator = Validator::make(request()->all(), [
            "email" => "required",
            "token" => "required",
            "password" => "required",
            "password_confirmation" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $email = request()->email ?? "";
        $token = request()->token ?? "";
        $password = request()->password ?? "";
        $password_confirmation = request()->password_confirmation ?? "";

        $password_reset_token = DB::table("password_reset_tokens")
            ->where("email", "=", $email)
            ->where("token", "=", $token)
            ->first();

        if ($password_reset_token == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Reset link is expired."
            ]);
        }

        if ($password != $password_confirmation)
        {
            return response()->json([
                "status" => "error",
                "message" => "Password mis-match."
            ]);
        }

        DB::table("password_reset_tokens")
            ->where("email", "=", $email)
            ->where("token", "=", $token)
            ->delete();

        DB::table("users")
            ->where("email", "=", $email)
            ->update([
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Password has been reset."
        ]);
    }

    public function send_password_reset_link()
    {
        $validator = Validator::make(request()->all(), [
            "email" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $email = request()->email ?? "";

        $user = DB::table("users")
            ->where("email", "=", $email)
            ->first();

        if ($user == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "User not found."
            ]);
        }

        // $reset_token = time() . md5($email);
        $reset_token = Str::random(60);

        $message = "<p>Please click the link below to reset your password</p>";
        $message .= "<a href='" . url("/reset-password/" . $email . "/" . $reset_token) . "'>";
            $message .= "Reset password";
        $message .= "</a>";

        $mail_error = $this->send_mail($email, $user->name, "Password reset link", $message);
        if (!empty($mail_error))
        {
            return response()->json([
                "status" => "error",
                "message" => $mail_error
            ]);
        }

        DB::table("password_reset_tokens")
            ->insertGetId([
                "email" => $email,
                "token" => $reset_token,
                "created_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Instructions to reset password has been sent."
        ]);
    }

    public function change_password()
    {
        $validator = Validator::make(request()->all(), [
            "current_password" => "required",
            "new_password" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $current_password = request()->current_password ?? "";
        $new_password = request()->new_password ?? "";

        if (!password_verify($current_password, $user->password))
        {
            return response()->json([
                "status" => "error",
                "message" => "In-correct password."
            ]);
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "password" => password_hash($new_password, PASSWORD_DEFAULT),
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Password has been changed."
        ]);
    }

    public function save_profile()
    {
        $validator = Validator::make(request()->all(), [
            "name" => "required",
            "profile_image" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $name = request()->name ?? "";
        $file_path = $user->profile_image;

        if (request()->file("profile_image"))
        {
            if ($user->profile_image && Storage::exists("public/" . $user->profile_image))
            {
                Storage::delete("public/" . $user->profile_image);
            }

            $file = request()->file("profile_image");
            $file_path = "users/" . $user->id . "/profile-" . time() . "-" . $file->getClientOriginalName();
            $file->storeAs("/public", $file_path);
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "name" => $name,
                "profile_image" => $file_path,
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Profile has been saved."
        ]);
    }

    public function logout()
    {
        $user = auth()->user();

        // $user->tokens()->delete();

        $user->currentAccessToken()->delete();

        // $user->tokens()->where("id", $token_id)->delete();

        return response()->json([
            "status" => "success",
            "message" => "User has been logged-out."
        ]);
    }

    public function me()
    {
        $user = auth()->user();
        $user->profile_image = url("/storage/" . $user->profile_image);

        $new_messages = DB::table("notifications")
            ->where("user_id", "=", $user->id)
            ->where("is_read", "=", 0)
            ->where("type", "=", "new_message")
            ->count();

        $new_notifications = DB::table("notifications")
            ->where("user_id", "=", $user->id)
            ->where("is_read", "=", 0)
            ->count();

        $folders = DB::table("folders")
            ->where("folder_id", "=", 0)
            ->where("user_id", "=", $user->id)
            ->orderBy("name", "asc")
            ->get();

        $folders_arr = [];
        foreach ($folders as $f)
        {
            array_push($folders_arr, [
                "id" => $f->id,
                "name" => $f->name,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($f->updated_at . " UTC"))
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "profile_image" => $user->profile_image,
                "new_messages" => $new_messages,
                "new_notifications" => $new_notifications,
                "folders" => $folders_arr,
                "storage_used" => $user->storage_used ?? 0,
                "storage_total" => $user->storage_total ?? 0
            ]
        ]);
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

        $token = $user->createToken($this->token_secret)->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Login successfully.",
            "access_token" => $token
        ]);
    }

    public function register()
    {
        $validator = Validator::make(request()->all(), [
            "name" => "required",
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

        $name = request()->name ?? "";
        $email = request()->email ?? "";
        $password = request()->password ?? "";

        $user = DB::table("users")
            ->where("email", "=", $email)
            ->first();

        if ($user != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Email already exists."
            ]);
        }

        $user_arr = [
            "name" => $name,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "type" => "user",
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $setting_verify_email = DB::table("settings")
            ->where("key", "=", "verify_email")
            ->where("value", "=", "yes")
            ->first();

        if ($setting_verify_email == null)
        {
            $user_arr["email_verified_at"] = now()->utc();
        }
        else
        {
            $verification_code = Str::random(6);
            $user_arr["verification_code"] = $verification_code;

            $message = '<p>Your verification code is: <b style="font-size: 30px;">' . $verification_code . '</b></p>';
            $this->send_mail($email, $name, "Email verification", $message);
        }

        DB::table("users")
            ->insertGetId($user_arr);

        if ($setting_verify_email == null)
        {
            return response()->json([
                "status" => "success",
                "message" => "Account has been created. Please login now.",
                "verification" => false
            ]);
        }
        else
        {
            return response()->json([
                "status" => "success",
                "message" => "Please check your email, a verification code has been sent to you.",
                "verification" => true
            ]);
        }
    }
}
