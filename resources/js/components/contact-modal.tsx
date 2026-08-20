import * as Dialog from '@radix-ui/react-dialog';
import ContactForm from '@/components/contact-form';

type ContactModalProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

/**
 * The contact form in a dialog.
 *
 * Built on the Radix dialog primitive rather than the project's shadcn wrapper:
 * the marketing pages deliberately do not load Tailwind, so the shadcn
 * components would render unstyled. Radix itself ships no styles, and gives us
 * the focus trap, focus restore, Escape handling, scroll lock and ARIA wiring.
 */
export default function ContactModal({
    open,
    onOpenChange,
}: ContactModalProps) {
    return (
        <Dialog.Root open={open} onOpenChange={onOpenChange}>
            <Dialog.Portal>
                <Dialog.Overlay className="modal-overlay" />
                <Dialog.Content className="modal-panel">
                    <div className="modal-head">
                        <div>
                            <p className="eyebrow dark">Start a conversation</p>
                            <Dialog.Title className="modal-title">
                                Tell us what you are trying to solve.
                            </Dialog.Title>
                            <Dialog.Description className="modal-description">
                                Share a little context and we will come back
                                with a point of view, not a pitch deck.
                            </Dialog.Description>
                        </div>
                        <Dialog.Close
                            className="modal-close"
                            aria-label="Close"
                        >
                            ✕
                        </Dialog.Close>
                    </div>

                    <ContactForm />
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}
