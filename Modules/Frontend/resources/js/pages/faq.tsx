import {
    Accordion,
    AccordionItem,
    AccordionTrigger,
    AccordionContent,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import React from 'react';
import {
    PageShell,
    NavBar,
    Footer,
    RoadWatermark,
    useI18n,
} from './site-shared';

function FAQHeader() {
    const { t } = useI18n();

    return (
        <section
            className="relative overflow-hidden border-b"
            style={{ borderColor: 'var(--border)' }}
        >
            <RoadWatermark />
            <div className="relative mx-auto max-w-3xl px-6 pt-16 pb-14 text-center">
                <Badge
                    variant="outline"
                    className="mb-4 font-mono text-xs"
                    style={{
                        borderColor: 'var(--accent)',
                        color: 'var(--accent-dark)',
                        background: 'var(--bg-raised)',
                    }}
                >
                    {t.faqPage.eyebrow}
                </Badge>
                <h1 className="font-display mb-4 text-4xl font-semibold">
                    {t.faqPage.title}
                </h1>
                <p style={{ color: 'var(--text-secondary)' }}>
                    {t.faqPage.sub}
                </p>
            </div>
        </section>
    );
}

function FAQList() {
    const { t } = useI18n();

    return (
        <section className="mx-auto max-w-3xl px-6 py-16">
            <Accordion type="single" collapsible className="w-full">
                {t.faqPage.items.map((f, i) => (
                    <AccordionItem
                        key={i}
                        value={`item-${i}`}
                        style={{ borderColor: 'var(--border)' }}
                    >
                        <AccordionTrigger className="text-left text-sm font-semibold">
                            {f.q}
                        </AccordionTrigger>
                        <AccordionContent
                            className="text-sm"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {f.a}
                        </AccordionContent>
                    </AccordionItem>
                ))}
            </Accordion>
        </section>
    );
}

function FAQContent() {
    return (
        <>
            <NavBar />
            <FAQHeader />
            <FAQList />
            <Footer />
        </>
    );
}

export default function FAQPage() {
    return (
        <PageShell>
            <FAQContent />
        </PageShell>
    );
}
