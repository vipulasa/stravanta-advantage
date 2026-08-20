<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->sentence(6));

        return [
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(20),
            'body' => collect(range(1, 5))
                ->map(fn (): string => '<p>'.fake()->paragraph().'</p>')
                ->implode(''),
            'status' => PostStatus::Published,
            'published_at' => fake()->dateTimeBetween('-1 year'),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    /**
     * Indicate that the post is live on the public site.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the post has never been published.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is published, but not until a future date.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->addWeek(),
        ]);
    }

    /**
     * Indicate that the post has been taken down.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Archived,
        ]);
    }
}
