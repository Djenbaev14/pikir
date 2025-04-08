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
        Schema::create('feedback_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses');
            $table->unsignedBigInteger('feedback_id');
            $table->foreign('feedback_id')->references('id')->on('feedback');
            $table->unsignedBigInteger('review_question_id');
            $table->foreign('review_question_id')->references('id')->on('review_questions');
            $table->integer('rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_details');
    }
};
