import { Head } from '@inertiajs/react';
import '../../css/stravanta.css';

const logo = '/images/stravanta-logo.png';

const services = [
    {
        number: '01',
        duration: '2 weeks',
        title: 'Business Advantage Scan',
        description:
            'Find the operational bottlenecks and practical AI opportunities that can create the greatest measurable return.',
        outcome: 'A prioritised 90-day action roadmap',
        investment: 'LKR 200,000 / €1,500',
    },
    {
        number: '02',
        duration: '8 weeks',
        title: 'Performance Accelerator',
        description:
            'Build a practical operating system that strengthens priorities, accountability, visibility and delivery predictability.',
        outcome: 'A repeatable system for reliable execution',
        investment: 'LKR 1.2m / €8,000',
    },
    {
        number: '03',
        duration: '3+ months',
        title: 'Executive Partner',
        description:
            'Add senior operations and delivery leadership without the cost or commitment of a full-time director.',
        outcome: 'Ongoing leadership, governance and momentum',
        investment: 'LKR 500,000 / €3,500 monthly',
    },
];

const steps = [
    { number: '01', title: 'Diagnose', description: 'Understand the real constraint—not just the visible symptom.' },
    { number: '02', title: 'Prioritise', description: 'Focus investment on the few changes with the strongest return.' },
    { number: '03', title: 'Implement', description: 'Build practical workflows, operating rhythms and controls.' },
    { number: '04', title: 'Embed', description: 'Transfer capability so improvements continue without dependency.' },
];

const signals = [
    'Delivery commitments are regularly missed',
    'Reporting and coordination consume too much time',
    'Leaders lack reliable operational visibility',
    'AI use is fragmented and difficult to measure',
    'Growth is increasing cost faster than revenue',
];

const contactEmail = 'hello@stravantaadvisory.com';

function Brand() {
    return (
        <a className="brand" href="#top" aria-label="STRAVANTA Advisory home">
            <img src={logo} alt="STRAVANTA" />
            <span>Advisory</span>
        </a>
    );
}

