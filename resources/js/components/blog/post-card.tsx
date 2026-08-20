import { Link } from '@inertiajs/react';
import { show } from '@/routes/blog';
import type { PostCard as PostCardData } from '@/types/blog';

/**
 * Format a publish date for display, tolerating a missing one.
 *
 * Published posts always carry a date, but the type allows null because the
 * same card shape is reused wherever a post is summarised.
 */
export function formatPublishedAt(value: string | null): string | null {
    if (value === null) {
        return null;
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export default function PostCard({ post }: { post: PostCardData }) {
    const publishedAt = formatPublishedAt(post.published_at);

    return (
        <article className="post-card">
            {post.image ? (
                <img
                    className="post-card-image"
                    src={post.image}
                    alt=""
                    loading="lazy"
                />
            ) : (
                <div className="post-card-image is-empty" aria-hidden="true" />
            )}

            <div className="post-card-body">
                <p className="post-card-meta">
                    {post.category && (
                        <span className="category">{post.category.name}</span>
                    )}
                    {publishedAt && (
                        <time dateTime={post.published_at ?? undefined}>
                            {publishedAt}
                        </time>
                    )}
                </p>

                <h2>
                    <Link href={show.url({ post: post.slug })}>
                        {post.title}
                    </Link>
                </h2>

                {post.excerpt && <p>{post.excerpt}</p>}

                <span className="post-card-more">Read the article ↗</span>
            </div>
        </article>
    );
}
