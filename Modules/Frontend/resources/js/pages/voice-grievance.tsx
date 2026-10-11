import { Button } from '@/components/ui/button';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Mic, Square } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import {
    Footer,
    HOME_URL,
    NavBar,
    PageShell,
    useI18n,
} from './site-shared';

interface VoiceGrievancePageProps {
    received?: boolean;
    referenceNo?: string;
}

interface VoiceFormData {
    audio: File | null;
    duration_seconds: number | null;
    language_selected: string;
    contact_phone: string;
}

const MAX_AUDIO_BYTES = 20 * 1024 * 1024;
const MAX_DURATION_SECONDS = 600;

export default function VoiceGrievancePage({
    received = false,
    referenceNo,
}: VoiceGrievancePageProps) {
    const { t } = useI18n();
    const [recording, setRecording] = useState(false);
    const [recordingSeconds, setRecordingSeconds] = useState(0);
    const [recordingError, setRecordingError] = useState('');
    const recorderRef = useRef<MediaRecorder | null>(null);
    const streamRef = useRef<MediaStream | null>(null);
    const chunksRef = useRef<BlobPart[]>([]);
    const recordingStartedAtRef = useRef<number>(0);
    const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
    const { data, setData, post, processing, errors } = useForm<VoiceFormData>({
        audio: null,
        duration_seconds: null,
        language_selected: 'st',
        contact_phone: '',
    });

    const audioPreviewUrl = useMemo(
        () => (data.audio ? URL.createObjectURL(data.audio) : ''),
        [data.audio],
    );

    useEffect(() => {
        return () => {
            if (timerRef.current) {
                clearInterval(timerRef.current);
            }

            if (recorderRef.current?.state === 'recording') {
                recorderRef.current.ondataavailable = null;
                recorderRef.current.onstop = null;
                recorderRef.current.stop();
            }

            streamRef.current?.getTracks().forEach((track) => track.stop());
        };
    }, []);

    useEffect(() => {
        return () => {
            if (audioPreviewUrl) {
                URL.revokeObjectURL(audioPreviewUrl);
            }
        };
    }, [audioPreviewUrl]);

    const stopRecording = () => {
        if (recorderRef.current?.state === 'recording') {
            recorderRef.current.stop();
        }
    };

    const startRecording = async () => {
        setRecordingError('');

        if (
            !navigator.mediaDevices?.getUserMedia ||
            typeof MediaRecorder === 'undefined'
        ) {
            setRecordingError(t.voicePage.microphoneError);

            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: true,
            });
            streamRef.current = stream;
            chunksRef.current = [];

            const mimeType = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/ogg;codecs=opus',
                'audio/mp4',
            ].find((type) => MediaRecorder.isTypeSupported(type));
            const recorder = new MediaRecorder(
                stream,
                mimeType ? { mimeType } : undefined,
            );
            recorderRef.current = recorder;
            recordingStartedAtRef.current = Date.now();
            recorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    chunksRef.current.push(event.data);

                    const totalBytes = chunksRef.current.reduce(
                        (total, chunk) =>
                            total +
                            (chunk instanceof Blob ? chunk.size : 0),
                        0,
                    );

                    if (totalBytes > MAX_AUDIO_BYTES) {
                        setRecordingError(t.voicePage.fileError);
                        stopRecording();
                    }
                }
            };
            recorder.onerror = () => {
                setRecordingError(t.voicePage.microphoneError);
                stopRecording();
            };
            recorder.onstop = () => {
                const duration = Math.min(
                    MAX_DURATION_SECONDS,
                    Math.max(
                        1,
                        Math.round(
                            (Date.now() - recordingStartedAtRef.current) /
                                1000,
                        ),
                    ),
                );
                const blob = new Blob(chunksRef.current, {
                    type: recorder.mimeType || 'audio/webm',
                });
                const extension = blob.type.includes('mp4')
                    ? 'm4a'
                    : blob.type.includes('ogg')
                      ? 'ogg'
                      : 'webm';

                if (blob.size > MAX_AUDIO_BYTES) {
                    setRecordingError(t.voicePage.fileError);
                    setData('audio', null);
                } else if (blob.size > 0) {
                    setData('audio', new File([blob], `voice-grievance.${extension}`, { type: blob.type }));
                    setData('duration_seconds', duration);
                } else {
                    setRecordingError(t.voicePage.microphoneError);
                }

                stream.getTracks().forEach((track) => track.stop());
                streamRef.current = null;
                setRecording(false);

                if (timerRef.current) {
                    clearInterval(timerRef.current);
                    timerRef.current = null;
                }
            };
            recorder.start(1000);
            setData('audio', null);
            setData('duration_seconds', null);
            setRecordingSeconds(0);
            setRecording(true);
            timerRef.current = setInterval(() => {
                const seconds = Math.floor(
                    (Date.now() - recordingStartedAtRef.current) / 1000,
                );
                setRecordingSeconds(seconds);

                if (seconds >= MAX_DURATION_SECONDS) {
                    stopRecording();
                }
            }, 1000);
        } catch {
            streamRef.current?.getTracks().forEach((track) => track.stop());
            streamRef.current = null;
            setRecordingError(t.voicePage.microphoneError);
        }
    };

    const selectAudio = (file?: File) => {
        setRecordingError('');

        if (!file) {
            setData('audio', null);
            setData('duration_seconds', null);

            return;
        }

        if (
            !file.type.startsWith('audio/') ||
            file.size > MAX_AUDIO_BYTES ||
            !/\.(mp3|wav|m4a|aac|ogg|oga|webm|mp4)$/i.test(file.name)
        ) {
            setRecordingError(t.voicePage.fileError);
            setData('audio', null);
            setData('duration_seconds', null);

            return;
        }

        setData('audio', file);
        setData('duration_seconds', null);
    };

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/lodge-by-voice', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setData('audio', null);
                setData('duration_seconds', null);
            },
        });
    };

    const formatDuration = (seconds: number) =>
        `${Math.floor(seconds / 60)
            .toString()
            .padStart(2, '0')}:${(seconds % 60).toString().padStart(2, '0')}`;

    return (
        <PageShell>
            <Head title={received ? t.voicePage.receivedTitle : t.voicePage.title} />
            <NavBar />
            <main id="main-content" tabIndex={-1}>
                <section className="mx-auto max-w-3xl px-6 py-12 md:py-16">
                    <Button asChild variant="ghost" className="mb-6">
                        <Link href={HOME_URL}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            {t.voicePage.backHome}
                        </Link>
                    </Button>

                    {received ? (
                        <div
                            role="status"
                            aria-live="polite"
                            className="rounded-lg border p-6 md:p-8"
                            style={{
                                borderColor: 'var(--border)',
                                background: 'var(--bg-raised)',
                            }}
                        >
                            <h1 className="mb-3 font-display text-3xl font-semibold">
                                {t.voicePage.receivedTitle}
                            </h1>
                            <p className="mb-6 text-base leading-relaxed text-muted-foreground">
                                {t.voicePage.receivedDescription}
                            </p>
                            <p className="text-sm font-medium">
                                {t.voicePage.reference}
                            </p>
                            <p className="mt-1 font-mono text-xl font-semibold">
                                {referenceNo}
                            </p>
                            <p className="mt-3 text-sm text-muted-foreground">
                                {t.voicePage.keepReference}
                            </p>
                        </div>
                    ) : (
                        <div
                            className="rounded-lg border p-6 md:p-8"
                            style={{
                                borderColor: 'var(--border)',
                                background: 'var(--bg-raised)',
                            }}
                        >
                            <h1 className="mb-3 font-display text-3xl font-semibold">
                                {t.voicePage.title}
                            </h1>
                            <p className="mb-4 text-base leading-relaxed text-muted-foreground">
                                {t.voicePage.description}
                            </p>
                            <p className="mb-6 rounded-md bg-muted p-4 text-sm leading-relaxed">
                                {t.voicePage.instructions}
                            </p>
                            <form className="space-y-6" onSubmit={submit}>
                                <div className="space-y-2">
                                    <label
                                        htmlFor="language_selected"
                                        className="block text-sm font-semibold"
                                    >
                                        {t.voicePage.language}
                                    </label>
                                    <select
                                        id="language_selected"
                                        value={data.language_selected}
                                        onChange={(event) =>
                                            setData(
                                                'language_selected',
                                                event.target.value,
                                            )
                                        }
                                        className="min-h-12 w-full rounded-md border bg-background px-3 text-base"
                                    >
                                        <option value="st">
                                            {t.voicePage.languageSesotho}
                                        </option>
                                        <option value="en">
                                            {t.voicePage.languageEnglish}
                                        </option>
                                        <option value="zu">
                                            {t.voicePage.languageZulu}
                                        </option>
                                        <option value="xh">
                                            {t.voicePage.languageXhosa}
                                        </option>
                                        <option value="other">
                                            {t.voicePage.languageOther}
                                        </option>
                                    </select>
                                    {errors.language_selected && (
                                        <p role="alert" className="text-sm text-destructive">
                                            {errors.language_selected}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-3">
                                    <Button
                                        type="button"
                                        size="lg"
                                        className="min-h-14 w-full text-base"
                                        onClick={
                                            recording
                                                ? stopRecording
                                                : startRecording
                                        }
                                        aria-pressed={recording}
                                    >
                                        {recording ? (
                                            <Square className="mr-2 h-5 w-5" />
                                        ) : (
                                            <Mic className="mr-2 h-5 w-5" />
                                        )}
                                        {recording
                                            ? t.voicePage.stopRecording
                                            : t.voicePage.startRecording}
                                    </Button>
                                    <p
                                        aria-live="polite"
                                        className="text-sm text-muted-foreground"
                                    >
                                        {recording
                                            ? `${t.voicePage.recording}: ${formatDuration(recordingSeconds)}`
                                            : ''}
                                    </p>
                                    {recordingError && (
                                        <p
                                            role="alert"
                                            className="text-sm text-destructive"
                                        >
                                            {recordingError}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="audio"
                                        className="block text-sm font-semibold"
                                    >
                                        {t.voicePage.chooseAudio}
                                    </label>
                                    <input
                                        id="audio"
                                        type="file"
                                        accept="audio/*,.mp3,.wav,.m4a,.aac,.ogg,.oga,.webm,.mp4"
                                        disabled={recording}
                                        onChange={(event) =>
                                            selectAudio(
                                                event.currentTarget.files?.[0],
                                            )
                                        }
                                        className="block min-h-12 w-full rounded-md border bg-background p-2 text-sm"
                                    />
                                    {data.audio && (
                                        <>
                                            <p className="text-sm text-muted-foreground">
                                                {t.voicePage.selectedAudio}:{' '}
                                                {data.audio.name}
                                            </p>
                                            <audio
                                                controls
                                                preload="metadata"
                                                src={audioPreviewUrl}
                                                className="w-full"
                                                aria-label={t.voicePage.selectedAudio}
                                            />
                                        </>
                                    )}
                                    {errors.audio && (
                                        <p role="alert" className="text-sm text-destructive">
                                            {errors.audio}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="contact_phone"
                                        className="block text-sm font-semibold"
                                    >
                                        {t.voicePage.phone}
                                    </label>
                                    <input
                                        id="contact_phone"
                                        type="tel"
                                        autoComplete="tel"
                                        value={data.contact_phone}
                                        onChange={(event) =>
                                            setData(
                                                'contact_phone',
                                                event.target.value,
                                            )
                                        }
                                        className="min-h-12 w-full rounded-md border bg-background px-3 text-base"
                                    />
                                    <p className="text-sm text-muted-foreground">
                                        {t.voicePage.phoneHelp}
                                    </p>
                                    {errors.contact_phone && (
                                        <p role="alert" className="text-sm text-destructive">
                                            {errors.contact_phone}
                                        </p>
                                    )}
                                </div>

                                <p className="text-sm text-muted-foreground">
                                    {t.voicePage.privacy}
                                </p>
                                <Button
                                    type="submit"
                                    size="lg"
                                    className="min-h-14 w-full text-base"
                                    disabled={processing || recording || !data.audio}
                                >
                                    {processing
                                        ? t.voicePage.submitting
                                        : t.voicePage.submit}
                                </Button>
                            </form>
                        </div>
                    )}
                </section>
            </main>
            <Footer />
        </PageShell>
    );
}
