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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('owners');
            $table->string('name');
            $table->string('logo');
            $table->string('slug');
            $table->string('qr_code_path')->nullable();
            $table->string('token')->nullable();
            $table->string('chat_id')->nullable();
            $table->boolean('status')->default(1);
            $table->longText('comment_lable')->nullable();
            $table->enum('type', ['rating', 'single_choice']); // otziv turi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
