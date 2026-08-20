<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The categories the blog ships with.
     *
     * These mirror the advisory practice areas on the marketing site, so they
     * are reference data rather than sample content: they are inserted here so
     * that every environment has them without anyone remembering to run a
     * seeder. The insert deliberately uses the query builder rather than the
     * Eloquent model, so this migration keeps replaying correctly even if the
     * model later gains casts, events or required attributes.
     *
     * @var list<array{name: string, slug: string, description: string}>
     */
    private const CATEGORIES = [
        [
            'name' => 'Operational Excellence',
            'slug' => 'operational-excellence',
            'description' => 'Removing the bottlenecks that quietly cap throughput, quality and margin.',
        ],
        [
            'name' => 'AI & Automation',
            'slug' => 'ai-and-automation',
            'description' => 'Practical, measurable applications of AI inside real operating workflows.',
        ],
        [
            'name' => 'Leadership & Governance',
            'slug' => 'leadership-and-governance',
            'description' => 'Operating rhythms, accountability and the visibility leaders need to steer.',
        ],
        [
            'name' => 'Growth & Performance',
            'slug' => 'growth-and-performance',
            'description' => 'Growing revenue faster than cost, and knowing which levers actually moved it.',
        ],
        [
            'name' => 'Delivery & Execution',
            'slug' => 'delivery-and-execution',
            'description' => 'Making delivery commitments predictable enough to plan a business around.',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('categories')->insert(array_map(
            fn (array $category): array => [
                ...$category,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::CATEGORIES,
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
