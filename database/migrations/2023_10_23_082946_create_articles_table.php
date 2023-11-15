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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 150)->nullable();
            $table->string('slug', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->integer('categories')->unsigned();
            $table->foreign('categories')->references('id')->on('categories');
            $table->text('image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived', "deleted"])->nullable();
            $table->integer('like_count')->nullable();
            $table->integer('click_count')->nullable();
            $table->integer('repost_count')->nullable();
            $table->string('author_id')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
