<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category')->default('utility');
            $table->string('calculator_key')->nullable();
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->text('intro_content')->nullable();
            $table->json('faq_content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_indexable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_pages');
    }
};
