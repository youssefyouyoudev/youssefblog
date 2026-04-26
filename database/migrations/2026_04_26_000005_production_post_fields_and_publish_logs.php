<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('published_at');
            }

            if (! Schema::hasColumn('posts', 'last_updated_at')) {
                $table->timestamp('last_updated_at')->nullable()->after('updated_at');
            }

            if (! Schema::hasColumn('posts', 'schema_type')) {
                $table->string('schema_type')->default('BlogPosting')->after('last_updated_at');
            }
        });

        if (! Schema::hasTable('publish_logs')) {
            Schema::create('publish_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->timestamp('published_at');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_logs');

        Schema::table('posts', function (Blueprint $table): void {
            foreach (['schema_type', 'last_updated_at', 'meta_title'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
