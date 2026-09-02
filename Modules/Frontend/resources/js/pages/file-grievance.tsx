import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import {
    FileText,
    ClipboardCheck,
    UserCheck,
    CheckCircle2,
    Search,
    Send,
    ArrowRight,
    ArrowLeft,
    MapPin,
    Copy,
    Star,
    Loader2,
    ShieldAlert,
    XCircle,
    Clock,
    ShieldCheck,
    EyeOff,
    Phone,
    Mail,
    RefreshCw,
    ImagePlus,
    X,
    UploadCloud,
    Construction,
    Landmark,
    Droplets,
    UserX,
    HeartHandshake,
    HelpCircle,
    AlertTriangle,
} from 'lucide-react';
import React, { useEffect, useMemo, useRef, useState } from 'react';
import { PageShell, NavBar, Footer } from './site-shared';
type GrievanceStatus =
    | 'submitted'
    | 'acknowledged'
    | 'assigned'
    | 'in_progress'
    | 'escalated'
    | 'resolved'
    | 'closed'
    | 'rejected'
    | 'reopened';

type Priority = 'low' | 'normal' | 'high';
interface ApiCategory {
    id: number;
    name: string;
    name_st: string;
    slug: string;
    icon: string | null;
    is_sensitive: boolean;
    form_fields?: { key: string; label: string; placeholder?: string }[];
}

interface ApiDistrict {
    id: number;
    name: string;
}

interface ApiDivision {
    id: number;
    name: string;
    district_id: number;
}

interface StatusHistoryEntry {
    id: number;
    from_status: GrievanceStatus | null;
    to_status: GrievanceStatus;
    note: string | null;
    changed_at: string; // ISO
}

interface GrievanceMessageT {
    id: number;
    sender: 'citizen' | 'officer';
    body: string;
    created_at: string; // ISO
}

interface EscalationT {
    id: number;
    level: string; // e.g. "Zonal Officer" | "Regional Head" | "Director"
    reason: string;
    escalated_at: string; // ISO
}

interface ResolutionT {
    id: number;
    summary: string;
    resolved_at: string; // ISO
}

interface AttachmentT {
    id: number;
    url: string;
    original_filename: string;
}

interface TrackedGrievance {
    reference_number: string;
    status: GrievanceStatus;
    priority: Priority;
    category_name: string;
    division_name: string | null;
    section_name: string | null;
    description: string;
    location_description: string | null;
    sla_due_at: string | null;
    satisfaction_rating: number | null;
    history: StatusHistoryEntry[];
    messages: GrievanceMessageT[];
    escalation: EscalationT | null;
    resolution: ResolutionT | null;
    attachments: AttachmentT[];
}

// ---------------------------------------------------------------------------
// Attachment (dropzone) rules — keep in sync with config/grievances.php and
// StoreGrievanceRequest on the backend.
// ---------------------------------------------------------------------------

const MAX_ATTACHMENTS = 5;
const MAX_ATTACHMENT_MB = 8;
const ACCEPTED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];

interface PendingFile {
    id: string;
    file: File;
    previewUrl: string;
    error: string | null;
}
const WARNING = '#B8860B';
const DANGER = '#B3261E';

interface StatusVisual {
    color: string;
    bg: string;
    description: string;
    icon: typeof FileText;
}

const STATUS_VISUALS: Record<GrievanceStatus, StatusVisual> = {
    submitted: {
        color: 'var(--text-secondary)',
        bg: 'var(--bg-raised)',
        description: 'Received and waiting to be reviewed.',
        icon: FileText,
    },
    acknowledged: {
        color: 'var(--accent-dark)',
        bg: 'var(--bg-raised)',
        description: 'A district officer has reviewed your case.',
        icon: ClipboardCheck,
    },
    assigned: {
        color: 'var(--accent-dark)',
        bg: 'var(--bg-raised)',
        description: 'Assigned to the responsible office.',
        icon: UserCheck,
    },
    in_progress: {
        color: 'var(--accent-dark)',
        bg: 'var(--bg-raised)',
        description: 'Work on your case is underway.',
        icon: Clock,
    },
    escalated: {
        color: DANGER,
        bg: 'rgba(179,38,30,0.08)',
        description: 'Raised to a higher office for action.',
        icon: ShieldAlert,
    },
    resolved: {
        color: 'var(--resolved)',
        bg: 'var(--resolved-bg)',
        description: 'An outcome has been recorded for your case.',
        icon: CheckCircle2,
    },
    closed: {
        color: 'var(--resolved)',
        bg: 'var(--resolved-bg)',
        description: 'This case is closed.',
        icon: CheckCircle2,
    },
    rejected: {
        color: DANGER,
        bg: 'rgba(179,38,30,0.08)',
        description: 'This case could not be actioned. See the note below.',
        icon: XCircle,
    },
    reopened: {
        color: WARNING,
        bg: 'rgba(184,134,11,0.08)',
        description: 'This case has been reopened for further action.',
        icon: RefreshCw,
    },
};

// Labels mirror GrievanceStatus::label() in App\Enums\GrievanceStatus exactly.
// Statuses aren't a database table — they're a fixed PHP enum — so there's
// nothing to fetch here; keep this object in lockstep with the enum by hand
// whenever a case or its label() changes on the backend.
const STATUS_LABELS: Record<GrievanceStatus, string> = {
    submitted: 'Submitted',
    acknowledged: 'Acknowledged',
    assigned: 'Assigned',
    in_progress: 'In progress',
    escalated: 'Escalated',
    resolved: 'Resolved',
    closed: 'Closed',
    rejected: 'Rejected',
    reopened: 'Reopened',
};

// Happy-path order for the linear timeline, matching the enum's normal
// forward flow (App\Enums\GrievanceStatus::allowedTransitions()). escalated /
// rejected / reopened are branch states and are never part of this line —
// Timeline renders them separately, as it already did.
const STATUS_ORDER: GrievanceStatus[] = [
    'submitted',
    'acknowledged',
    'assigned',
    'in_progress',
    'resolved',
    'closed',
];

type StatusMetaMap = Record<GrievanceStatus, StatusVisual & { label: string }>;

const STATUS_META: StatusMetaMap = (Object.keys(STATUS_VISUALS) as GrievanceStatus[]).reduce((acc, status) => {
    acc[status] = { ...STATUS_VISUALS[status], label: STATUS_LABELS[status] };

    return acc;
}, {} as StatusMetaMap);

// Maps grievance_categories.icon (a lucide icon name) to a component.
// Extend as new categories/icons are added on the backend; unknown or
// missing names fall back to a generic icon rather than breaking the UI.
const ICON_MAP: Record<string, typeof FileText> = {
    road: Construction,
    roads: Construction,
    construction: Construction,
    land: Landmark,
    landmark: Landmark,
    water: Droplets,
    droplets: Droplets,
    conduct: UserX,
    officer: UserX,
    welfare: HeartHandshake,
    grant: HeartHandshake,
    other: HelpCircle,
};

