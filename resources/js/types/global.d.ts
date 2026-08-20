import type { Honeypot, ServiceInterestOption } from '@/types/contact';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            flash: { status: string | null };
            honeypot: Honeypot;
            serviceInterests: ServiceInterestOption[];
            [key: string]: unknown;
        };
    }
}

export {};
