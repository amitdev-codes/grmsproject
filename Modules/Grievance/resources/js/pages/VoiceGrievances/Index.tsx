import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { Mic } from 'lucide-react';
import { route } from 'ziggy-js';
import IndexLayout from '../../../../../../resources/js/components/index-layout';

interface VoiceGrievanceRow {
    id: number;
    reference_no: string;
    language_selected: string | null;
    status: string;
    contact_phone: string | null;
    assigned_officer: { id: number; name: string } | null;
    grievance: { id: number; reference_no: string } | null;
    created_at: string;
}

interface VoiceGrievanceIndexProps {
    voiceGrievances: {
        data: VoiceGrievanceRow[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        total: number;
    };
}

export default function VoiceGrievanceIndex({
    voiceGrievances,
}: VoiceGrievanceIndexProps) {
    return (
        <IndexLayout
            title="Voice grievance inbox"
            breadcrumbs={[
                { label: 'Grievances', href: '/grievances' },
                { label: 'Voice grievance inbox', href: '/voice-grievances' },
            ]}
        >
            <Head title="Voice grievance inbox" />
            <div className="overflow-hidden rounded-lg border bg-card">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b p-4">
                    <div>
                        <h1 className="text-lg font-semibold">
                            Voice grievance inbox
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Listen to recordings, transcribe and translate them,
                            then file the grievance.
                        </p>
                    </div>
                    <span className="text-sm text-muted-foreground">
                        {voiceGrievances.total} recording(s)
                    </span>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[720px] text-left text-sm">
                        <thead className="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th scope="col" className="px-4 py-3">Reference</th>
                                <th scope="col" className="px-4 py-3">Language</th>
                                <th scope="col" className="px-4 py-3">Received</th>
                                <th scope="col" className="px-4 py-3">Assigned to</th>
                                <th scope="col" className="px-4 py-3">Status</th>
                                <th scope="col" className="px-4 py-3">Grievance</th>
                                <th scope="col" className="px-4 py-3">
                                    <span className="sr-only">Review</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {voiceGrievances.data.map((voiceGrievance) => (
                                <tr key={voiceGrievance.id}>
                                    <td className="px-4 py-3 font-mono text-xs">
                                        {voiceGrievance.reference_no}
                                    </td>
                                    <td className="px-4 py-3">
                                        {voiceGrievance.language_selected ?? 'Unknown'}
                                    </td>
                                    <td className="px-4 py-3">
                                        {new Date(voiceGrievance.created_at).toLocaleString()}
                                    </td>
                                    <td className="px-4 py-3">
                                        {voiceGrievance.assigned_officer?.name ?? 'Unassigned'}
                                    </td>
                                    <td className="px-4 py-3 capitalize">
                                        {voiceGrievance.status.replaceAll('_', ' ')}
                                    </td>
                                    <td className="px-4 py-3 font-mono text-xs">
                                        {voiceGrievance.grievance?.reference_no ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <Button asChild size="sm" variant="outline">
                                            <Link
                                                href={route('voice-grievances.show', voiceGrievance.id)}
                                                aria-label={`Review ${voiceGrievance.reference_no}`}
                                            >
                                                <Mic className="mr-2 h-4 w-4" />
                                                Review
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                            {voiceGrievances.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-12 text-center text-muted-foreground"
                                    >
                                        No voice recordings have been received.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {voiceGrievances.last_page > 1 && (
                    <nav
                        aria-label="Voice grievance pages"
                        className="flex flex-wrap gap-2 border-t p-4"
                    >
                        {voiceGrievances.links.map((link) => (
                            <Button
                                key={link.label}
                                asChild
                                size="sm"
                                variant={link.active ? 'default' : 'outline'}
                                disabled={!link.url}
                            >
                                <Link
                                    href={link.url ?? '#'}
                                    aria-current={link.active ? 'page' : undefined}
                                    children={link.label.replace(/<[^>]*>/g, '')}
                                />
                            </Button>
                        ))}
                    </nav>
                )}
            </div>
        </IndexLayout>
    );
}
