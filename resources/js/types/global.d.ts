import type { Honeypot, ServiceInterestOption } from '@/types/contact';
import type { Seo } from '@/types/seo';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            flash: { status: string | null };
            honeypot: Honeypot;
            serviceInterests: ServiceInterestOption[];
            seo: Seo;
            [key: string]: unknown;
        };
    }
}

export {};
