<?php

use App\Enums\PostStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;

/**
 * Resolve an admin panel URL from the panel's configuration, so these tests
 * keep passing if the panel path changes.
 */
function blogAdminPath(string $suffix = ''): string
{
    return '/'.trim(Filament::getPanel('admin')->getPath().'/'.ltrim($suffix, '/'), '/');
}

/**
 * Create a published post, defaulting the relations the listing eager loads.
 *
 * @param  array<string, mixed>  $attributes
 */
function publishedPost(array $attributes = []): Post
{
    return Post::factory()->published()->create($attributes);
}

/*
|--------------------------------------------------------------- categories ---
*/

test('the migration seeds the advisory practice areas', function () {
    expect(Category::query()->count())->toBe(5)
        ->and(Category::query()->pluck('slug')->all())->toContain(
            'operational-excellence',
            'ai-and-automation',
            'leadership-and-governance',
            'growth-and-performance',
            'delivery-and-execution',
        );
});

/*
|-------------------------------------------------------------------- index ---
*/

test('the blog index renders the listing component', function () {
    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('blog/index')
            ->has('posts.data')
            ->has('categories')
            ->where('activeCategory', null)
            ->etc());
});

test('the blog index lists published posts', function () {
    publishedPost(['title' => 'Where the constraint hides']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Where the constraint hides')
            ->etc());
});

test('the blog index hides posts that are not live', function (string $state) {
    Post::factory()->{$state}()->create();

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts.data', 0)->etc());
})->with([
    'a draft' => 'draft',
    'an archived post' => 'archived',
    'a post scheduled for later' => 'scheduled',
]);

test('the blog index orders posts by publish date, newest first', function () {
    publishedPost(['title' => 'Older', 'published_at' => now()->subMonth()]);
    publishedPost(['title' => 'Newer', 'published_at' => now()->subDay()]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('posts.data.0.title', 'Newer')
            ->where('posts.data.1.title', 'Older')
            ->etc());
});

test('the blog index can be filtered to one category', function () {
    $advisory = Category::query()->where('slug', 'ai-and-automation')->sole();

    publishedPost(['title' => 'In category', 'category_id' => $advisory->getKey()]);
    publishedPost(['title' => 'Out of category']);

    $this->get(route('blog.index', ['category' => $advisory->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'In category')
            ->where('activeCategory', $advisory->slug)
            ->etc());
});

test('an unknown category slug yields an empty listing rather than an error', function () {
    publishedPost();

    $this->get(route('blog.index', ['category' => 'not-a-category']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 0)
            ->where('activeCategory', 'not-a-category')
            ->etc());
});

/*
|------------------------------------------------------------------ article ---
*/

test('a published post renders the article component', function () {
    $post = publishedPost([
        'title' => 'Making delivery dates mean something',
        'excerpt' => 'Predictability is a system property.',
        'body' => '<p>Four changes that move a team from hopeful to reliable.</p>',
    ]);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('blog/show')
            ->where('post.title', 'Making delivery dates mean something')
            ->where('post.excerpt', 'Predictability is a system property.')
            ->etc());
});

test('the article body is delivered as rendered rich content', function () {
    $post = publishedPost([
        'body' => '<p>The constraint is usually one process upstream.</p>',
    ]);

    // `body` is a registered rich content attribute, so the raw column is not
    // necessarily renderable HTML — this guards that wiring.
    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('post.body', fn (string $body): bool => str_contains(
                $body,
                'The constraint is usually one process upstream.',
            ))
            ->etc());
});

test('a post that is not live is not reachable by slug', function (string $state) {
    $post = Post::factory()->{$state}()->create();

    $this->get(route('blog.show', $post))->assertNotFound();
})->with([
    'a draft' => 'draft',
    'an archived post' => 'archived',
    'a post scheduled for later' => 'scheduled',
]);

