import { useForm, usePage } from '@inertiajs/react';
import { useId, useRef, useState } from 'react';
import type { FormEvent } from 'react';
import type { ContactFormData } from '@/types/contact';

/**
 * Build the wrapper classes for a field.
 *
 * Returns complete class names rather than interpolating a conditional
 * fragment: `prettier-plugin-tailwindcss` treats a className template literal
 * as a class list and normalises its whitespace, which silently ate the
 * leading space in `' field-invalid'` and rendered `fieldfield-invalid` —
 * dropping the field layout entirely whenever validation failed.
 */
function fieldClasses(hasError: unknown, wide = false): string {
    return [
        'field',
        wide ? 'field-wide' : null,
        hasError ? 'field-invalid' : null,
    ]
        .filter(Boolean)
        .join(' ');
}

const emptyForm: ContactFormData = {
    name: '',
    email: '',
    company: '',
    phone: '',
    service_interest: '',
    message: '',
};

export default function ContactForm() {
    const { honeypot, serviceInterests } = usePage().props;
    const [submitted, setSubmitted] = useState(false);
    const banner = useRef<HTMLDivElement>(null);

    // The page and the modal can render this form at the same time on
    // /contact, so field ids must be unique per instance — otherwise every
    // label in the modal points at the page form's input.
    const id = useId();
    const fieldId = (field: string): string => `${id}-${field}`;

    const { data, setData, post, processing, errors, reset, transform } =
        useForm<ContactFormData>({ ...emptyForm });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        // The honeypot field name is randomised per request, so its values are
        // attached from the server-supplied prop at submit time rather than
        // being held in form state.
        transform((values) => ({
            ...values,
            [honeypot.nameFieldName]: '',
            [honeypot.validFromFieldName]: honeypot.encryptedValidFrom,
        }));

        post('/contact', {
            preserveScroll: true,
            // Inertia replaces component state after a POST redirect by
            // default, which would discard the confirmation banner the moment
            // it was set.
            preserveState: true,
            onSuccess: () => {
                reset();
                setSubmitted(true);
                // Move focus to the confirmation so it is announced and so
                // keyboard users are not left at the bottom of a blank form.
                requestAnimationFrame(() => {
                    banner.current?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                    banner.current?.focus({ preventScroll: true });
                });
            },
        });
    }

    return (
        <>
            {submitted && (
                <div
                    className="form-banner"
                    ref={banner}
                    tabIndex={-1}
                    role="status"
                    aria-live="polite"
                >
                    <p className="mini">Enquiry received</p>
                    <h3>Thank you — we have your message.</h3>
                    <p>
                        We have emailed you a confirmation and will reply within
                        one business day.
                    </p>
                </div>
            )}

            <form className="contact-form" onSubmit={submit} noValidate>
                {/* Honeypot trap: off-screen, untabbable, hidden from assistive tech. */}
                <div className="honeypot-field" aria-hidden="true">
                    <label htmlFor={fieldId('hp')}>
                        Leave this field empty
                    </label>
                    <input
                        id={fieldId('hp')}
                        type="text"
                        name={honeypot.nameFieldName}
                        tabIndex={-1}
                        autoComplete="off"
                        defaultValue=""
                    />
                </div>

                <div className={fieldClasses(errors.name)}>
                    <label htmlFor={fieldId('name')}>Your name</label>
                    <input
                        id={fieldId('name')}
                        type="text"
                        name="name"
                        autoComplete="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        aria-invalid={Boolean(errors.name)}
                        aria-describedby={
                            errors.name ? fieldId('name-error') : undefined
                        }
                    />
                    {errors.name && (
                        <p className="field-error" id={fieldId('name-error')}>
                            {errors.name}
                        </p>
                    )}
                </div>

                <div className={fieldClasses(errors.email)}>
                    <label htmlFor={fieldId('email')}>Work email</label>
                    <input
                        id={fieldId('email')}
                        type="email"
                        name="email"
                        autoComplete="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        aria-invalid={Boolean(errors.email)}
                        aria-describedby={
                            errors.email ? fieldId('email-error') : undefined
                        }
                    />
                    {errors.email && (
                        <p className="field-error" id={fieldId('email-error')}>
                            {errors.email}
                        </p>
                    )}
                </div>

                <div className={fieldClasses(errors.company)}>
                    <label htmlFor={fieldId('company')}>
                        Company <span className="optional">(optional)</span>
                    </label>
                    <input
                        id={fieldId('company')}
                        type="text"
                        name="company"
                        autoComplete="organization"
                        value={data.company}
                        onChange={(e) => setData('company', e.target.value)}
                    />
                    {errors.company && (
                        <p className="field-error">{errors.company}</p>
                    )}
                </div>

                <div className={fieldClasses(errors.phone)}>
                    <label htmlFor={fieldId('phone')}>
                        Phone <span className="optional">(optional)</span>
                    </label>
                    <input
                        id={fieldId('phone')}
                        type="tel"
                        name="phone"
                        autoComplete="tel"
                        value={data.phone}
                        onChange={(e) => setData('phone', e.target.value)}
                    />
                    {errors.phone && (
                        <p className="field-error">{errors.phone}</p>
                    )}
                </div>

                <div className={fieldClasses(errors.service_interest, true)}>
                    <label htmlFor={fieldId('service')}>
                        Which engagement interests you?
                    </label>
                    <select
                        id={fieldId('service')}
                        name="service_interest"
                        value={data.service_interest}
                        onChange={(e) =>
                            setData('service_interest', e.target.value)
                        }
                        aria-invalid={Boolean(errors.service_interest)}
                        aria-describedby={
                            errors.service_interest
                                ? fieldId('service-error')
                                : undefined
                        }
                    >
                        <option value="">Please choose…</option>
                        {serviceInterests.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                    {errors.service_interest && (
                        <p
                            className="field-error"
                            id={fieldId('service-error')}
                        >
                            {errors.service_interest}
                        </p>
                    )}
                </div>

                <div className={fieldClasses(errors.message, true)}>
                    <label htmlFor={fieldId('message')}>
                        What would you like to solve?
                    </label>
                    <textarea
                        id={fieldId('message')}
                        name="message"
                        value={data.message}
                        onChange={(e) => setData('message', e.target.value)}
                        aria-invalid={Boolean(errors.message)}
                        aria-describedby={
                            errors.message
                                ? fieldId('message-error')
                                : undefined
                        }
                    />
                    {errors.message && (
                        <p
                            className="field-error"
                            id={fieldId('message-error')}
                        >
                            {errors.message}
                        </p>
                    )}
                </div>

                <div className="form-actions">
                    <button
                        className="button button-primary"
                        type="submit"
                        disabled={processing}
                    >
                        {processing ? 'Sending…' : 'Send enquiry ↗'}
                    </button>
                    <p className="form-note">
                        We reply within one business day.
                    </p>
                </div>
            </form>
        </>
    );
}
