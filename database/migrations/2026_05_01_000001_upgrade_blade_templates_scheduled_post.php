<?php

use Database\Seeders\UpgradeBladeTemplatesPostSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => UpgradeBladeTemplatesPostSeeder::class,
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('posts')
            ->where('slug', 'laravel-blade-templates-beginners')
            ->update([
                'title' => 'Blade Templates From Beginner to Practical Use',
                'slug' => 'blade-templates-from-beginner-to-practical-use',
                'meta_title' => 'Blade Templates From Beginner to Practical Use | Youssef Blog',
                'seo_title' => 'Blade Templates From Beginner to Practical Use | Youssef Blog',
                'meta_description' => 'Beginner-friendly Laravel guide to Blade Templates From Beginner to Practical Use with examples, mistakes, checklists, and production',
                'canonical_url' => null,
                'last_updated_at' => null,
            ]);
    }
};
