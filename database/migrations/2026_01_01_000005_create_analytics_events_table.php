<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tool_key')->nullable();
            $table->string('province', 2)->nullable();
            $table->string('frequency', 20)->nullable();
            $table->string('salary_range', 20)->nullable();
            $table->string('path')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['name', 'created_at']);
            $table->index(['province', 'created_at']);
            $table->index('tool_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
