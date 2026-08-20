import { createInertiaApp } from '@inertiajs/react';

/**
 * Pages set their own complete <title> via Inertia's <Head>, so no global
 * suffix is applied here — the marketing page's title must match the approved
 * template exactly.
 */
createInertiaApp({
    strictMode: true,
    progress: {
        color: '#1559f5',
    },
});
