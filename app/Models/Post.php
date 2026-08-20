<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A blog article.
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $body
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User|null $author
 */
#[Fillable([
    'category_id',
    'user_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'status',
    'published_at',
    'meta_title',
    'meta_description',
])]
class Post extends Model implements HasMedia, HasRichContent
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use InteractsWithRichContent;

    /**
     * The image conversion used for cards in listings.
     */
    public const CARD_CONVERSION = 'card';

    /**
     * The image conversion used for the article header.
     */
    public const HERO_CONVERSION = 'hero';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Resolve posts by their slug in public URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the category this post is filed under.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the admin user who wrote this post.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Limit the query to posts that are live on the public site.
     *
     * A post is only public once it is marked published *and* its publish time
     * has passed, so scheduling a post for later is simply a matter of setting
     * a future `published_at`.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Determine whether this post is currently live on the public site.
     *
     * Mirrors scopePublished() for a single, already-loaded record.
     */
    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Route rich editor uploads through the media library.
     *
     * This also scopes every `data-id` in the stored content to this record's
     * own media collection, so a tampered identifier cannot pull in another
     * record's file.
     */
    public function setUpRichContent(): void
    {
        $this->registerRichContent('body')
            ->fileAttachmentProvider(
                SpatieMediaLibraryFileAttachmentProvider::make()->collection('post_attachments'),
            );
    }

    /**
     * Register the collections a post can hold media in.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);

        $this->addMediaCollection('post_attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif']);
    }

    /**
     * Register the derived image sizes the site renders.
     *
     * These are generated synchronously: the queue runs on the database driver
     * and a worker is not guaranteed, so a queued conversion would leave the
     * listing without a thumbnail until someone noticed.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(self::CARD_CONVERSION)
            ->nonQueued()
            ->width(640);

        $this->addMediaConversion(self::HERO_CONVERSION)
            ->nonQueued()
            ->width(1600);
    }

    /**
     * Shape the post for the blog listing and related-post strips.
     *
     * Kept on the model so the listing and the article page cannot drift apart
     * on what a "card" contains.
     *
     * The publish date is formatted here rather than in the browser. Under SSR
     * the server renders in the server's timezone and the client would rehydrate
     * in the visitor's, so a client-side `toLocaleDateString()` produces a
     * hydration mismatch whenever the two disagree about the date.
     *
     * @return array{
     *     title: string,
     *     slug: string,
     *     excerpt: string|null,
     *     published_at: string|null,
     *     published_at_label: string|null,
     *     category: array{name: string, slug: string}|null,
     *     author: string|null,
     *     image: string|null,
     * }
     */
    public function toCardArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_at_label' => $this->published_at?->format('j F Y'),
            'category' => $this->category === null ? null : [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'author' => $this->author?->name,
            'image' => $this->featuredImageUrl(self::CARD_CONVERSION),
        ];
    }

    /**
     * Get a URL for the featured image, or null when the post has none.
     *
     * `getFirstMediaUrl()` returns an empty string rather than null when the
     * collection is empty, which quietly renders a broken `<img>` on the front
     * end. Normalising it here keeps that decision in one place.
     */
    public function featuredImageUrl(string $conversion = ''): ?string
    {
        $url = $this->getFirstMediaUrl('featured_image', $conversion);

        return $url === '' ? null : $url;
    }
}
