import { Link } from '@inertiajs/react';
import * as Dialog from '@radix-ui/react-dialog';
import Brand from '@/components/brand';
import type { NavLink } from '@/types/contact';

type MobileMenuProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    links: NavLink[];
    onStartConversation: () => void;
};

/**
 * Full-screen navigation for narrow viewports, including its own trigger.
 *
 * The approved template hid the nav and the header CTA below 900px without
 * putting anything in their place, leaving phones with a logo and no way to
 * navigate. This restores both.
 *
 * The hamburger is a `Dialog.Trigger` rather than a plain button so that Radix
 * owns the whole focus cycle — without it, closing the menu drops focus onto
 * the body instead of returning it to the control that opened the menu.
 */
export default function MobileMenu({
    open,
    onOpenChange,
    links,
    onStartConversation,
}: MobileMenuProps) {
    return (
        <Dialog.Root open={open} onOpenChange={onOpenChange}>
            <Dialog.Trigger className="nav-toggle" aria-label="Open menu">
                <span />
            </Dialog.Trigger>

            <Dialog.Portal>
                <Dialog.Overlay className="menu-overlay" />
                <Dialog.Content className="menu-panel">
                    <Dialog.Title className="sr-only-title">Menu</Dialog.Title>
                    <Dialog.Description className="sr-only-title">
                        Links to the main sections of the STRAVANTA Advisory
                        site.
                    </Dialog.Description>

                    <div className="menu-head">
                        <Brand />
                        <Dialog.Close
                            className="menu-close"
                            aria-label="Close menu"
                        >
                            ✕
                        </Dialog.Close>
                    </div>

                    <nav className="menu-body" aria-label="Primary navigation">
                        {links.map((link) =>
                            link.page ? (
                                <Link
                                    key={link.href}
                                    href={link.href}
                                    onClick={() => onOpenChange(false)}
                                >
                                    {link.label}
                                </Link>
                            ) : (
                                <a
                                    key={link.href}
                                    href={link.href}
                                    onClick={() => onOpenChange(false)}
                                >
                                    {link.label}
                                </a>
                            ),
                        )}
                        <button
                            className="button button-primary"
                            type="button"
                            onClick={() => {
                                onOpenChange(false);
                                onStartConversation();
                            }}
                        >
                            Start a conversation ↗
                        </button>
                    </nav>

                    <p className="menu-foot">Sri Lanka • Europe</p>
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}
