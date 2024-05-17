<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->longText("message")->nullable();
            $table->unsignedBigInteger("sender_id")->nullable();
            $table->foreign("sender_id")->references("id")->on("users")->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->unsignedBigInteger("receiver_id")->nullable();
            $table->foreign("receiver_id")->references("id")->on("users")->onUpdate("CASCADE")->onDelete("CASCADE");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
