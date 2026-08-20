import { Head } from '@inertiajs/react';
import ContactForm from '@/components/contact-form';
import SiteLayout from '@/components/site-layout';

const contactEmail = 'hello@stravantaadvisory.com';

export default function Contact() {
    return (
        <SiteLayout>
            <Head>
                <title>Contact STRAVANTA Advisory</title>
                <meta
                    name="description"
                    content="Start a conversation about your priorities, constraints and the business outcome that matters most. Operator-led advisory across Sri Lanka and Europe."
                />
            </Head>

            <main>
                <section className="contact-page">
                    <div className="contact-aside">
                        <p className="eyebrow dark">Your next move</p>
                        <h2>
                            Let&rsquo;s talk about
                            <br />
                            the real constraint.
                        </h2>
                        <p>
                            Tell us what is slowing the business down. We will
                            come back with a point of view on where the return
                            is, not a generic proposal.
                        </p>

                        <div className="contact-detail">
                            <span>Email</span>
                            <a href={`mailto:${contactEmail}`}>
                                {contactEmail}
                            </a>
                        </div>
                        <div className="contact-detail">
                            <span>Serving</span>
                            <strong>Sri Lanka • Europe</strong>
                        </div>
                        <div className="contact-detail">
                            <span>Response time</span>
                            <strong>Within one business day</strong>
                        </div>
                    </div>

                    <div className="contact-panel">
                        <ContactForm />
                    </div>
                </section>
            </main>
        </SiteLayout>
    );
}
