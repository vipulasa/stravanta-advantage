import { Link } from '@inertiajs/react';
import {
    createContext,
    useCallback,
    useContext,
    useRef,
    useState,
} from 'react';
import type { ReactNode } from 'react';
import Brand from '@/components/brand';
import ContactModal from '@/components/contact-modal';
import MobileMenu from '@/components/mobile-menu';
import type { NavLink } from '@/types/contact';
import '../../css/stravanta.css';
import '../../css/stravanta-ui.css';

const ContactModalContext = createContext<{ openContactModal: () => void }>({
    openContactModal: () => {},
});

/**
 * Open the contact dialog from anywhere inside the layout.
 *
 * The dialog lives in `SiteLayout`, but the buttons that open it are spread
 * through page content, so it is exposed through context rather than threaded
 * down as props. Note that the consumer must be rendered *inside* the layout;
 * calling this in the page component itself sits above the provider.
 */
export function useContactModal() {
    return useContext(ContactModalContext);
}

type SiteLayoutProps = {
    children: ReactNode;
    /**
     * On the home page the section links scroll; everywhere else they have to
     * navigate home first.
     */
    isHome?: boolean;
};

export default function SiteLayout({
    children,
    isHome = false,
}: SiteLayoutProps) {
    const [menuOpen, setMenuOpen] = useState(false);
    const [contactOpen, setContactOpen] = useState(false);

    // The dialog has several triggers in different places, so Radix cannot
    // restore focus on its own. Remember whatever opened it and hand focus
    // back on close.
    const lastTrigger = useRef<HTMLElement | null>(null);

    const openContactModal = useCallback(() => {
        lastTrigger.current = document.activeElement as HTMLElement | null;
        setContactOpen(true);
    }, []);

    const closeContactModal = useCallback((open: boolean) => {
        setContactOpen(open);

        if (!open) {
            lastTrigger.current?.focus();
        }
    }, []);

    const prefix = isHome ? '' : '/';
    const links: NavLink[] = [
        { label: 'Services', href: `${prefix}#services`, page: !isHome },
        { label: 'About', href: `${prefix}#about`, page: !isHome },
        { label: 'Approach', href: `${prefix}#approach`, page: !isHome },
        { label: 'Contact', href: '/contact', page: true },
    ];

    return (
        <ContactModalContext.Provider value={{ openContactModal }}>
            <header className="header">
                <Brand />

                <nav aria-label="Primary navigation">
                    {links.map((link) =>
                        link.page ? (
                            <Link key={link.href} href={link.href}>
                                {link.label}
                            </Link>
                        ) : (
                            <a key={link.href} href={link.href}>
                                {link.label}
                            </a>
                        ),
                    )}
                </nav>

                <button
                    className="button button-small"
                    type="button"
                    onClick={openContactModal}
                >
                    Start a conversation <span>↗</span>
                </button>

                <MobileMenu
                    open={menuOpen}
                    onOpenChange={setMenuOpen}
                    links={links}
                    onStartConversation={openContactModal}
                />
            </header>

            {children}

            <footer>
                <Brand />
                <p>Build smarter. Operate better. Grow faster.</p>
                <p>© 2026 STRAVANTA Advisory</p>
            </footer>

            <ContactModal open={contactOpen} onOpenChange={closeContactModal} />
        </ContactModalContext.Provider>
    );
}
