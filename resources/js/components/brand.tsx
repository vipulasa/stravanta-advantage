import { Link } from '@inertiajs/react';

const logo = '/images/stravanta-logo.png';

/**
 * The STRAVANTA wordmark. Links home rather than to the `#top` anchor, so it
 * works from every page and not only the marketing home page.
 */
export default function Brand() {
    return (
        <Link className="brand" href="/" aria-label="STRAVANTA Advisory home">
            <img src={logo} alt="STRAVANTA" />
            <span>Advisory</span>
        </Link>
    );
}