function resolveCategoryIcon(icon: string | null): typeof FileText {
    if (!icon) {
        return HelpCircle;
    }

    return ICON_MAP[icon.trim().toLowerCase()] ?? HelpCircle;
}

// ---------------------------------------------------------------------------
// Reference-data API layer — categories, districts, divisions and status
// labels/order all come from the backend so this file never hardcodes them.
// ---------------------------------------------------------------------------

async function fetchCategories(): Promise<ApiCategory[]> {
    const res = await fetch('/api/v1/grievance-categories');

    if (!res.ok) {
        throw new Error('Failed to load categories');
    }

    const json = await res.json();

    return json.data ?? json;
}

async function fetchDistricts(): Promise<ApiDistrict[]> {
    const res = await fetch('/api/v1/districts');

    if (!res.ok) {
        throw new Error('Failed to load districts');
    }

    const json = await res.json();

    return json.data ?? json;
}

async function fetchDivisions(): Promise<ApiDivision[]> {
    const res = await fetch(`/api/v1/divisions`);

    if (!res.ok) {
        throw new Error('Failed to load divisions');
    }

    const json = await res.json();

    return json.data ?? json;
}

// ---------------------------------------------------------------------------
// GrievanceMetaProvider — fetches categories/districts once (the two real
// database tables) and shares them, so no component re-declares or
// re-fetches this data. Status labels/order come from the STATUS_META
// constant above, not a fetch — see the note on STATUS_LABELS. Divisions are
// fetched separately (per selected district) via useDivisions.
// ---------------------------------------------------------------------------

interface GrievanceMetaContextValue {
    categories: ApiCategory[];
    districts: ApiDistrict[];
    statusMeta: StatusMetaMap;
    statusOrder: GrievanceStatus[];
    loading: boolean;
    error: string | null;
}

const GrievanceMetaContext = React.createContext<GrievanceMetaContextValue | null>(null);

function useGrievanceMeta(): GrievanceMetaContextValue {
    const ctx = React.useContext(GrievanceMetaContext);

    if (!ctx) {
        throw new Error('useGrievanceMeta must be used within GrievanceMetaProvider');
    }

    return ctx;
}

function GrievanceMetaProvider({ children }: { children: React.ReactNode }) {
    const [categories, setCategories] = useState<ApiCategory[]>([]);
    const [districts, setDistricts] = useState<ApiDistrict[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        (async () => {
            try {
                const [cats, dists] = await Promise.all([fetchCategories(), fetchDistricts()]);

                if (cancelled) {
                    return;
                }

                setCategories(cats);
                setDistricts(dists);
            } catch {
                if (!cancelled) {
                    setError("We couldn't load the form options. Please refresh the page.");
                }
            } finally {
                if (!cancelled) {
                    setLoading(false);
                }
            }
        })();

        return () => {
            cancelled = true;
        };
    }, []);

    const value = useMemo(
        () => ({ categories, districts, statusMeta: STATUS_META, statusOrder: STATUS_ORDER, loading, error }),
        [categories, districts, loading, error],
    );

    return <GrievanceMetaContext.Provider value={value}>{children}</GrievanceMetaContext.Provider>;
}

