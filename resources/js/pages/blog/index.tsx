import { Link } from '@inertiajs/react';
import PostCard from '@/components/blog/post-card';
import SeoHead from '@/components/seo-head';
import SiteLayout from '@/components/site-layout';
import { index } from '@/routes/blog';
import type {
    BlogCategory,
    Paginated,
    PostCard as PostCardData,
} from '@/types/blog';
import '../../../css/stravanta-blog.css';

/**
 * Laravel labels the previous/next pagination links with HTML entities.
 *
 * They are rendered as text rather than markup, so the two entities Laravel
 * actually emits are swapped for the characters they stand for.
 */
function paginationLabel(label: string): string {
    return label.replace('&laquo;', '\u00ab').replace('&raquo;', '\u00bb');
}

type BlogIndexProps = {
    posts: Paginated<PostCardData>;
    categories: BlogCategory[];
    /** The slug currently being filtered on, echoed back by the server. */
    activeCategory: string | null;
};

export default function BlogIndex({
    posts,
    categories,
    activeCategory,
}: BlogIndexProps) {
    return (
        <SiteLayout>
            <SeoHead />

            <main>
                <section className="blog-masthead">
                    <p className="eyebrow">Insights</p>
                    <h1>Notes from inside the operating system.</h1>
                    <p>
                        What we see repeatedly across engagements: where the
                        real constraint hides, which AI work pays back, and how
                        delivery becomes predictable rather than hopeful.
                    </p>
                </section>

                {categories.length > 0 && (
                    <nav
                        className="blog-filter"
                        aria-label="Filter by category"
                    >
                        <Link
                            href={index.url()}
                            aria-current={activeCategory === null}
                        >
                            All
                        </Link>
                        {categories.map((category) => (
                            <Link
                                key={category.slug}
                                href={index.url({
                                    query: { category: category.slug },
                                })}
                                aria-current={activeCategory === category.slug}
                            >
                                {category.name}
                            </Link>
                        ))}
                    </nav>
                )}

                <section className="blog-list">
                    {posts.data.length === 0 ? (
                        <p className="blog-empty">
                            Nothing published here yet. Check back shortly, or{' '}
                            <Link className="text-link" href={index.url()}>
                                view every article
                            </Link>
                            .
                        </p>
                    ) : (
                        <div className="blog-grid">
                            {posts.data.map((post) => (
                                <PostCard key={post.slug} post={post} />
                            ))}
                        </div>
                    )}

                    {posts.last_page > 1 && (
                        <nav
                            className="blog-pagination"
                            aria-label="Article pages"
                        >
                            {posts.links.map((link) =>
                                link.url === null ? (
                                    <span
                                        key={link.label}
                                        className="is-disabled"
                                    >
                                        {paginationLabel(link.label)}
                                    </span>
                                ) : (
                                    <Link
                                        key={link.label}
                                        href={link.url}
                                        aria-current={
                                            link.active ? 'page' : undefined
                                        }
                                    >
                                        {paginationLabel(link.label)}
                                    </Link>
                                ),
                            )}
                        </nav>
                    )}
                </section>
            </main>
        </SiteLayout>
    );
}
