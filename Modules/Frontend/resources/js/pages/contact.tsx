import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MapPin, Phone, Mail, Clock, Send } from 'lucide-react';
import React, { useState } from 'react';
import {
    PageShell,
    NavBar,
    Footer,
    RoadWatermark,
    useI18n,
} from './site-shared';

// Replace with the exact office coordinates (this defaults to central Maseru).
const MAP_LAT = -29.3167;
const MAP_LNG = 27.4833;
const MAP_EMBED_SRC = `https://www.google.com/maps?q=${MAP_LAT},${MAP_LNG}&z=15&output=embed`;

function ContactHeader() {
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
                    {t.contactPage.eyebrow}
                </Badge>
                <h1 className="font-display mb-4 text-4xl font-semibold">
                    {t.contactPage.title}
                </h1>
                <p style={{ color: 'var(--text-secondary)' }}>
                    {t.contactPage.sub}
                </p>
            </div>
        </section>
    );
}

function AddressAndMap() {
    const { t } = useI18n();

    return (
        <div>
            <div
                className="mb-6 overflow-hidden rounded-md border"
                style={{ borderColor: 'var(--border)' }}
            >
                <iframe
                    title="Roads Directorate office location"
                    src={MAP_EMBED_SRC}
                    width="100%"
                    height="300"
                    style={{ border: 0, display: 'block' }}
                    loading="lazy"
                    referrerPolicy="no-referrer-when-downgrade"
                />
            </div>

            <div
                className="space-y-5 rounded-md border p-6"
                style={{
                    borderColor: 'var(--border)',
                    background: 'var(--bg-raised)',
                }}
            >
                <h3 className="font-display text-lg font-semibold">
                    {t.contactPage.addressTitle}
                </h3>

                <div className="flex gap-3">
                    <MapPin
                        className="mt-0.5 h-4 w-4 shrink-0"
                        style={{ color: 'var(--accent)' }}
                    />
                    <div
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.addressLines.map((line) => (
                            <p key={line}>{line}</p>
                        ))}
                    </div>
                </div>

                <div className="flex gap-3">
                    <Phone
                        className="mt-0.5 h-4 w-4 shrink-0"
                        style={{ color: 'var(--accent)' }}
                    />
                    <div
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        <p
                            className="font-medium"
                            style={{ color: 'var(--text-primary)' }}
                        >
                            {t.contactPage.phoneLabel}
                        </p>
                        <p>{t.contactPage.phone}</p>
                    </div>
                </div>

                <div className="flex gap-3">
                    <Mail
                        className="mt-0.5 h-4 w-4 shrink-0"
                        style={{ color: 'var(--accent)' }}
                    />
                    <div
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        <p
                            className="font-medium"
                            style={{ color: 'var(--text-primary)' }}
                        >
                            {t.contactPage.emailLabel}
                        </p>
                        <p>{t.contactPage.email}</p>
                    </div>
                </div>

                <div className="flex gap-3">
                    <Clock
                        className="mt-0.5 h-4 w-4 shrink-0"
                        style={{ color: 'var(--accent)' }}
                    />
                    <div
                        className="text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        <p
                            className="font-medium"
                            style={{ color: 'var(--text-primary)' }}
                        >
                            {t.contactPage.hoursLabel}
                        </p>
                        <p>{t.contactPage.hours}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

interface FormState {
    name: string;
    email: string;
    mobile: string;
    subject: string;
    message: string;
}

function ContactForm() {
    const { t } = useI18n();
    const [form, setForm] = useState<FormState>({
        name: '',
        email: '',
        mobile: '',
        subject: '',
        message: '',
    });
    const [submitted, setSubmitted] = useState(false);

    const update =
        (key: keyof FormState) =>
        (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) =>
            setForm((f) => ({ ...f, [key]: e.target.value }));

    const handleSubmit = async (e: React.SyntheticEvent) => {
        e.preventDefault();
        // Wire this to your Laravel route, e.g.:
        // const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // await fetch('/contact', {
        //   method: 'POST',
        //   headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token ?? '' },
        //   body: JSON.stringify(form),
        // });
        setSubmitted(true);
    };

    if (submitted) {
        return (
            <div
                className="rounded-md border p-8 text-center"
                style={{
                    borderColor: 'var(--resolved)',
                    background: 'var(--resolved-bg)',
                }}
            >
                <p className="text-sm" style={{ color: 'var(--resolved)' }}>
                    {t.contactPage.submitted}
                </p>
            </div>
        );
    }

    return (
        <div
            className="rounded-md border p-6"
            style={{
                borderColor: 'var(--border)',
                background: 'var(--bg-raised)',
            }}
        >
            <h3 className="font-display mb-6 text-lg font-semibold">
                {t.contactPage.formHeading}
            </h3>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label
                        className="mb-1 block text-xs font-medium"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.fields.name}
                    </label>
                    <input
                        type="text"
                        required
                        value={form.name}
                        onChange={update('name')}
                    />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            className="mb-1 block text-xs font-medium"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {t.contactPage.fields.email}
                        </label>
                        <input
                            type="email"
                            required
                            value={form.email}
                            onChange={update('email')}
                        />
                    </div>
                    <div>
                        <label
                            className="mb-1 block text-xs font-medium"
                            style={{ color: 'var(--text-secondary)' }}
                        >
                            {t.contactPage.fields.mobile}
                        </label>
                        <input
                            type="tel"
                            required
                            value={form.mobile}
                            onChange={update('mobile')}
                        />
                    </div>
                </div>
                <div>
                    <label
                        className="mb-1 block text-xs font-medium"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.fields.subject}
                    </label>
                    <input
                        type="text"
                        required
                        value={form.subject}
                        onChange={update('subject')}
                    />
                </div>
                <div>
                    <label
                        className="mb-1 block text-xs font-medium"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {t.contactPage.fields.message}
                    </label>
                    <textarea
                        required
                        rows={5}
                        value={form.message}
                        onChange={update('message')}
                    />
                </div>
                <Button
                    type="submit"
                    className="w-full"
                    style={{ background: 'var(--accent)', color: '#14213D' }}
                >
                    {t.contactPage.submit} <Send className="ml-1 h-4 w-4" />
                </Button>
            </form>
        </div>
    );
}

function ContactContent() {
    return (
        <>
            <NavBar />
            <ContactHeader />
            <section className="mx-auto max-w-6xl px-6 py-16">
                <div className="grid gap-10 md:grid-cols-2">
                    <AddressAndMap />
                    <ContactForm />
                </div>
            </section>
            <Footer />
        </>
    );
}

export default function ContactPage() {
    return (
        <PageShell>
            <ContactContent />
        </PageShell>
    );
}
