<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'image_credit',
        'status',
        'published_at',
        'meta_title',
        'seo_title',
        'meta_description',
        'keywords',
        'faqs',
        'canonical_url',
        'og_image',
        'reading_time',
        'views',
        'ad_clicks',
        'affiliate_clicks',
        'is_featured',
        'last_updated_at',
        'schema_type',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'is_featured' => 'boolean',
            'keywords' => 'array',
            'faqs' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(PostViewLog::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function (Builder $query): void {
                $query->where('status', 'published')
                    ->orWhere('status', 'scheduled');
            });
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->published()->latest('published_at');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')->whereNotNull('published_at')->where('published_at', '>', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function readingMinutes(): int
    {
        return $this->reading_time;
    }

    public function getReadingTimeAttribute(?int $value): int
    {
        return max(1, (int) ceil(Str::wordCount(strip_tags($this->content ?? '')) / 220));
    }

    public function getExcerptAttribute(?string $value): string
    {
        $source = filled($value) ? $value : ($this->content ?? '');

        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($source))), 160);
    }

    public function getSeoTitleAttribute(?string $value): ?string
    {
        return $this->meta_title ?: $value;
    }

    public function keywordList(): string
    {
        return collect($this->keywords ?? [])->filter()->implode(', ');
    }

    public function shortAnchorTitle(): string
    {
        return Str::of($this->title)
            ->replaceMatches('/\b(in|for|with|without|while|before|after|from|that|what|how|to)\b/i', '')
            ->squish()
            ->words(6, '')
            ->toString();
    }
}
