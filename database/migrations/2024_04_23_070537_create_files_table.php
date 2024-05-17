<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users")->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->integer("folder_id")->default(0);
            $table->longText("name")->nullable();
            $table->longText("path")->nullable();
            // $table->binary("private_path")->nullable();
            $table->double("size")->default(0);
            $table->string("type")->nullable();
            $table->string("extension")->nullable();

            // ALTER TABLE files ADD visibility ENUM('private', 'public') DEFAULT 'private' AFTER size;
            $table->enum("visibility", ["private", "public"])->default("private");
            $table->timestamps();
        });

        DB::statement("ALTER TABLE files ADD private_path LONGBLOB DEFAULT NULL AFTER path");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
