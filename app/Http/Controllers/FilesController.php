<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Storage;
use Validator;

class FilesController extends Controller
{
    public function remove_access()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function detail()
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

        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            date_default_timezone_set($time_zone);
        }

        $user = auth()->user();
        $id = request()->id ?? 0;

        $file = DB::table("files")
            ->select("files.*", "folders.name AS folder_name")
            ->leftJoin("folders", "folders.id", "=", "files.folder_id")
            ->where("files.id", "=", $id)
            ->where("files.user_id", "=", $user->id)
            ->first();

        if ($file == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "File not found."
            ]);
        }

        $file_obj = [
            "id" => $file->id,
            "name" => $file->name,
            "size" => $file->size,
            "type" => $file->type,
            "extension" => $file->extension,
            "visibility" => $file->visibility,
            "created_at" => date("Y-m-d h:i:s a", strtotime($file->created_at . " UTC")),
            "updated_at" => date("Y-m-d h:i:s a", strtotime($file->updated_at . " UTC"))
        ];

        if (Storage::exists("public/" . $file->path))
        {
            $file_obj["path"] = url("storage/" . $file->path);
        }

        if ($file->folder_name)
        {
            $file_obj["folder"] = [
                "id" => $file->folder_id,
                "name" => $file->folder_name
            ];
        }

        // This feature is available in premium version only.
        $file_obj["shares"] = [];

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "file" => $file_obj
        ]);
    }

    public function shares()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }
    
    public function share()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function change_visibility()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function search()
    {
        $validator = Validator::make(request()->all(), [
            "q" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $q = request()->q ?? "";

        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            date_default_timezone_set($time_zone);
        }

        $folders = DB::table("folders")
            ->where("name", "LIKE", "%" . $q . "%")
            ->where("user_id", "=", $user->id)
            ->orderBy("id", "desc")
            ->paginate();

        $folders_arr = [];
        foreach ($folders as $f)
        {
            $files = DB::table("files")
                ->where("user_id", "=", $user->id)
                ->where("folder_id", "=", $f->id)
                ->count();

            array_push($folders_arr, [
                "id" => $f->id,
                "name" => $f->name,
                "files" => $files,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($f->updated_at . " UTC"))
            ]);
        }

        $files = DB::table("files")
            ->where("name", "LIKE", "%" . $q . "%")
            ->where("user_id", "=", $user->id)
            ->orderBy("id", "desc")
            ->paginate();

        $files_arr = [];
        foreach ($files as $file)
        {
            array_push($files_arr, [
                "id" => $file->id,
                "name" => $file->name,
                "path" => url("/storage/" . $file->path),
                "size" => $file->size,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($file->updated_at . " UTC"))
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "folders" => $folders_arr,
            "files" => $files_arr
        ]);
    }

    public function update_content()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function fetch_content()
    {
        return response()->json([
            "status" => "error",
            "message" => $this->premium_message
        ]);
    }

    public function delete_permanently()
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

        $user = auth()->user();
        $id = request()->id ?? 0;

        $file = DB::table("files")
            ->where("id", "=", $id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($file == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "File not found."
            ]);
        }

        $size_bytes = $file->size;

        if (Storage::exists("public/" . $file->path))
            Storage::delete("public/" . $file->path);

        DB::table("files")
            ->where("id", "=", $file->id)
            ->where("user_id", "=", $user->id)
            ->delete();

        if ($user->storage_used - $size_bytes > 0)
        {
            DB::table("users")
                ->where("id", "=", $user->id)
                ->decrement("storage_used", $size_bytes);

            DB::table("users")
                ->where("id", "=", $user->id)
                ->update([
                    "updated_at" => now()->utc()
                ]);
        }
        else
        {
            DB::table("users")
                ->where("id", "=", $user->id)
                ->update([
                    "storage_used" => 0,
                    "updated_at" => now()->utc()
                ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "File has been deleted permanently."
        ]);
    }

    public function delete_folder_permanently()
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

        $user = auth()->user();
        $id = request()->id ?? 0;

        $folder = DB::table("folders")
            ->where("id", "=", $id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($folder == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Folder not found."
            ]);
        }

        $tree = [];
        $user_folders = DB::table("folders")
            ->where("user_id", "=", $user->id)
            ->get();

        $check_folder = $folder->id;
        foreach ($user_folders as $user_folder)
        {
            if ($check_folder == $user_folder->folder_id)
            {
                $check_folder = $user_folder->id;
                array_push($tree, $user_folder->id);
            }
        }

        $files_arr = [];
        $folders = [];

        foreach ($tree as $t)
        {
            $tree_files = DB::table("files")
                ->where("folder_id", "=", $t)
                ->where("user_id", "=", $user->id)
                ->get();

            foreach ($tree_files as $f)
            {
                array_push($files_arr, $f->id);

                if (Storage::exists("public/" . $f->path))
                    Storage::delete("public/" . $f->path);

                DB::table("files")
                    ->where("id", "=", $f->id)
                    ->where("user_id", "=", $user->id)
                    ->delete();
            }

            DB::table("folders")
                ->where("id", "=", $t)
                ->where("user_id", "=", $user->id)
                ->delete();

            array_push($folders, $t);
        }

        $files = DB::table("files")
            ->where("folder_id", "=", $folder->id)
            ->where("user_id", "=", $user->id)
            ->get();

        foreach ($files as $f)
        {
            array_push($files_arr, $f->id);

            if (Storage::exists("public/" . $f->path))
                Storage::delete("public/" . $f->path);

            DB::table("files")
                ->where("id", "=", $f->id)
                ->where("folder_id", "=", $folder->id)
                ->where("user_id", "=", $user->id)
                ->delete();
        }

        DB::table("folders")
            ->where("id", "=", $folder->id)
            ->where("user_id", "=", $user->id)
            ->delete();

        array_push($folders, $folder->id);

        return response()->json([
            "status" => "success",
            "message" => "Folder has been deleted permanently.",
            "files" => $files_arr,
            "folders" => $folders
        ]);
    }

    public function rename()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "name" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $id = request()->id ?? 0;
        $name = request()->name ?? "";

        $file = DB::table("files")
            ->where("id", "=", $id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($file == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "File not found."
            ]);
        }

        if ($file->folder_id > 0)
        {
            $name_exists = DB::table("files")
                ->where("folder_id", "=", $file->folder_id)
                ->where("user_id", "=", $user->id)
                ->where("name", "=", $name)
                ->exists();

            if ($name_exists)
            {
                return response()->json([
                    "status" => "error",
                    "message" => "File with name '" . $name . "' already exists in this folder."
                ]);
            }
        }

        DB::table("files")
            ->where("id", "=", $file->id)
            ->update([
                "name" => $name,
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "File has been renamed."
        ]);
    }

    public function rename_folder()
    {
        $validator = Validator::make(request()->all(), [
            "id" => "required",
            "name" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $id = request()->id ?? 0;
        $name = request()->name ?? "";

        $folder = DB::table("folders")
            ->where("id", "=", $id)
            ->where("user_id", "=", $user->id)
            ->first();

        if ($folder == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Folder not found."
            ]);
        }

        if ($folder->folder_id > 0)
        {
            $name_exists = DB::table("folders")
                ->where("folder_id", "=", $folder->folder_id)
                ->where("user_id", "=", $user->id)
                ->where("name", "=", $name)
                ->exists();

            if ($name_exists)
            {
                return response()->json([
                    "status" => "error",
                    "message" => "Folder with name '" . $name . "' already exists in this folder."
                ]);
            }
        }

        DB::table("folders")
            ->where("id", "=", $folder->id)
            ->update([
                "name" => $name,
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Folder has been renamed."
        ]);
    }

    public function all_files()
    {
        $user = auth()->user();

        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            date_default_timezone_set($time_zone);
        }

        $files = DB::table("files")
            ->where("user_id", "=", $user->id)
            ->orderBy("id", "desc")
            ->paginate();

        $files_arr = [];
        foreach ($files as $file)
        {
            array_push($files_arr, [
                "id" => $file->id,
                "name" => $file->name,
                "path" => url("/storage/" . $file->path),
                "size" => $file->size,
                "type" => $file->type,
                "extension" => $file->extension,
                "visibility" => $file->visibility,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($file->updated_at . " UTC"))
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "files" => $files_arr
        ]);
    }

    public function single_folder()
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

        $user = auth()->user();
        $id = request()->id ?? 0;

        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            date_default_timezone_set($time_zone);
        }

        $folder = DB::table("folders")
            ->where("id", "=", $id)
            ->first();

        if ($folder == null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Folder not found."
            ]);
        }

        if ($folder->user_id != $user->id)
        {
            return response()->json([
                "status" => "error",
                "message" => "Un-authorized."
            ]);
        }

        $folders = DB::table("folders")
            ->where("user_id", "=", $user->id)
            ->where("folder_id", "=", $folder->id)
            ->orderBy("id", "desc")
            ->paginate();

        $folders_arr = [];
        foreach ($folders as $f)
        {
            $files = DB::table("files")
                ->where("user_id", "=", $user->id)
                ->where("folder_id", "=", $f->id)
                ->count();

            array_push($folders_arr, [
                "id" => $f->id,
                "name" => $f->name,
                "files" => $files,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($f->updated_at . " UTC"))
            ]);
        }

        $files = DB::table("files")
            ->where("user_id", "=", $user->id)
            ->where("folder_id", "=", $folder->id)
            ->orderBy("id", "desc")
            ->paginate();

        $files_arr = [];
        foreach ($files as $file)
        {
            array_push($files_arr, [
                "id" => $file->id,
                "name" => $file->name,
                "path" => url("/storage/" . $file->path),
                "size" => $file->size,
                "type" => $file->type,
                "extension" => $file->extension,
                "visibility" => $file->visibility,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($file->updated_at . " UTC"))
            ]);
        }

        $tree = [];
        $user_folders = DB::table("folders")
            ->where("id", "!=", $folder->id)
            ->where("user_id", "=", $user->id)
            ->orderBy("id", "desc")
            ->get();

        $check_folder = $folder->folder_id;
        foreach ($user_folders as $user_folder)
        {
            if ($check_folder == $user_folder->id)
            {
                $check_folder = $user_folder->folder_id;
                array_push($tree, [
                    "id" => $user_folder->id,
                    "name" => $user_folder->name
                ]);
            }
        }
        $tree = array_reverse($tree);

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "folder" => [
                "id" => $folder->id,
                "name" => $folder->name,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($folder->updated_at . " UTC"))
            ],
            "folders" => $folders_arr,
            "files" => $files_arr,
            "tree" => $tree
        ]);
    }

    public function create_folder()
    {
        $validator = Validator::make(request()->all(), [
            "name" => "required"
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

        $folder_id = request()->folder_id ?? 0;
        $folder = null;

        if ($folder_id > 0)
        {
            $folder = DB::table("folders")
                ->where("id", "=", $folder_id)
                ->where("user_id", "=", $user->id)
                ->first();

            if ($folder == null)
            {
                return response()->json([
                    "status" => "error",
                    "message" => "Folder not found."
                ]);
            }
        }

        $folder = DB::table("folders")
            ->where("user_id", "=", $user->id)
            ->where("name", "=", $name)
            ->where("folder_id", "=", $folder_id)
            ->first();

        if ($folder != null)
        {
            return response()->json([
                "status" => "error",
                "message" => "Folder '" . $name . "' already exists."
            ]);
        }

        $folder_obj = [
            "user_id" => $user->id,
            "name" => $name,
            "folder_id" => $folder_id,
            "created_at" => now()->utc(),
            "updated_at" => now()->utc()
        ];

        $folder_obj["id"] = DB::table("folders")
            ->insertGetId($folder_obj);

        return response()->json([
            "status" => "success",
            "message" => "Folder has been created.",
            "folder" => [
                "id" => $folder_obj["id"],
                "name" => $name,
                "updated_at" => $folder_obj["updated_at"]
            ]
        ]);
    }

    public function home()
    {
        $user = auth()->user();

        $time_zone = request()->time_zone ?? "";
        if (!empty($time_zone))
        {
            date_default_timezone_set($time_zone);
        }

        $total_folders = DB::table("folders")
            ->where("user_id", "=", $user->id)
            ->count();

        $total_files = DB::table("files")
            ->where("user_id", "=", $user->id)
            ->count();

        $files = DB::table("files")
            ->where("user_id", "=", $user->id)
            ->orderBy("id", "desc")
            ->paginate(4);

        $files_arr = [];
        foreach ($files as $file)
        {
            array_push($files_arr, [
                "id" => $file->id,
                "name" => $file->name,
                "path" => ($file->path && Storage::exists("public/" . $file->path)) ? url("/storage/" . $file->path) : null,
                "size" => $file->size,
                "type" => $file->type,
                "extension" => $file->extension,
                "visibility" => $file->visibility,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($file->updated_at . " UTC"))
            ]);
        }

        $folders = DB::table("folders")
            ->where("user_id", "=", $user->id)
            ->where("folder_id", "=", 0)
            ->orderBy("id", "desc")
            ->paginate(4);

        $folders_arr = [];
        foreach ($folders as $folder)
        {
            $files = DB::table("files")
                ->where("user_id", "=", $user->id)
                ->where("folder_id", "=", $folder->id)
                ->count();

            array_push($folders_arr, [
                "id" => $folder->id,
                "name" => $folder->name,
                "files" => $files,
                "updated_at" => date("Y-m-d h:i:s a", strtotime($folder->updated_at . " UTC"))
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data has been fetched.",
            "files" => $files_arr,
            "folders" => $folders_arr,
            "total_files" => $total_files,
            "total_folders" => $total_folders
        ]);
    }

    public function upload()
    {
        $validator = Validator::make(request()->all(), [
            "files" => "required",
            "visibility" => "required"
        ]);

        if (!$validator->passes() && count($validator->errors()->all()) > 0)
        {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->all()[0]
            ]);
        }

        $user = auth()->user();
        $folder_id = request()->folder_id ?? 0;
        $visibility = "public";
        $folder = null;

        if (!in_array($visibility, ["private", "public"]))
        {
            return response()->json([
                "status" => "error",
                "message" => "In-valid 'visibility' value."
            ]);
        }

        if ($folder_id > 0)
        {
            $folder = DB::table("folders")
                ->where("id", "=", $folder_id)
                ->where("user_id", "=", $user->id)
                ->first();

            if ($folder == null)
            {
                return response()->json([
                    "status" => "error",
                    "message" => "Folder not found."
                ]);
            }
        }

        $files_arr = [];
        $files = [];
        $total_size = 0;
        $file_ids = [];
        $file_paths = [];

        if (request()->file("files"))
        {
            foreach (request()->file("files") as $file)
            {
                $total_size += $file->getSize();
            }
        }

        if ($total_size > $user->storage_total)
        {
            return response()->json([
                "status" => "error",
                "message" => "Storage exceeded."
            ]);
        }

        if (request()->file("files"))
        {
            foreach (request()->file("files") as $file)
            {
                $file_size = 0;
                $private_path = null;
                $file_path = null;

                $file_path = "files/" . $user->id . "/" . time() . "-" . $file->getClientOriginalName();
                $file->storeAs("/public", $file_path);

                $file_size = filesize(storage_path("app/public/" . $file_path));

                $type = $file->getClientMimeType();
                $extension = strtolower($file->getClientOriginalExtension());

                $file_obj = [
                    "user_id" => $user->id,
                    "name" => $file->getClientOriginalName(),
                    "path" => $file_path,
                    "private_path" => $private_path,
                    "size" => $file_size,
                    "type" => $type,
                    "extension" => $extension,
                    "folder_id" => $folder_id,
                    "visibility" => $visibility,
                    "created_at" => now()->utc(),
                    "updated_at" => now()->utc()
                ];

                $file_obj["id"] = DB::table("files")
                    ->insertGetId($file_obj);

                array_push($files_arr, [
                    "id" => $file_obj["id"],
                    "name" => $file_obj["name"],
                    "path" => url("/storage/" . $file_obj["path"]),
                    "size" => $file_obj["size"],
                    "type" => $file_obj["type"],
                    "extension" => $file_obj["extension"],
                    "folder_id" => $folder_id,
                    "visibility" => $visibility,
                    "updated_at" => $file_obj["updated_at"]
                ]);

                array_push($file_ids, $file_obj["id"]);
                array_push($file_paths, $file_path);
            }
        }

        DB::table("users")
            ->where("id", "=", $user->id)
            ->increment("storage_used", $total_size);

        DB::table("users")
            ->where("id", "=", $user->id)
            ->update([
                "updated_at" => now()->utc()
            ]);

        return response()->json([
            "status" => "success",
            "message" => "Files has been uploaded.",
            "files" => $files_arr
        ]);
    }
}