function useDivisions(districtId: string): {
    divisions: ApiDivision[];
    loading: boolean;
} {
    const [divisions, setDivisions] = useState<ApiDivision[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!districtId) {
            return; // no setState — the return value below derives the empty case
        }

        let cancelled = false;

        Promise.resolve()
            .then(() => {
                if (!cancelled) {
                    setLoading(true);
                }

                return fetchDivisions();
            })
            .then((d) => {
                if (!cancelled) {
                    setDivisions(d);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setDivisions([]);
                }
            })
            .finally(() => {
                if (!cancelled) {
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [districtId]);

    return { divisions: districtId ? divisions : [], loading };
}

interface SubmitFields {
    category_id: number;
    district_id: number | null;
    division_id: number | null;
    description: string;
    location_description: string;
    metadata: Record<string, string>;
    is_anonymous: boolean;
    contact_name: string;
    contact_phone: string;
    contact_email: string;
}

function buildSubmitFormData(fields: SubmitFields, files: File[]): FormData {
    const form = new FormData();
    form.append('category_id', String(fields.category_id));

    if (fields.district_id) {
        form.append('district_id', String(fields.district_id));
    }

    if (fields.division_id) {
        form.append('division_id', String(fields.division_id));
    }

    form.append('description', fields.description);

    if (fields.location_description) {
        form.append('location_description', fields.location_description);
    }

    form.append('is_anonymous', fields.is_anonymous ? '1' : '0');

    if (!fields.is_anonymous) {
        if (fields.contact_name) {
            form.append('contact_name', fields.contact_name);
        }

        if (fields.contact_phone) {
            form.append('contact_phone', fields.contact_phone);
        }

        if (fields.contact_email) {
            form.append('contact_email', fields.contact_email);
        }
    }

    form.append('submitted_via', 'web');
    Object.entries(fields.metadata).forEach(([key, value]) => {
        if (value) {
            form.append(`metadata[${key}]`, value);
        }
    });
    files.forEach((file) => form.append('attachments[]', file));

    return form;
}

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function submitGrievance(
    fields: SubmitFields,
    files: File[],
): Promise<{ reference_number: string; sla_due_at: string }> {
    const res = await fetch('/grievances/add', {
        method: 'POST',
        headers: {
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: buildSubmitFormData(fields, files),
    });

    if (!res.ok) {
        const body = await res.json().catch(() => null);

        throw new Error(body?.message ?? 'Request failed');
    }

    const json = await res.json();
    const data = json.data ?? json;

    return {
        reference_number: data.reference_number,
        sla_due_at: data.sla_due_at,
    };
}

async function trackGrievance(reference: string, contact: string): Promise<TrackedGrievance | null> {
    const res = await fetch(`/grievances/track?ref=${encodeURIComponent(reference)}&contact=${encodeURIComponent(contact)}`);

    if (!res.ok) {
        throw new Error('Not found');
    }

    return await res.json();
}

async function sendCitizenMessage(reference: string, body: string): Promise<GrievanceMessageT> {
    try {
        const res = await fetch(`grievances/${reference}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ body }),
        });

        if (!res.ok) {
            throw new Error('Request failed');
        }

        return await res.json();
    } catch {
        return { id: Date.now(), sender: 'citizen', body, created_at: new Date().toISOString() };
    }
}

async function submitRating(reference: string, rating: number): Promise<void> {
    try {
        await fetch(`/api/grievances/${reference}/rating`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ satisfaction_rating: rating }),
        });
    } catch {
        // demo mode - no-op
    }
}

// ---------------------------------------------------------------------------
// Small shared pieces
// ---------------------------------------------------------------------------

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-LS', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatDateTime(iso: string) {
    return new Date(iso).toLocaleString('en-LS', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function StatusBadge({ status }: { status: GrievanceStatus }) {
    const { statusMeta } = useGrievanceMeta();
    const meta = statusMeta[status];
    const Icon = meta.icon;

    return (
        <Badge
            variant="outline"
            className="gap-1.5 font-mono text-xs"
            style={{ borderColor: meta.color, color: meta.color, background: meta.bg }}
        >
            <Icon className="h-3 w-3" />
            {meta.label}
        </Badge>
    );
}

function PriorityBadge({ priority }: { priority: Priority }) {
    const label = priority === 'high' ? 'High priority' : priority === 'low' ? 'Low priority' : 'Normal priority';
    const color = priority === 'high' ? DANGER : 'var(--text-secondary)';

    return (
        <Badge variant="outline" className="font-mono text-xs" style={{ borderColor: color, color }}>
            {label}
        </Badge>
    );
}

function SectionHeader({ eyebrow, title, sub }: { eyebrow: string; title: string; sub: string }) {
    return (
        <div className="mb-8 text-center">
            <Badge
                variant="outline"
                className="mb-4 font-mono text-xs"
                style={{ borderColor: 'var(--accent)', color: 'var(--accent-dark)', background: 'var(--bg-raised)' }}
            >
                {eyebrow}
            </Badge>
            <h1 className="font-display mb-2 text-3xl font-semibold md:text-4xl">{title}</h1>
            <p className="mx-auto max-w-lg text-sm" style={{ color: 'var(--text-secondary)' }}>
                {sub}
            </p>
        </div>
    );
}

function MetaLoadingState() {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-20 text-center">
            <Loader2 className="h-6 w-6 animate-spin" style={{ color: 'var(--text-secondary)' }} />
            <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                Loading form options...
            </p>
        </div>
    );
}

function MetaErrorState({ message }: { message: string }) {
    return (
        <Alert style={{ borderColor: DANGER, background: 'rgba(179,38,30,0.06)' }}>
            <AlertTriangle className="h-4 w-4" style={{ color: DANGER }} />
            <AlertDescription style={{ color: DANGER }}>{message}</AlertDescription>
        </Alert>
    );
}

// ---------------------------------------------------------------------------
// Filing wizard
// ---------------------------------------------------------------------------

const STEP_LABELS = ['Category', 'Details', 'Photos', 'Contact', 'Review'];

function StepIndicator({ step }: { step: number }) {
    return (
        <div className="mb-8 flex items-center">
            {STEP_LABELS.map((label, i) => {
                const n = i + 1;
                const active = n <= step;
                const isLast = i === STEP_LABELS.length - 1;

                return (
                    <React.Fragment key={label}>
                        <div className="flex flex-col items-center gap-1.5">
                            <div
                                className="flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold transition-colors duration-300"
                                style={{
                                    background: active ? 'var(--accent)' : 'transparent',
                                    border: `1.5px solid ${active ? 'var(--accent)' : 'var(--border)'}`,
                                    color: active ? '#14213D' : 'var(--text-secondary)',
                                }}
                            >
                                {n < step ? <CheckCircle2 className="h-3.5 w-3.5" /> : n}
                            </div>
                            <span
                                className="hidden text-[11px] sm:block"
                                style={{ color: active ? 'var(--text-primary)' : 'var(--text-secondary)' }}
                            >
                                {label}
                            </span>
                        </div>
                        {!isLast && (
                            <div
                                className="mx-2 h-px flex-1 transition-colors duration-300"
                                style={{ background: n < step ? 'var(--accent)' : 'var(--border)' }}
                            />
                        )}
                    </React.Fragment>
                );
            })}
        </div>
    );
}

function AttachmentsStep({
                             files,
                             onAdd,
                             onRemove,
                         }: {
    files: PendingFile[];
    onAdd: (list: FileList | null) => void;
    onRemove: (id: string) => void;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragOver, setDragOver] = useState(false);

    return (
        <div className="space-y-4">
            <div>
                <Label className="mb-1 block text-sm font-semibold">Add photos (optional)</Label>
                <p className="mb-3 text-xs" style={{ color: 'var(--text-secondary)' }}>
                    Photos of the issue help the responsible office act faster. Up to {MAX_ATTACHMENTS} files, JPG,
                    PNG, WEBP or HEIC, {MAX_ATTACHMENT_MB}MB each.
                </p>
                <div
                    role="button"
                    tabIndex={0}
                    onClick={() => inputRef.current?.click()}
                    onKeyDown={(e) => e.key === 'Enter' && inputRef.current?.click()}
                    onDragOver={(e) => {
                        e.preventDefault();
                        setDragOver(true);
                    }}
                    onDragLeave={() => setDragOver(false)}
                    onDrop={(e) => {
                        e.preventDefault();
                        setDragOver(false);
                        onAdd(e.dataTransfer.files);
                    }}
                    className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-md border-2 border-dashed px-6 py-10 text-center transition-colors"
                    style={{
                        borderColor: dragOver ? 'var(--accent)' : 'var(--border)',
                        background: dragOver ? 'var(--bg-page)' : 'var(--bg-raised)',
                    }}
                >
                    <UploadCloud className="h-6 w-6" style={{ color: 'var(--text-secondary)' }} />
                    <p className="text-sm font-medium">Drag photos here, or click to browse</p>
                    <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                        {files.length}/{MAX_ATTACHMENTS} added
                    </p>
                    <input
                        ref={inputRef}
                        type="file"
                        multiple
                        accept={ACCEPTED_MIME_TYPES.join(',')}
                        className="hidden"
                        onChange={(e) => {
                            onAdd(e.target.files);
                            e.target.value = '';
                        }}
                    />
                </div>
            </div>

            {files.length > 0 && (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    {files.map((f) => (
                        <div
                            key={f.id}
                            className="group relative overflow-hidden rounded-md border"
                            style={{ borderColor: f.error ? DANGER : 'var(--border)' }}
                        >
                            <img src={f.previewUrl} alt={f.file.name} className="h-24 w-full object-cover" />
                            <button
                                type="button"
                                aria-label={`Remove ${f.file.name}`}
                                onClick={() => onRemove(f.id)}
                                className="absolute top-1 right-1 rounded-full p-1"
                                style={{ background: 'rgba(20,33,61,0.75)' }}
                            >
                                <X className="h-3 w-3 text-white" />
                            </button>
                            {f.error ? (
                                <p className="truncate p-1.5 text-[10px]" style={{ color: DANGER }}>
                                    {f.error}
                                </p>
                            ) : (
                                <p
                                    className="truncate p-1.5 text-[10px]"
                                    style={{ color: 'var(--text-secondary)', background: 'var(--bg-raised)' }}
                                >
                                    {f.file.name}
                                </p>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function FileGrievanceWizard({ onFiled }: { onFiled: (ref: string) => void }) {
    const { categories, districts } = useGrievanceMeta();

    const [step, setStep] = useState(1);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [confirmation, setConfirmation] = useState<{ ref: string; slaDue: string } | null>(null);

    const [categoryId, setCategoryId] = useState<string>('');
    const [districtId, setDistrictId] = useState<string>('');
    const [divisionId, setDivisionId] = useState<string>('');
    const [description, setDescription] = useState('');
    const [locationDescription, setLocationDescription] = useState('');
    const [metadata, setMetadata] = useState<Record<string, string>>({});
    const [photos, setPhotos] = useState<PendingFile[]>([]);
    const [isAnonymous, setIsAnonymous] = useState(false);
    const [contactName, setContactName] = useState('');
    const [contactPhone, setContactPhone] = useState('');
    const [contactEmail, setContactEmail] = useState('');

    const category = categories.find((c) => String(c.id) === categoryId) ?? null;
    const { divisions: divisionsForDistrict, loading: divisionsLoading } = useDivisions(districtId);

    function addPhotos(list: FileList | null) {
        if (!list) {
            return;
        }

        const incoming = Array.from(list).slice(0, MAX_ATTACHMENTS - photos.length);
        const next: PendingFile[] = incoming.map((file) => {
            let error: string | null = null;

            if (!ACCEPTED_MIME_TYPES.includes(file.type)) {
                error = 'Unsupported file type';
            } else if (file.size > MAX_ATTACHMENT_MB * 1024 * 1024) {
                error = `Larger than ${MAX_ATTACHMENT_MB}MB`;
            }

            return {
                id: `${file.name}-${file.lastModified}-${Math.random().toString(36).slice(2)}`,
                file,
                previewUrl: URL.createObjectURL(file),
                error,
            };
        });
        setPhotos((prev) => [...prev, ...next]);
    }

    function removePhoto(id: string) {
        setPhotos((prev) => {
            const target = prev.find((p) => p.id === id);

            if (target) {
                URL.revokeObjectURL(target.previewUrl);
            }

            return prev.filter((p) => p.id !== id);
        });
    }

    const canProceed = useMemo(() => {
        if (step === 1) {
            return Boolean(categoryId && districtId);
        }

        if (step === 2) {
            return description.trim().length >= 20;
        }

        if (step === 3) {
            return photos.every((p) => !p.error);
        }

        if (step === 4) {
            return isAnonymous || contactPhone.trim().length > 0 || contactEmail.trim().length > 0;
        }

        return true;
    }, [step, categoryId, districtId, description, photos, isAnonymous, contactPhone, contactEmail]);

    async function handleSubmit() {
        setSubmitting(true);
        setError(null);

        try {
            const result = await submitGrievance(
                {
                    category_id: Number(categoryId),
                    district_id: districtId ? Number(districtId) : null,
                    division_id: divisionId ? Number(divisionId) : null,
                    description,
                    location_description: locationDescription,
                    metadata,
                    is_anonymous: isAnonymous,
                    contact_name: contactName,
                    contact_phone: contactPhone,
                    contact_email: contactEmail,
                },
                photos.filter((p) => !p.error).map((p) => p.file),
            );
            setConfirmation({ ref: result.reference_number, slaDue: result.sla_due_at });
        } catch {
            setError("We couldn't submit your grievance. Please check your connection and try again.");
        } finally {
            setSubmitting(false);
        }
    }

    function resetForm() {
        photos.forEach((p) => URL.revokeObjectURL(p.previewUrl));
        setConfirmation(null);
        setStep(1);
        setCategoryId('');
        setDistrictId('');
        setDivisionId('');
        setDescription('');
        setLocationDescription('');
        setMetadata({});
        setPhotos([]);
        setIsAnonymous(false);
        setContactName('');
        setContactPhone('');
        setContactEmail('');
    }

    if (confirmation) {
        return (
            <Card className="border" style={{ borderColor: 'var(--resolved)', background: 'var(--resolved-bg)' }}>
                <CardContent className="p-8 text-center">
                    <CheckCircle2 className="mx-auto mb-4 h-10 w-10" style={{ color: 'var(--resolved)' }} />
                    <h2 className="font-display mb-2 text-2xl font-semibold">Grievance received</h2>
                    <p className="mb-6 text-sm" style={{ color: 'var(--text-secondary)' }}>
                        Keep this reference number safe. You'll need it to check the status of your case.
                    </p>
                    <div
                        className="mx-auto mb-6 flex max-w-xs items-center justify-between rounded-md border px-4 py-3"
                        style={{ borderColor: 'var(--border)', background: 'var(--bg-page)' }}
                    >
                        <span className="font-mono text-lg font-semibold">{confirmation.ref}</span>
                        <button
                            type="button"
                            aria-label="Copy reference number"
                            onClick={() => navigator.clipboard?.writeText(confirmation.ref)}
                            className="rounded p-1.5 transition-colors hover:bg-black/5"
                        >
                            <Copy className="h-4 w-4" style={{ color: 'var(--text-secondary)' }} />
                        </button>
                    </div>
                    <p className="mb-6 text-xs" style={{ color: 'var(--text-secondary)' }}>
                        Target response by {formatDate(confirmation.slaDue)}.
                    </p>
                    <div className="flex flex-wrap justify-center gap-3">
                        <Button
                            onClick={() => onFiled(confirmation.ref)}
                            style={{ background: 'var(--accent)', color: '#14213D' }}
                        >
                            Track this grievance <ArrowRight className="ml-1 h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            style={{ borderColor: 'var(--text-primary)', color: 'var(--text-primary)' }}
                            onClick={resetForm}
                        >
                            File another
                        </Button>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="border" style={{ borderColor: 'var(--border)', background: 'var(--bg-raised)' }}>
            <CardContent className="p-6 md:p-8">
                <StepIndicator step={step} />

                {step === 1 && (
                    <div className="space-y-6">
                        <div>
                            <Label className="mb-2 block text-sm font-semibold">What is this about?</Label>
                            <div className="grid gap-2.5 sm:grid-cols-2">
                                {categories.map((c) => {
                                    const CategoryIcon = resolveCategoryIcon(c.icon);
                                    const selected = categoryId === String(c.id);

                                    return (
                                        <button
                                            key={c.id}
                                            type="button"
                                            onClick={() => setCategoryId(String(c.id))}
                                            className="flex items-start gap-2.5 rounded-md border p-3.5 text-left transition-colors"
                                            style={{
                                                borderColor: selected ? 'var(--accent)' : 'var(--border)',
                                                background: selected ? 'var(--bg-page)' : 'transparent',
                                            }}
                                        >
                                            <CategoryIcon
                                                className="mt-0.5 h-4 w-4 shrink-0"
                                                style={{ color: 'var(--text-secondary)' }}
                                            />
                                            <span>
                                                <span className="flex items-center gap-1.5">
                                                    <span className="text-sm font-semibold">{c.name}</span>
                                                    {c.is_sensitive && (
                                                        <Badge
                                                            variant="outline"
                                                            className="font-mono text-[10px]"
                                                            style={{ borderColor: DANGER, color: DANGER }}
                                                        >
                                                            Sensitive
                                                        </Badge>
                                                    )}
                                                </span>
                                                {c.name_st && (
                                                    <span
                                                        className="mt-0.5 block text-xs"
                                                        style={{ color: 'var(--text-secondary)' }}
                                                    >
                                                        {c.name_st}
                                                    </span>
                                                )}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label className="mb-2 block text-sm font-semibold">District</Label>
                                <Select
                                    value={districtId}
                                    onValueChange={(v) => {
                                        setDistrictId(v);
                                        setDivisionId('');
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select your district" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {districts.map((d) => (
                                            <SelectItem key={d.id} value={String(d.id)}>
                                                {d.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label className="mb-2 block text-sm font-semibold">Office (optional)</Label>
                                <Select value={divisionId} onValueChange={setDivisionId} disabled={!districtId}>
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder={
                                                !districtId
                                                    ? 'Choose a district first'
                                                    : divisionsLoading
                                                        ? 'Loading offices...'
                                                        : 'If you know it'
                                            }
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {divisionsForDistrict.map((d) => (
                                            <SelectItem key={d.id} value={String(d.id)}>
                                                {d.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>
                )}

                {step === 2 && (
                    <div className="space-y-5">
                        <div>
                            <Label className="mb-2 block text-sm font-semibold">Tell us what happened</Label>
                            <Textarea
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                placeholder="Describe what happened, when, and who or what was involved. The more detail, the faster we can act."
                                className="min-h-35"
                            />
                            <p className="mt-1 text-xs" style={{ color: 'var(--text-secondary)' }}>
                                {description.trim().length < 20
                                    ? `${20 - description.trim().length} more characters needed`
                                    : 'Looks good'}
                            </p>
                        </div>

                        <div>
                            <Label className="mb-2 flex items-center gap-1.5 text-sm font-semibold">
                                <MapPin className="h-3.5 w-3.5" /> Location
                            </Label>
                            <Input
                                value={locationDescription}
                                onChange={(e) => setLocationDescription(e.target.value)}
                                placeholder="e.g. Main North 1, near Ha Abia junction"
                            />
                        </div>

                        {/* Category-specific extra fields — populated only if the backend
                            sends `form_fields` on the category (add a jsonb column of that
                            name to grievance_categories to enable this). */}
                        {category?.form_fields?.map((f) => (
                            <div key={f.key}>
                                <Label className="mb-2 block text-sm font-semibold">{f.label}</Label>
                                <Input
                                    value={metadata[f.key] ?? ''}
                                    onChange={(e) => setMetadata((m) => ({ ...m, [f.key]: e.target.value }))}
                                    placeholder={f.placeholder}
                                />
                            </div>
                        ))}
                    </div>
                )}

                {step === 3 && <AttachmentsStep files={photos} onAdd={addPhotos} onRemove={removePhoto} />}

                {step === 4 && (
                    <div className="space-y-6">
                        <div>
                            <Label className="mb-3 block text-sm font-semibold">How should we reach you?</Label>
                            <RadioGroup
                                value={isAnonymous ? 'anonymous' : 'contact'}
                                onValueChange={(v) => setIsAnonymous(v === 'anonymous')}
                                className="space-y-2.5"
                            >
                                <label
                                    className="flex cursor-pointer items-start gap-3 rounded-md border p-3.5"
                                    style={{ borderColor: !isAnonymous ? 'var(--accent)' : 'var(--border)' }}
                                >
                                    <RadioGroupItem value="contact" className="mt-0.5" />
                                    <div>
                                        <p className="text-sm font-semibold">Share my contact details</p>
                                        <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                                            An officer can follow up and you'll get updates as your case moves.
                                        </p>
                                    </div>
                                </label>
                                <label
                                    className="flex cursor-pointer items-start gap-3 rounded-md border p-3.5"
                                    style={{ borderColor: isAnonymous ? 'var(--accent)' : 'var(--border)' }}
                                >
                                    <RadioGroupItem value="anonymous" className="mt-0.5" />
                                    <div className="flex items-start gap-2">
                                        <EyeOff className="mt-0.5 h-3.5 w-3.5 shrink-0" style={{ color: 'var(--text-secondary)' }} />
                                        <div>
                                            <p className="text-sm font-semibold">File anonymously</p>
                                            <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                                                No follow-up messages, but your reference number still lets you check status.
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </RadioGroup>
                        </div>

                        {!isAnonymous && (
                            <div className="grid gap-4">
                                <div>
                                    <Label className="mb-2 block text-sm font-semibold">Full name</Label>
                                    <Input value={contactName} onChange={(e) => setContactName(e.target.value)} placeholder="Your name" />
                                </div>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <Label className="mb-2 flex items-center gap-1.5 text-sm font-semibold">
                                            <Phone className="h-3.5 w-3.5" /> Phone
                                        </Label>
                                        <Input value={contactPhone} onChange={(e) => setContactPhone(e.target.value)} placeholder="e.g. 5812 3456" />
                                    </div>
                                    <div>
                                        <Label className="mb-2 flex items-center gap-1.5 text-sm font-semibold">
                                            <Mail className="h-3.5 w-3.5" /> Email
                                        </Label>
                                        <Input
                                            type="email"
                                            value={contactEmail}
                                            onChange={(e) => setContactEmail(e.target.value)}
                                            placeholder="Optional"
                                        />
                                    </div>
                                </div>
                                <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                                    Provide a phone number or an email so an officer can reach you.
                                </p>
                            </div>
                        )}
                    </div>
                )}

                {step === 5 && (
                    <div className="space-y-5">
                        <div className="rounded-md border p-5" style={{ borderColor: 'var(--border)', background: 'var(--bg-page)' }}>
                            <dl className="space-y-3 text-sm">
                                <div className="flex justify-between gap-4">
                                    <dt style={{ color: 'var(--text-secondary)' }}>Category</dt>
                                    <dd className="text-right font-medium">{category?.name}</dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt style={{ color: 'var(--text-secondary)' }}>District</dt>
                                    <dd className="text-right font-medium">
                                        {districts.find((d) => String(d.id) === districtId)?.name}
                                    </dd>
                                </div>
                                {locationDescription && (
                                    <div className="flex justify-between gap-4">
                                        <dt style={{ color: 'var(--text-secondary)' }}>Location</dt>
                                        <dd className="text-right font-medium">{locationDescription}</dd>
                                    </div>
                                )}
                                <Separator style={{ background: 'var(--border)' }} />
                                <div>
                                    <dt className="mb-1" style={{ color: 'var(--text-secondary)' }}>
                                        Description
                                    </dt>
                                    <dd className="leading-relaxed">{description}</dd>
                                </div>
                                <Separator style={{ background: 'var(--border)' }} />
                                <div className="flex justify-between gap-4">
                                    <dt style={{ color: 'var(--text-secondary)' }}>Photos</dt>
                                    <dd className="text-right font-medium">
                                        {photos.filter((p) => !p.error).length} attached
                                    </dd>
                                </div>
                                <div className="flex justify-between gap-4">
                                    <dt style={{ color: 'var(--text-secondary)' }}>Filing as</dt>
                                    <dd className="text-right font-medium">
                                        {isAnonymous ? 'Anonymous' : contactName || 'Named complainant'}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {photos.filter((p) => !p.error).length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {photos
                                    .filter((p) => !p.error)
                                    .map((p) => (
                                        <img
                                            key={p.id}
                                            src={p.previewUrl}
                                            alt={p.file.name}
                                            className="h-14 w-14 rounded-md border object-cover"
                                            style={{ borderColor: 'var(--border)' }}
                                        />
                                    ))}
                            </div>
                        )}

                        {error && (
                            <Alert style={{ borderColor: DANGER }}>
                                <AlertDescription style={{ color: DANGER }}>{error}</AlertDescription>
                            </Alert>
                        )}
                    </div>
                )}

                <div className="mt-8 flex items-center justify-between">
                    <Button
                        variant="ghost"
                        disabled={step === 1}
                        onClick={() => setStep((s) => s - 1)}
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        <ArrowLeft className="mr-1 h-4 w-4" /> Back
                    </Button>
                    {step < STEP_LABELS.length ? (
                        <Button
                            disabled={!canProceed}
                            onClick={() => setStep((s) => s + 1)}
                            style={{ background: 'var(--accent)', color: '#14213D' }}
                        >
                            Continue <ArrowRight className="ml-1 h-4 w-4" />
                        </Button>
                    ) : (
                        <Button
                            disabled={submitting}
                            onClick={handleSubmit}
                            style={{ background: 'var(--accent)', color: '#14213D' }}
                        >
                            {submitting ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" /> Submitting...
                                </>
                            ) : (
                                <>
                                    Submit grievance <ArrowRight className="ml-1 h-4 w-4" />
                                </>
                            )}
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

// ---------------------------------------------------------------------------
// Tracking panel
// ---------------------------------------------------------------------------

function Timeline({ history, currentStatus }: { history: StatusHistoryEntry[]; currentStatus: GrievanceStatus }) {
    const { statusMeta, statusOrder } = useGrievanceMeta();
    const byStatus = new Map(history.map((h) => [h.to_status, h]));
    const currentIndex = statusOrder.indexOf(currentStatus);

    return (
        <div className="space-y-0">
            {statusOrder.map((status, i) => {
                const entry = byStatus.get(status);
                const done = currentIndex >= 0 ? i <= currentIndex : Boolean(entry);
                const isCurrent = status === currentStatus;
                const meta = statusMeta[status];
                const Icon = meta.icon;
                const isLast = i === statusOrder.length - 1;

                return (
                    <div key={status} className="flex gap-3">
                        <div className="flex flex-col items-center">
                            <div
                                className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                                style={{
                                    background: done ? meta.color : 'transparent',
                                    border: `1.5px solid ${done ? meta.color : 'var(--border)'}`,
                                }}
                            >
                                <Icon className="h-3.5 w-3.5" style={{ color: done ? '#fff' : 'var(--text-secondary)' }} />
                            </div>
                            {!isLast && (
                                <div
                                    className="w-px flex-1"
                                    style={{ background: i < currentIndex ? meta.color : 'var(--border)', minHeight: 28 }}
                                />
                            )}
                        </div>
                        <div className="pb-7">
                            <p
                                className="text-sm font-semibold"
                                style={{ color: done ? 'var(--text-primary)' : 'var(--text-secondary)' }}
                            >
                                {meta.label}
                                {isCurrent && (
                                    <span className="ml-2 text-xs font-normal" style={{ color: 'var(--text-secondary)' }}>
                                        current stage
                                    </span>
                                )}
                            </p>
                            {entry?.changed_at && (
                                <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                                    {formatDateTime(entry.changed_at)}
                                </p>
                            )}
                            {entry?.note && (
                                <p className="mt-1 text-xs leading-relaxed" style={{ color: 'var(--text-secondary)' }}>
                                    {entry.note}
                                </p>
                            )}
                        </div>
                    </div>
                );
            })}
            {(currentStatus === 'escalated' || currentStatus === 'rejected' || currentStatus === 'reopened') && (
                <div className="flex gap-3">
                    <div
                        className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                        style={{ background: statusMeta[currentStatus].color, border: '1.5px solid transparent' }}
                    >
                        {React.createElement(statusMeta[currentStatus].icon, { className: 'h-3.5 w-3.5', style: { color: '#fff' } })}
                    </div>
                    <div>
                        <p className="text-sm font-semibold">{statusMeta[currentStatus].label}</p>
                        <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                            {statusMeta[currentStatus].description}
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
}
function useNow(refreshMs = 60000) {
    const [now, setNow] = useState<number | null>(null);

    useEffect(() => {
        let cancelled = false;

        const tick = () => {
            if (!cancelled) {
                setNow(Date.now());
            }
        };

        // deferred so it isn't a synchronous setState call inside the effect body
        Promise.resolve().then(tick);
        const id = setInterval(tick, refreshMs);

        return () => {
            cancelled = true;
            clearInterval(id);
        };
    }, [refreshMs]);

    return now;
}
function SlaBanner({ grievance }: { grievance: TrackedGrievance }) {
    const now = useNow();

    if (
        !grievance.sla_due_at ||
        grievance.status === 'resolved' ||
        grievance.status === 'closed'
    ) {
        return null;
    }

    if (now === null) {
        return null; // haven't read the clock yet — render nothing rather than guess
    }

    const due = new Date(grievance.sla_due_at).getTime();
    const daysLeft = Math.ceil((due - now) / 86400000);
    const overdue = daysLeft < 0;

    return (
        <Alert
            style={{
                borderColor: overdue ? DANGER : 'var(--border)',
                background: overdue ? 'rgba(179,38,30,0.06)' : 'var(--bg-page)',
            }}
        >
            <AlertDescription
                style={{ color: overdue ? DANGER : 'var(--text-secondary)' }}
            >
                {overdue
                    ? `This case is past its target response date (${formatDate(grievance.sla_due_at)}).`
                    : `Target response by ${formatDate(grievance.sla_due_at)} - ${daysLeft} day${daysLeft === 1 ? '' : 's'} left.`}
            </AlertDescription>
        </Alert>
    );
}

function AttachmentsGallery({ attachments }: { attachments: AttachmentT[] }) {
    if (attachments.length === 0) {
        return null;
    }

    return (
        <div>
            <h3 className="mb-3 flex items-center gap-1.5 text-sm font-semibold">
                <ImagePlus className="h-3.5 w-3.5" /> Photos submitted
            </h3>
            <div className="flex flex-wrap gap-2">
                {attachments.map((a) => (
                    <a key={a.id} href={a.url} target="_blank" rel="noreferrer">
                        <img
                            src={a.url}
                            alt={a.original_filename}
                            className="h-16 w-16 rounded-md border object-cover"
                            style={{ borderColor: 'var(--border)' }}
                        />
                    </a>
                ))}
            </div>
        </div>
    );
}

function MessageThread({ grievance, onSent }: { grievance: TrackedGrievance; onSent: (m: GrievanceMessageT) => void }) {
    const [draft, setDraft] = useState('');
    const [sending, setSending] = useState(false);
    const closed = grievance.status === 'closed' || grievance.status === 'rejected';

    async function handleSend() {
        if (!draft.trim()) {
            return;
        }

        setSending(true);
        const msg = await sendCitizenMessage(grievance.reference_number, draft.trim());
        onSent(msg);
        setDraft('');
        setSending(false);
    }

    return (
        <div>
            <h3 className="mb-3 text-sm font-semibold">Messages</h3>
            <div className="mb-3 max-h-72 space-y-3 overflow-y-auto rounded-md border p-4" style={{ borderColor: 'var(--border)' }}>
                {grievance.messages.length === 0 && (
                    <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                        No messages yet.
                    </p>
                )}
                {grievance.messages.map((m) => (
                    <div key={m.id} className={`flex ${m.sender === 'citizen' ? 'justify-end' : 'justify-start'}`}>
                        <div
                            className="max-w-[80%] rounded-md px-3.5 py-2.5"
                            style={{
                                background: m.sender === 'citizen' ? 'var(--accent)' : 'var(--bg-raised)',
                                color: m.sender === 'citizen' ? '#14213D' : 'var(--text-primary)',
                            }}
                        >
                            <p className="text-sm leading-relaxed">{m.body}</p>
                            <p
                                className="mt-1 text-[10px]"
                                style={{ color: m.sender === 'citizen' ? 'rgba(20,33,61,0.65)' : 'var(--text-secondary)' }}
                            >
                                {m.sender === 'citizen' ? 'You' : 'Officer'} - {formatDateTime(m.created_at)}
                            </p>
                        </div>
                    </div>
                ))}
            </div>
            {!closed ? (
                <div className="flex gap-2">
                    <Input
                        value={draft}
                        onChange={(e) => setDraft(e.target.value)}
                        placeholder="Write a reply..."
                        onKeyDown={(e) => e.key === 'Enter' && handleSend()}
                    />
                    <Button
                        onClick={handleSend}
                        disabled={sending || !draft.trim()}
                        style={{ background: 'var(--accent)', color: '#14213D' }}
                    >
                        {sending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
                    </Button>
                </div>
            ) : (
                <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                    This case is closed and no longer accepting messages.
                </p>
            )}
        </div>
    );
}

function RatingWidget({ grievance }: { grievance: TrackedGrievance }) {
    const [rating, setRating] = useState(grievance.satisfaction_rating ?? 0);
    const [hover, setHover] = useState(0);
    const [submitted, setSubmitted] = useState(Boolean(grievance.satisfaction_rating));

    async function handleRate(value: number) {
        setRating(value);
        await submitRating(grievance.reference_number, value);
        setSubmitted(true);
    }

    return (
        <div className="rounded-md border p-5" style={{ borderColor: 'var(--resolved)', background: 'var(--resolved-bg)' }}>
            <p className="mb-1 flex items-center gap-2 text-sm font-semibold">
                <CheckCircle2 className="h-4 w-4" style={{ color: 'var(--resolved)' }} />
                Resolution recorded
            </p>
            {grievance.resolution?.summary && (
                <p className="mb-3 text-sm leading-relaxed" style={{ color: 'var(--text-secondary)' }}>
                    {grievance.resolution.summary}
                </p>
            )}
            {grievance.resolution?.resolved_at && (
                <p className="mb-4 text-xs" style={{ color: 'var(--text-secondary)' }}>
                    Resolved {formatDate(grievance.resolution.resolved_at)}
                </p>
            )}
            <Separator className="mb-4" style={{ background: 'var(--border)' }} />
            {submitted ? (
                <p className="text-sm">Thank you for rating your experience.</p>
            ) : (
                <>
                    <p className="mb-2 text-sm font-medium">How satisfied are you with this outcome?</p>
                    <div className="flex gap-1">
                        {[1, 2, 3, 4, 5].map((v) => (
                            <button
                                key={v}
                                type="button"
                                onMouseEnter={() => setHover(v)}
                                onMouseLeave={() => setHover(0)}
                                onClick={() => handleRate(v)}
                                aria-label={`Rate ${v} out of 5`}
                            >
                                <Star
                                    className="h-6 w-6"
                                    style={{
                                        color: (hover || rating) >= v ? 'var(--accent)' : 'var(--border)',
                                        fill: (hover || rating) >= v ? 'var(--accent)' : 'transparent',
                                    }}
                                />
                            </button>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}

function TrackGrievancePanel({ prefillRef }: { prefillRef: string | null }) {
    const [reference, setReference] = useState(prefillRef ?? '');
    const [contact, setContact] = useState('');
    const [loading, setLoading] = useState(false);
    const [notFound, setNotFound] = useState(false);
    const [grievance, setGrievance] = useState<TrackedGrievance | null>(null);

    async function performTrack(ref: string, contactValue: string) {
        if (!ref.trim()) {
            return;
        }

        setLoading(true);
        setNotFound(false);

        const result = await trackGrievance(ref.trim(), contactValue.trim());

        setGrievance(result);
        setNotFound(!result);
        setLoading(false);
    }

    function handleTrack() {
        performTrack(reference, contact);
    }

    React.useEffect(() => {
        if (!prefillRef) {
            return;
        }

        let cancelled = false;

        Promise.resolve().then(() => {
            if (!cancelled) {
                performTrack(prefillRef, '');
            }
        });

        return () => {
            cancelled = true;
        };
    }, [prefillRef]);

    return (
        <div className="space-y-6">
            <Card
                className="border"
                style={{
                    borderColor: 'var(--border)',
                    background: 'var(--bg-raised)',
                }}
            >
                <CardContent className="p-6">
                    <div className="grid gap-4 sm:grid-cols-[2fr_2fr_auto] sm:items-end">
                        <div>
                            <Label className="mb-2 block text-sm font-semibold">
                                Reference number
                            </Label>
                            <Input
                                value={reference}
                                onChange={(e) => setReference(e.target.value)}
                                placeholder="e.g. GRM-2026-01147"
                                className="font-mono"
                            />
                        </div>
                        <div>
                            <Label className="mb-2 block text-sm font-semibold">
                                Phone or email (if provided)
                            </Label>
                            <Input
                                value={contact}
                                onChange={(e) => setContact(e.target.value)}
                                placeholder="Leave blank if filed anonymously"
                            />
                        </div>
                        <Button
                            onClick={handleTrack}
                            disabled={loading || !reference.trim()}
                            style={{
                                background: 'var(--accent)',
                                color: '#14213D',
                            }}
                        >
                            {loading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <Search className="h-4 w-4" />
                            )}
                            <span className="ml-1.5">Track</span>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            {notFound && (
                <Alert style={{ borderColor: DANGER }}>
                    <AlertDescription style={{ color: DANGER }}>
                        We couldn't find a case matching that reference number
                        and contact. Double-check both and try again.
                    </AlertDescription>
                </Alert>
            )}

            {grievance && (
                <div className="space-y-6">
                    <Card
                        className="border"
                        style={{
                            borderColor: 'var(--border)',
                            background: 'var(--bg-page)',
                        }}
                    >
                        <CardContent className="p-6">
                            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p
                                        className="font-mono text-xs"
                                        style={{
                                            color: 'var(--text-secondary)',
                                        }}
                                    >
                                        {grievance.reference_number}
                                    </p>
                                    <h2 className="font-display text-xl font-semibold">
                                        {grievance.category_name}
                                    </h2>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <StatusBadge status={grievance.status} />
                                    <PriorityBadge
                                        priority={grievance.priority}
                                    />
                                </div>
                            </div>
                            <p
                                className="mb-4 text-sm leading-relaxed"
                                style={{ color: 'var(--text-secondary)' }}
                            >
                                {grievance.description}
                            </p>
                            <div
                                className="flex flex-wrap gap-x-6 gap-y-1 text-xs"
                                style={{ color: 'var(--text-secondary)' }}
                            >
                                {grievance.division_name && (
                                    <span>
                                        Handled by {grievance.division_name}
                                    </span>
                                )}
                                {grievance.location_description && (
                                    <span className="flex items-center gap-1">
                                        <MapPin className="h-3 w-3" />{' '}
                                        {grievance.location_description}
                                    </span>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <SlaBanner grievance={grievance} />

                    {grievance.escalation && (
                        <Alert
                            style={{
                                borderColor: DANGER,
                                background: 'rgba(179,38,30,0.06)',
                            }}
                        >
                            <ShieldAlert
                                className="h-4 w-4"
                                style={{ color: DANGER }}
                            />
                            <AlertDescription style={{ color: DANGER }}>
                                Escalated to {grievance.escalation.level} on{' '}
                                {formatDate(grievance.escalation.escalated_at)}:{' '}
                                {grievance.escalation.reason}
                            </AlertDescription>
                        </Alert>
                    )}

                    <div className="grid gap-6 md:grid-cols-2">
                        <Card
                            className="border"
                            style={{
                                borderColor: 'var(--border)',
                                background: 'var(--bg-raised)',
                            }}
                        >
                            <CardContent className="space-y-6 p-6">
                                <div>
                                    <h3 className="mb-4 text-sm font-semibold">
                                        Case timeline
                                    </h3>
                                    <Timeline
                                        history={grievance.history}
                                        currentStatus={grievance.status}
                                    />
                                </div>
                                <AttachmentsGallery
                                    attachments={grievance.attachments}
                                />
                            </CardContent>
                        </Card>

                        <Card
                            className="border"
                            style={{
                                borderColor: 'var(--border)',
                                background: 'var(--bg-raised)',
                            }}
                        >
                            <CardContent className="p-6">
                                <MessageThread
                                    grievance={grievance}
                                    onSent={(m) =>
                                        setGrievance((g) =>
                                            g
                                                ? {
                                                      ...g,
                                                      messages: [
                                                          ...g.messages,
                                                          m,
                                                      ],
                                                  }
                                                : g,
                                        )
                                    }
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {(grievance.status === 'resolved' ||
                        grievance.status === 'closed') && (
                        <RatingWidget grievance={grievance} />
                    )}
                </div>
            )}
        </div>
    );
}

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

function GrievanceHubContent() {
    const { loading, error } = useGrievanceMeta();
    const [tab, setTab] = useState<'file' | 'track'>('file');
    const [prefillRef, setPrefillRef] = useState<string | null>(null);

    return (
        <section className="mx-auto max-w-4xl px-6 py-16">
            <SectionHeader
                eyebrow="GRMS - Grievance Redress"
                title={tab === 'file' ? 'File a grievance' : 'Track your grievance'}
                sub={
                    tab === 'file'
                        ? "Tell us what happened. It takes about three minutes and you'll get a reference number to follow up with."
                        : 'Enter your reference number to see the current status, updates, and any response from your case officer.'
                }
            />

            {error ? (
                <MetaErrorState message={error} />
            ) : loading ? (
                <MetaLoadingState />
            ) : (
                <Tabs value={tab} onValueChange={(v) => setTab(v as 'file' | 'track')} className="w-full">
                    <TabsList className="mx-auto mb-8 grid max-w-xs grid-cols-2" style={{ background: 'var(--bg-raised)' }}>
                        <TabsTrigger value="file" className="text-sm">
                            File a grievance
                        </TabsTrigger>
                        <TabsTrigger value="track" className="text-sm">
                            Track status
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="file">
                        <FileGrievanceWizard
                            onFiled={(ref) => {
                                setPrefillRef(ref);
                                setTab('track');
                            }}
                        />
                    </TabsContent>

                    <TabsContent value="track">
                        <TrackGrievancePanel prefillRef={prefillRef} />
                    </TabsContent>
                </Tabs>
            )}

            <div className="mt-10 flex items-center justify-center gap-2 text-xs" style={{ color: 'var(--text-secondary)' }}>
                <ShieldCheck className="h-3.5 w-3.5" />
                Your information is kept confidential and only shared with the office handling your case.
            </div>
        </section>
    );
}

function GrievanceHub() {
    return (
        <GrievanceMetaProvider>
            <GrievanceHubContent />
        </GrievanceMetaProvider>
    );
}

export default function FileGrievancePage() {
    return (
        <PageShell>
            <NavBar />
            <GrievanceHub />
            <Footer />
        </PageShell>
    );
}
