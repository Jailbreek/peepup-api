<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'articles',
            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title', 150)->nullable();
                $table->string('slug', 100)->nullable();
                $table->text('description')->nullable();
                $table->text('content')->nullable();
                $table->text('image_cover')->nullable();
                $table->enum('status', ['draft', 'published', 'archived', "deleted", "dumped"])->nullable();
                $table->integer('visit_count')->nullable();
                $table->string('author_id')->nullable(false);
                $table->timestamp("created_at")->useCurrent();
                $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
