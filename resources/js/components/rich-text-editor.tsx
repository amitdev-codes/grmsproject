import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { Bold, Heading2, Italic, List, ListOrdered, Quote } from 'lucide-react';
import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface RichTextEditorProps {
    id: string;
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    label: string;
}

export function RichTextEditor({
    id,
    value,
    onChange,
    disabled = false,
    label,
}: RichTextEditorProps) {
    const editor = useEditor({
        extensions: [StarterKit],
        content: value,
        editable: !disabled,
        immediatelyRender: false,
        onUpdate: ({ editor: currentEditor }) => onChange(currentEditor.getHTML()),
        editorProps: {
            attributes: {
                id,
                'aria-label': label,
                class: 'min-h-48 px-3 py-2 text-sm outline-none [&_h2]:text-lg [&_h2]:font-semibold [&_h3]:font-semibold [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6 [&_blockquote]:border-l-2 [&_blockquote]:pl-4 [&_blockquote]:italic',
            },
        },
    });

    useEffect(() => {
        if (editor && editor.getHTML() !== value) {
            editor.commands.setContent(value || '', { emitUpdate: false });
        }
    }, [editor, value]);

    useEffect(() => {
        editor?.setEditable(!disabled);
    }, [disabled, editor]);

    if (!editor) {
        return (
            <div className="min-h-48 rounded-md border bg-background px-3 py-2 text-sm text-muted-foreground">
                {label}
            </div>
        );
    }

    const buttons = [
        { label: 'Bold', icon: Bold, action: () => editor.chain().focus().toggleBold().run(), active: editor.isActive('bold') },
        { label: 'Italic', icon: Italic, action: () => editor.chain().focus().toggleItalic().run(), active: editor.isActive('italic') },
        { label: 'Heading', icon: Heading2, action: () => editor.chain().focus().toggleHeading({ level: 2 }).run(), active: editor.isActive('heading', { level: 2 }) },
        { label: 'Bulleted list', icon: List, action: () => editor.chain().focus().toggleBulletList().run(), active: editor.isActive('bulletList') },
        { label: 'Numbered list', icon: ListOrdered, action: () => editor.chain().focus().toggleOrderedList().run(), active: editor.isActive('orderedList') },
        { label: 'Quote', icon: Quote, action: () => editor.chain().focus().toggleBlockquote().run(), active: editor.isActive('blockquote') },
    ];

    return (
        <div className={cn('overflow-hidden rounded-md border bg-background', disabled && 'opacity-70')}>
            <div className="flex flex-wrap gap-1 border-b p-1" role="toolbar" aria-label={`${label} formatting`}>
                {buttons.map(({ label: buttonLabel, icon: Icon, action, active }) => (
                    <Button
                        key={buttonLabel}
                        type="button"
                        size="icon"
                        variant={active ? 'secondary' : 'ghost'}
                        disabled={disabled}
                        aria-label={buttonLabel}
                        aria-pressed={active}
                        onClick={action}
                    >
                        <Icon className="size-4" />
                    </Button>
                ))}
            </div>
            <EditorContent editor={editor} />
        </div>
    );
}
