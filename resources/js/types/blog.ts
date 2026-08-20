/** A blog category, as exposed to the public site. */
export type BlogCategory = {
    name: string;
    slug: string;
};

/** The shape of a post in a listing or related-posts strip. */
export type PostCard = {
    title: string;
    slug: string;
    excerpt: string | null;
    /** ISO 8601, or null while the post is unpublished. */
    published_at: string | null;
    category: BlogCategory | null;
    author: string | null;
    /** Featured image URL, or null when the post has none. */
    image: string | null;
};

/** A full article. `body` is rendered HTML from the admin's rich editor. */
export type BlogPost = PostCard & {
    body: string;
    meta_title: string | null;
    meta_description: string | null;
};

/** One entry in Laravel's paginator link list. */
export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** A Laravel length-aware paginator, as serialised for Inertia. */
export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
};
