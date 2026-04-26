<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'ad_clicks')) {
                $table->unsignedBigInteger('ad_clicks')->default(0)->after('views');
            }

            if (! Schema::hasColumn('posts', 'affiliate_clicks')) {
                $table->unsignedBigInteger('affiliate_clicks')->default(0)->after('ad_clicks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'affiliate_clicks')) {
                $table->dropColumn('affiliate_clicks');
            }

            if (Schema::hasColumn('posts', 'ad_clicks')) {
                $table->dropColumn('ad_clicks');
            }
        });
    }
};
