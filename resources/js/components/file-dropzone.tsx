import { UploadCloud, X, FileIcon } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { useDropzone  } from 'react-dropzone';
import type {Accept} from 'react-dropzone';
import { cn } from '@/lib/utils';

interface PreviewItem {
    file: File;
    preview: string; // object URL for images, '' for non-image files
}

export interface ExistingFileItem {
    id: number | string;
    url: string;
    name: string;
    /** Optional: used to pick an icon vs image preview for non-images */
    isImage?: boolean;
}

export interface FileDropzoneProps {
    multiple?: boolean;
    maxFiles?: number;
    accept?: Accept;
    maxSizeMB?: number;
    value: File[];
    onChange: (files: File[]) => void;

    /** Single-mode existing file (unchanged, kept for backwards compat) */
    existingPreviewUrl?: string | null;
    onRemoveExisting?: () => void;

    /** Multi-mode existing files (already on the server) */
    existingFiles?: ExistingFileItem[];
    onRemoveExistingFile?: (id: number | string) => void;

    label?: string;
    helperText?: string;
    className?: string;
}

/**
 * Drag-and-drop file picker built on react-dropzone.
 * - Single mode: one file, with image preview + remove (also handles an
 *   "existing" server-side file via existingPreviewUrl/onRemoveExisting).
 * - Multiple mode: a grid of previews, each individually removable.
 */
export function FileDropzone({
    multiple = false,
    maxFiles = multiple ? 5 : 1,
    accept,
    maxSizeMB = 5,
    value,
    onChange,
    existingPreviewUrl,
    onRemoveExisting,
    existingFiles = [],
    onRemoveExistingFile,
    label,
    helperText,
    className,
}: FileDropzoneProps) {
    const [previews, setPreviews] = useState<PreviewItem[]>([]);

    useEffect(() => {
        const next = value.map((file) => ({
            file,
            preview: file.type.startsWith('image/')
                ? URL.createObjectURL(file)
                : '',
        }));
        setPreviews(next);

        return () =>
            next.forEach((p) => p.preview && URL.revokeObjectURL(p.preview));
    }, [value]);

    const remainingSlots = maxFiles - existingFiles.length - value.length;

    const onDrop = useCallback(
        (accepted: File[]) => {
            if (accepted.length === 0) {
                return;
            }

            if (multiple) {
                onChange(
                    [...value, ...accepted].slice(
                        0,
                        Math.max(0, maxFiles - existingFiles.length),
                    ),
                );
            } else {
                onChange(accepted.slice(0, 1));
            }
        },
        [multiple, maxFiles, value, onChange, existingFiles.length],
    );

    const { getRootProps, getInputProps, isDragActive, fileRejections } =
        useDropzone({
            onDrop,
            multiple,
            maxFiles: multiple
                ? Math.max(0, maxFiles - existingFiles.length)
                : maxFiles,
            accept,
            maxSize: maxSizeMB * 1024 * 1024,
            disabled: multiple && remainingSlots <= 0,
        });

    const removeAt = (index: number) =>
        onChange(value.filter((_, i) => i !== index));

    const showingExisting =
        !multiple && value.length === 0 && !!existingPreviewUrl;

    return (
        <div className={cn('flex flex-col gap-2', className)}>
            {label && <span className="text-sm font-medium">{label}</span>}

            <div
                {...getRootProps()}
                className={cn(
                    'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-md border border-dashed p-6 text-center transition-colors',
                    isDragActive
                        ? 'border-primary bg-primary/5'
                        : 'border-muted-foreground/25 hover:bg-muted/40',
                    multiple &&
                        remainingSlots <= 0 &&
                        'cursor-not-allowed opacity-50',
                )}
            >
                <input {...getInputProps()} />
                <UploadCloud className="h-8 w-8 text-muted-foreground" />
                <p className="text-sm text-muted-foreground">
                    {multiple && remainingSlots <= 0
                        ? 'Maximum files reached'
                        : isDragActive
                          ? 'Drop the file(s) here…'
                          : 'Drag & drop, or click to select'}
                </p>
                {helperText && (
                    <p className="text-xs text-muted-foreground">
                        {helperText}
                    </p>
                )}
            </div>

            {fileRejections.length > 0 && (
                <p className="text-sm text-destructive">
                    {fileRejections[0].errors[0]?.message ??
                        'File was rejected.'}
                </p>
            )}

            {showingExisting && (
                <div className="group relative flex w-fit flex-col items-center gap-1 rounded-md border p-2">
                    <button
                        type="button"
                        onClick={() => onRemoveExisting?.()}
                        className="absolute -top-2 -right-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 shadow transition-opacity group-hover:opacity-100"
                        aria-label="Remove current file"
                    >
                        <X className="h-3 w-3" />
                    </button>
                    <img
                        src={existingPreviewUrl!}
                        alt="Current file"
                        className="h-24 w-24 rounded object-cover"
                    />
                    <span className="text-xs text-muted-foreground">
                        Current
                    </span>
                </div>
            )}

            {(existingFiles.length > 0 || previews.length > 0) && (
                <div
                    className={cn(
                        'grid gap-3',
                        multiple
                            ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4'
                            : 'grid-cols-1',
                    )}
                >
                    {existingFiles.map((f) => (
                        <div
                            key={`existing-${f.id}`}
                            className="group relative flex flex-col items-center gap-1 rounded-md border p-2"
                        >
                            <button
                                type="button"
                                onClick={() => onRemoveExistingFile?.(f.id)}
                                className="absolute -top-2 -right-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 shadow transition-opacity group-hover:opacity-100"
                                aria-label="Remove file"
                            >
                                <X className="h-3 w-3" />
                            </button>
                            {f.isImage !== false ? (
                                <img
                                    src={f.url}
                                    alt={f.name}
                                    className="h-24 w-24 rounded object-cover"
                                />
                            ) : (
                                <a
                                    href={f.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex h-24 w-24 items-center justify-center rounded bg-muted"
                                >
                                    <FileIcon className="h-8 w-8 text-muted-foreground" />
                                </a>
                            )}
                            <span
                                className="w-full truncate text-xs text-muted-foreground"
                                title={f.name}
                            >
                                {f.name}
                            </span>
                        </div>
                    ))}

                    {previews.map((p, index) => (
                        <div
                            key={index}
                            className="group relative flex flex-col items-center gap-1 rounded-md border p-2"
                        >
                            <button
                                type="button"
                                onClick={() => removeAt(index)}
                                className="absolute -top-2 -right-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 shadow transition-opacity group-hover:opacity-100"
                                aria-label="Remove file"
                            >
                                <X className="h-3 w-3" />
                            </button>
                            {p.preview ? (
                                <img
                                    src={p.preview}
                                    alt={p.file.name}
                                    className="h-24 w-24 rounded object-cover"
                                />
                            ) : (
                                <div className="flex h-24 w-24 items-center justify-center rounded bg-muted">
                                    <FileIcon className="h-8 w-8 text-muted-foreground" />
                                </div>
                            )}
                            <span
                                className="w-full truncate text-xs text-muted-foreground"
                                title={p.file.name}
                            >
                                {p.file.name}
                            </span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
