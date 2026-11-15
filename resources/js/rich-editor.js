import { Editor } from '@tiptap/core';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';

function normalizeInitialContent(raw) {
    if (!raw || !raw.trim()) {
        return '';
    }
    const looksLikeHtml = /<\/?[a-z][\s\S]*>/i.test(raw);
    if (looksLikeHtml) {
        return raw;
    }
    return raw
        .split(/\n{2,}/)
        .map((block) => `<p>${block.replace(/\n/g, '<br>')}</p>`)
        .join('');
}

function initRichEditor(container) {
    if (container.dataset.editorReady === 'true') {
        return;
    }
    container.dataset.editorReady = 'true';

    const input = container.querySelector('[data-rich-editor-input]');
    const surface = container.querySelector('[data-rich-editor-surface]');
    if (!input || !surface) {
        return;
    }

    const placeholder = container.dataset.placeholder || 'Write your content here...';
    const buttons = Array.from(container.querySelectorAll('[data-editor-action]'));

    const updateToolbarState = (editor) => {
        buttons.forEach((btn) => {
            const action = btn.dataset.editorAction;
            let active = false;
            switch (action) {
                case 'bold':
                    active = editor.isActive('bold');
                    break;
                case 'italic':
                    active = editor.isActive('italic');
                    break;
                case 'strike':
                    active = editor.isActive('strike');
                    break;
                case 'h2':
                    active = editor.isActive('heading', { level: 2 });
                    break;
                case 'h3':
                    active = editor.isActive('heading', { level: 3 });
                    break;
                case 'bulletList':
                    active = editor.isActive('bulletList');
                    break;
                case 'orderedList':
                    active = editor.isActive('orderedList');
                    break;
                case 'blockquote':
                    active = editor.isActive('blockquote');
                    break;
                case 'codeBlock':
                    active = editor.isActive('codeBlock');
                    break;
                default:
                    active = false;
            }
            btn.dataset.active = active ? 'true' : 'false';
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const editor = new Editor({
        element: surface,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
            Placeholder.configure({
                placeholder,
            }),
        ],
        content: normalizeInitialContent(input.value),
        editorProps: {
            attributes: {
                class: 'rich-text-content min-h-[220px] px-3.5 py-3 text-sm leading-relaxed text-app-heading focus:outline-none',
            },
        },
        onCreate: ({ editor: instance }) => {
            updateToolbarState(instance);
        },
        onSelectionUpdate: ({ editor: instance }) => {
            updateToolbarState(instance);
        },
        onUpdate: ({ editor: instance }) => {
            input.value = instance.isEmpty ? '' : instance.getHTML();
            updateToolbarState(instance);
        },
    });

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.editorAction;
            const chain = editor.chain().focus();
            switch (action) {
                case 'bold':
                    chain.toggleBold().run();
                    break;
                case 'italic':
                    chain.toggleItalic().run();
                    break;
                case 'strike':
                    chain.toggleStrike().run();
                    break;
                case 'h2':
                    chain.toggleHeading({ level: 2 }).run();
                    break;
                case 'h3':
                    chain.toggleHeading({ level: 3 }).run();
                    break;
                case 'bulletList':
                    chain.toggleBulletList().run();
                    break;
                case 'orderedList':
                    chain.toggleOrderedList().run();
                    break;
                case 'blockquote':
                    chain.toggleBlockquote().run();
                    break;
                case 'codeBlock':
                    chain.toggleCodeBlock().run();
                    break;
                case 'undo':
                    chain.undo().run();
                    break;
                case 'redo':
                    chain.redo().run();
                    break;
                default:
                    break;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rich-editor]').forEach(initRichEditor);
});