test('an article suggests further posts from the same category', function () {
    $category = Category::query()->where('slug', 'operational-excellence')->sole();

    $post = publishedPost(['category_id' => $category->getKey()]);
    publishedPost(['title' => 'Same category', 'category_id' => $category->getKey()]);
    publishedPost(['title' => 'Different category']);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('related', 1)
            ->where('related.0.title', 'Same category')
            ->etc());
});

/*
|-------------------------------------------------------------------- media ---
*/

test('a featured image resolves to a url, and is absent when none is attached', function () {
    Storage::fake('public');

    $post = publishedPost();

    expect($post->featuredImageUrl())->toBeNull();

    $post->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 675))
        ->toMediaCollection('featured_image');

    expect($post->refresh()->featuredImageUrl())->toBeString()->not->toBeEmpty()
        ->and($post->featuredImageUrl(Post::CARD_CONVERSION))->toContain('card');
});

test('the featured image reaches the listing and the article', function () {
    Storage::fake('public');

    $post = publishedPost();
    $post->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 675))
        ->toMediaCollection('featured_image');

    $this->get(route('blog.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('posts.data.0.image', fn (?string $image): bool => $image !== null)
            ->etc());

    $this->get(route('blog.show', $post))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('post.image', fn (?string $image): bool => $image !== null)
            ->etc());
});

/*
|-------------------------------------------------------------------- admin ---
*/

test('an authenticated user can reach the blog resources', function (string $path) {
    $this->actingAs(User::factory()->create())
        ->get(blogAdminPath($path))
        ->assertOk();
})->with(['posts', 'posts/create', 'categories', 'categories/create']);

test('an authenticated user can create a post from the admin panel', function () {
    $author = User::factory()->create();
    $category = Category::query()->where('slug', 'ai-and-automation')->sole();

    Livewire::actingAs($author)
        ->test(CreatePost::class)
        ->fillForm([
            'title' => 'Measuring AI by outcome',
            'slug' => 'measuring-ai-by-outcome',
            'excerpt' => 'Counting licences tells you nothing.',
            'body' => '<p>Tie every pilot to a number on a management report.</p>',
            'status' => PostStatus::Published->value,
            'published_at' => now()->subHour(),
            'category_id' => $category->getKey(),
            'user_id' => $author->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->sole();

    expect($post->title)->toBe('Measuring AI by outcome')
        ->and($post->slug)->toBe('measuring-ai-by-outcome')
        ->and($post->status)->toBe(PostStatus::Published)
        ->and($post->category_id)->toBe($category->getKey())
        ->and($post->isPublished())->toBeTrue();

    // The post the admin just published is immediately live.
    $this->get(route('blog.show', $post))->assertOk();
});

test('two posts cannot share a slug', function () {
    publishedPost(['slug' => 'taken']);

    Livewire::actingAs(User::factory()->create())
        ->test(CreatePost::class)
        ->fillForm([
            'title' => 'Another article',
            'slug' => 'taken',
            'body' => '<p>Body.</p>',
            'status' => PostStatus::Draft->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

// The publish date defaults to now, so these two cases describe an author who
// deliberately clears the field. An empty string is what the cleared picker
// submits; `null` would drop the key and let the default stand.
test('a post cannot be published without a publish date', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(CreatePost::class)
        ->fillForm([
            'title' => 'Published without a date',
            'slug' => 'published-without-a-date',
            'body' => '<p>Body.</p>',
            'status' => PostStatus::Published->value,
            'published_at' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['published_at']);

    expect(Post::query()->count())->toBe(0);
});

test('a draft may be saved without a publish date', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(CreatePost::class)
        ->fillForm([
            'title' => 'Still drafting',
            'slug' => 'still-drafting',
            'body' => '<p>Body.</p>',
            'status' => PostStatus::Draft->value,
            'published_at' => '',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole()->published_at)->toBeNull();
});

test('a post with more than one page of articles paginates', function () {
    Post::factory()->published()->count(12)->create();

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('posts.data', 9)
            ->where('posts.last_page', 2)
            ->etc());

    $this->get(route('blog.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->has('posts.data', 3)->etc());
});
