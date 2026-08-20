<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * The demo articles, one per practice area.
     *
     * @var list<array{title: string, category: string, excerpt: string}>
     */
    private const POSTS = [
        [
            'title' => 'The bottleneck is rarely where the noise is',
            'category' => 'operational-excellence',
            'excerpt' => 'Teams optimise the step that complains loudest. The constraint is usually one process upstream, and quiet.',
        ],
        [
            'title' => 'Measuring AI by outcome, not by adoption',
            'category' => 'ai-and-automation',
            'excerpt' => 'Counting licences tells you nothing. Tie every pilot to a number that already appears on a management report.',
        ],
        [
            'title' => 'An operating rhythm your leadership team will actually keep',
            'category' => 'leadership-and-governance',
            'excerpt' => 'Governance fails when it competes with delivery. The meetings that survive are the ones that remove work.',
        ],
        [
            'title' => 'When growth costs more than it earns',
            'category' => 'growth-and-performance',
            'excerpt' => 'Revenue growth that outpaces margin is a warning, not a win. Here is where the cost usually hides.',
        ],
        [
            'title' => 'Making delivery dates mean something again',
            'category' => 'delivery-and-execution',
            'excerpt' => 'Predictability is a system property, not a promise. Four changes that move a team from hopeful to reliable.',
        ],
    ];

    /**
     * Seed a demo article for each of the categories created by migration.
     */
    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create();
        $categories = Category::query()->pluck('id', 'slug');

        foreach (self::POSTS as $index => $post) {
            Post::factory()
                ->published()
                ->create([
                    'category_id' => $categories[$post['category']] ?? null,
                    'user_id' => $author->id,
                    'title' => $post['title'],
                    'slug' => Str::slug($post['title']),
                    'excerpt' => $post['excerpt'],
                    'published_at' => now()->subDays(($index + 1) * 3),
                ]);
        }
    }
}
