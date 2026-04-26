<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('post_view_logs')) {
            Schema::create('post_view_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->string('ip_hash', 64)->nullable();
                $table->timestamp('viewed_at')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_view_logs');
    }
};
