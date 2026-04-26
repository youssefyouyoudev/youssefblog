<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'featured_image_alt')) {
                $table->string('featured_image_alt')->nullable()->after('featured_image');
            }

            if (! Schema::hasColumn('posts', 'image_credit')) {
                $table->string('image_credit')->nullable()->after('featured_image_alt');
            }

            if (! Schema::hasColumn('posts', 'keywords')) {
                $table->json('keywords')->nullable()->after('meta_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'keywords')) {
                $table->dropColumn('keywords');
            }

            if (Schema::hasColumn('posts', 'image_credit')) {
                $table->dropColumn('image_credit');
            }

            if (Schema::hasColumn('posts', 'featured_image_alt')) {
                $table->dropColumn('featured_image_alt');
            }
        });
    }
};
