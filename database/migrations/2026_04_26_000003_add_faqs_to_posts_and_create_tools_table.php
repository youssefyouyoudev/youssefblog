<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'faqs')) {
                $table->json('faqs')->nullable()->after('keywords');
            }
        });

        Schema::create('tools', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->index();
            $table->text('description');
            $table->string('affiliate_url')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');

        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'faqs')) {
                $table->dropColumn('faqs');
            }
        });
    }
};
