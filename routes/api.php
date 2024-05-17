<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\FilesController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post("/verify-email", [UserController::class, "verify_email"]);
Route::post("/reset-password", [UserController::class, "reset_password"]);
Route::post("/send-password-reset-link", [UserController::class, "send_password_reset_link"]);
Route::post("/login", [UserController::class, "login"]);
Route::post("/register", [UserController::class, "register"]);
Route::post("/admin/login", [AdminController::class, "login"]);

Route::group([
    "middleware" => ["auth:sanctum"]
], function () {
    Route::post("/folders/delete-permanently", [FilesController::class, "delete_folder_permanently"]);
    Route::post("/folders/rename", [FilesController::class, "rename_folder"]);
    Route::post("/folders/create", [FilesController::class, "create_folder"]);
    Route::post("/folders", [FilesController::class, "single_folder"]);

    Route::post("/files/remove-access", [FilesController::class, "remove_access"]);
    Route::post("/files/detail", [FilesController::class, "detail"]);
    Route::post("/files/my-shared", [FilesController::class, "my_shared"]);
    Route::post("/files/shares", [FilesController::class, "shares"]);
    Route::post("/files/change-visibility", [FilesController::class, "change_visibility"]);
    Route::post("/files/share", [FilesController::class, "share"]);
    Route::post("/files/search", [FilesController::class, "search"]);
    Route::post("/files/update-content", [FilesController::class, "update_content"]);
    Route::post("/files/fetch-content", [FilesController::class, "fetch_content"]);
    Route::post("/files/rename", [FilesController::class, "rename"]);
    Route::post("/files/all", [FilesController::class, "all_files"]);
    Route::post("/files/home", [FilesController::class, "home"]);
    Route::post("/files/delete-permanently", [FilesController::class, "delete_permanently"]);
    Route::post("/files/upload", [FilesController::class, "upload"]);

    Route::post("/messages/fetch", [MessagesController::class, "fetch"]);
    Route::post("/messages/send", [MessagesController::class, "send"]);

    Route::post("/change-password", [UserController::class, "change_password"]);
    Route::post("/save-profile", [UserController::class, "save_profile"]);
    Route::post("/logout", [UserController::class, "logout"]);
    Route::post("/me", [UserController::class, "me"]);

    Route::post("/admin/send-message", [AdminController::class, "send_message"]);
    Route::post("/admin/fetch-messages", [AdminController::class, "fetch_messages"]);
    Route::post("/admin/fetch-contacts", [AdminController::class, "fetch_contacts"]);
    Route::post("/admin/users/add", [AdminController::class, "add_user"]);
    Route::post("/admin/users/change-password", [AdminController::class, "change_user_password"]);
    Route::post("/admin/users/delete", [AdminController::class, "delete_user"]);
    Route::post("/admin/users/update", [AdminController::class, "update_user"]);
    Route::post("/admin/users/fetch/{id}", [AdminController::class, "fetch_single_user"]);
    Route::post("/admin/users/fetch", [AdminController::class, "fetch_users"]);
    Route::post("/admin/fetch-settings", [AdminController::class, "fetch_settings"]);
    Route::post("/admin/save-settings", [AdminController::class, "save_settings"]);
});