export default function Welcome() {
    return (
        <>
            <Head>
                <title>STRAVANTA Advisory | Turn Ambition Into Advantage</title>
                <meta
                    name="description"
                    content="Operator-led strategy, operations, AI and transformation advisory for ambitious companies in Sri Lanka and Europe."
                />
            </Head>

            <header className="header">
                <Brand />
                <nav aria-label="Primary navigation">
                    <a href="#services">Services</a>
                    <a href="#about">About</a>
                    <a href="#approach">Approach</a>
                    <a href="#contact">Contact</a>
                </nav>
                <a className="button button-small" href="#contact">
                    Start a conversation <span>↗</span>
                </a>
            </header>

            <main>
                <section className="hero" id="top">
                    <div className="orbit" aria-hidden="true" />
                    <div className="hero-content">
                        <p className="eyebrow">Operator-led business transformation</p>
                        <h1>
                            Turn ambition
                            <br />
                            into <em>advantage.</em>
                        </h1>
                        <p className="hero-copy">
                            STRAVANTA helps ambitious companies convert strategy into measurable growth through better
                            operations, practical AI and disciplined execution.
                        </p>
                        <div className="actions">
                            <a className="button button-primary" href="#services">
                                Explore our services ↓
                            </a>
                            <a className="text-link" href="#contact">
                                Discuss your challenge ↗
                            </a>
                        </div>
                    </div>
                    <aside className="proof">
                        <div>
                            <strong>4–8</strong>
                            <span>weeks to measurable progress</span>
                        </div>
                        <div>
                            <strong>2</strong>
                            <span>markets: Sri Lanka &amp; Europe</span>
                        </div>
                        <div>
                            <strong>1</strong>
                            <span>accountable transformation partner</span>
                        </div>
                    </aside>
                </section>

                <section className="capabilities">
                    <span>Strategy</span>
                    <i />
                    <span>Operations</span>
                    <i />
                    <span>AI</span>
                    <i />
                    <span>Transformation</span>
                </section>

                <section className="section" id="services">
                    <div className="heading">
                        <div>
                            <p className="eyebrow dark">How we create advantage</p>
                            <h2>
                                Focused engagements.
                                <br />
                                Measurable outcomes.
                            </h2>
                        </div>
                        <p>
                            Start with clarity, build the right operating system, then sustain momentum with experienced
                            leadership.
                        </p>
                    </div>
                    <div className="service-grid">
                        {services.map((service) => (
                            <article className="card" key={service.number}>
                                <div className="card-top">
                                    <span className="number">{service.number}</span>
                                    <span className="duration">{service.duration}</span>
                                </div>
                                <h3>{service.title}</h3>
                                <p>{service.description}</p>
                                <div className="outcome">
                                    <span>Outcome</span>
                                    <strong>{service.outcome}</strong>
                                </div>
                                <div className="investment">
                                    <span>Investment</span>
                                    <strong>{service.investment}</strong>
                                </div>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="about" id="about">
                    <div className="about-left">
                        <p className="eyebrow">Why STRAVANTA</p>
                        <h2>Advice is only valuable when it changes how work gets done.</h2>
                        <p className="signature">
                            Partner-led advisory
                            <span>Strategy • Operations • AI • Transformation</span>
                        </p>
                    </div>
                    <div className="about-right">
                        <p className="lead">
                            STRAVANTA brings strategy, operations, technology and delivery into one accountable
                            transformation partnership.
                        </p>
                        <p>
                            We work alongside leadership teams to diagnose what is slowing the business down, establish
                            the right operating discipline and use AI where it creates genuine commercial value—not
                            where it merely creates noise.
                        </p>
                        <div className="pillars">
                            <span>Operator-led</span>
                            <span>Outcome-focused</span>
                            <span>Technology-practical</span>
                            <span>Built for adoption</span>
                        </div>
                    </div>
                </section>

                <section className="section approach" id="approach">
                    <div className="heading">
                        <div>
                            <p className="eyebrow dark">The STRAVANTA method</p>
                            <h2>
                                Clarity before activity.
                                <br />
                                Outcomes before tools.
                            </h2>
                        </div>
                        <p>
                            A disciplined path from diagnosis to lasting capability—designed to create progress without
                            disrupting the business unnecessarily.
                        </p>
                    </div>
                    <div className="steps">
                        {steps.map((step) => (
                            <article className="step" key={step.number}>
                                <span>{step.number}</span>
                                <h3>{step.title}</h3>
                                <p>{step.description}</p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="fit">
                    <div className="fit-copy">
                        <p className="eyebrow dark">Where we create the most value</p>
                        <h2>
                            Built for ambitious
                            <br />
                            growth companies.
                        </h2>
                        <p>
                            The strongest fit is a 40–250 person business where growth has outpaced the systems used to
                            manage work.
                        </p>
                        <div className="sectors">
                            <span>Technology &amp; IT-enabled services</span>
                            <span>Professional &amp; business services</span>
                            <span>Export-oriented SMEs</span>
                        </div>
                    </div>
                    <div className="signals">
                        <p className="mini">Typical signals</p>
                        <ul>
                            {signals.map((signal) => (
                                <li key={signal}>{signal}</li>
                            ))}
                        </ul>
                        <div className="market">
                            <span>Serving</span>
                            <strong>Sri Lanka • Europe</strong>
                        </div>
                    </div>
                </section>

                <section className="cta" id="contact">
                    <div>
                        <p className="eyebrow">Your next move</p>
                        <h2>
                            Ready to turn friction
                            <br />
                            into forward motion?
                        </h2>
                    </div>
                    <div className="cta-copy">
                        <p>
                            Begin with a focused conversation about your priorities, constraints and the business
                            outcome that matters most.
                        </p>
                        <a
                            className="button button-gold"
                            href={`mailto:${contactEmail}?subject=STRAVANTA%20Advisory%20enquiry`}
                        >
                            Request a consultation ↗
                        </a>
                        <a className="email" href={`mailto:${contactEmail}`}>
                            {contactEmail}
                        </a>
                    </div>
                </section>
            </main>

            <footer>
                <Brand />
                <p>Build smarter. Operate better. Grow faster.</p>
                <p>© 2026 STRAVANTA Advisory</p>
            </footer>
        </>
    );
}
