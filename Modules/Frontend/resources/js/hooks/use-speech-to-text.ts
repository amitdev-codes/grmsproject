import { useEffect, useRef, useState, useSyncExternalStore } from 'react';

interface SpeechRecognitionAlternativeLike {
    transcript: string;
}

interface SpeechRecognitionResultLike extends ArrayLike<SpeechRecognitionAlternativeLike> {
    isFinal: boolean;
}

interface SpeechRecognitionEventLike {
    resultIndex: number;
    results: ArrayLike<SpeechRecognitionResultLike>;
}

interface SpeechRecognitionErrorEventLike {
    error: string;
}

interface SpeechRecognitionLike {
    continuous: boolean;
    interimResults: boolean;
    lang: string;
    onend: (() => void) | null;
    onerror: ((event: SpeechRecognitionErrorEventLike) => void) | null;
    onresult: ((event: SpeechRecognitionEventLike) => void) | null;
    start: () => void;
    stop: () => void;
}

type SpeechRecognitionConstructor = new () => SpeechRecognitionLike;

interface SpeechRecognitionWindow extends Window {
    SpeechRecognition?: SpeechRecognitionConstructor;
    webkitSpeechRecognition?: SpeechRecognitionConstructor;
}

function subscribeToSpeechSupport(): () => void {
    return () => {};
}

function getSpeechSupportSnapshot(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    const speechWindow = window as SpeechRecognitionWindow;

    return Boolean(
        speechWindow.SpeechRecognition ??
            speechWindow.webkitSpeechRecognition,
    );
}

function getSpeechSupportServerSnapshot(): boolean {
    return false;
}

export function useSpeechToText(
    language: string,
    onTranscript: (transcript: string) => void,
) {
    const supported = useSyncExternalStore(
        subscribeToSpeechSupport,
        getSpeechSupportSnapshot,
        getSpeechSupportServerSnapshot,
    );
    const [listening, setListening] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const recognitionRef = useRef<SpeechRecognitionLike | null>(null);
    const activeRef = useRef(false);
    const onTranscriptRef = useRef(onTranscript);

    useEffect(() => {
        onTranscriptRef.current = onTranscript;
    }, [onTranscript]);

    useEffect(() => {
        const speechWindow = window as SpeechRecognitionWindow;
        const Recognition =
            speechWindow.SpeechRecognition ??
            speechWindow.webkitSpeechRecognition;

        if (!Recognition) {
            return;
        }

        const recognition = new Recognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = language;
        recognition.onresult = (event) => {
            for (let index = event.resultIndex; index < event.results.length; index++) {
                const result = event.results[index];

                if (result.isFinal) {
                    const transcript = result[0]?.transcript.trim();

                    if (transcript) {
                        onTranscriptRef.current(transcript);
                    }
                }
            }
        };
        recognition.onerror = (event) => {
            setError(event.error || 'speech-error');
        };
        recognition.onend = () => {
            activeRef.current = false;
            setListening(false);
        }
        recognitionRef.current = recognition;

        return () => {
            recognition.onresult = null;
            recognition.onerror = null;
            recognition.onend = null;

            if (activeRef.current) {
                recognition.stop();
                activeRef.current = false;
            }

            recognitionRef.current = null;
        };
    }, [language]);

    const start = () => {
        if (!recognitionRef.current || activeRef.current) {
            return;
        }

        setError(null);

        try {
            recognitionRef.current.start();
            activeRef.current = true;
            setListening(true);
        } catch {
            setError('speech-error');
        }
    };

    const stop = () => recognitionRef.current?.stop();

    return { supported, listening, error, start, stop };
}
