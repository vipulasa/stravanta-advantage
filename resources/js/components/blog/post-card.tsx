import { Link } from '@inertiajs/react';
import { show } from '@/routes/blog';
import type { PostCard as PostCardData } from '@/types/blog';

export default function PostCard({ post }: { post: PostCardData }) {
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
                    {post.published_at_label && (
                        <time dateTime={post.published_at ?? undefined}>
                            {post.published_at_label}
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
