/** Honeypot field configuration, generated per request by spatie/laravel-honeypot. */
export type Honeypot = {
    enabled: boolean;
    /** Randomised per render — never cache or hardcode this. */
    nameFieldName: string;
    validFromFieldName: string;
    encryptedValidFrom: string;
};

/** One selectable engagement, sourced from the ServiceInterest PHP enum. */
export type ServiceInterestOption = {
    value: string;
    label: string;
};

/** The fields a visitor fills in. Honeypot fields are added at submit time. */
export type ContactFormData = {
    name: string;
    email: string;
    company: string;
    phone: string;
    service_interest: string;
    message: string;
};

/** A primary navigation entry. */
export type NavLink = {
    label: string;
    href: string;
    /** True when the target is an Inertia page rather than an in-page anchor. */
    page?: boolean;
};
