import { Link } from '@inertiajs/react';
import PostCard from '@/components/blog/post-card';
import SeoHead from '@/components/seo-head';
import SiteLayout from '@/components/site-layout';
import { index } from '@/routes/blog';
import type { BlogPost, PostCard as PostCardData } from '@/types/blog';
import '../../../css/stravanta-blog.css';

type BlogShowProps = {
    post: BlogPost;
    related: PostCardData[];
};

export default function BlogShow({ post, related }: BlogShowProps) {
    return (
        <SiteLayout>
            <SeoHead />

            <main>
                <article>
                    <header className="post-header">
                        <p className="eyebrow">
                            {post.category?.name ?? 'Insights'}
                        </p>
                        <h1>{post.title}</h1>
                        {post.excerpt && <p>{post.excerpt}</p>}

                        <p className="post-byline">
                            {post.author && <span>{post.author}</span>}
                            {post.published_at_label && (
                                <time dateTime={post.published_at ?? undefined}>
                                    {post.published_at_label}
                                </time>
                            )}
                        </p>
                    </header>

                    {post.image && (
                        <img className="post-hero" src={post.image} alt="" />
                    )}

                    {/* The body is HTML written by an authenticated admin in
                        the panel's rich editor, and is rendered server-side
                        with resolved attachment URLs. */}
                    <div
                        className="post-body"
                        dangerouslySetInnerHTML={{ __html: post.body }}
                    />

                    {/* A div, not a <footer>: `stravanta.css` styles the bare
                        `footer` element as the navy site footer, and that would
                        leak into any footer element on the page. */}
                    <div className="post-footer">
                        <Link className="text-link" href={index.url()}>
                            ← All insights
                        </Link>
                    </div>
                </article>

                {related.length > 0 && (
                    <section className="post-related">
                        <h2>More on this</h2>
                        <div className="blog-grid">
                            {related.map((item) => (
                                <PostCard key={item.slug} post={item} />
                            ))}
                        </div>
                    </section>
                )}
            </main>
        </SiteLayout>
    );
}
