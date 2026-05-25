(function () {
    if (window.jQuery) {
        window.jQuery(($) => {
            $('.admin-nav__link').on('mouseenter', function () {
                $(this).attr('data-hovered', '1');
            });
            $('form[data-validate]').on('submit.jqueryCheck', function () {
                $(this).find('[required]').each(function () {
                    $(this).toggleClass('is-invalid', !String($(this).val() || '').trim());
                });
            });
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm || 'Подтвердите действие')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('textarea.wysiwyg').forEach((textarea) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'wysiwyg-wrap';
        const toolbar = document.createElement('div');
        toolbar.className = 'wysiwyg-toolbar';
        const editor = document.createElement('div');
        editor.className = 'wysiwyg-editor';
        editor.contentEditable = 'true';
        editor.innerHTML = textarea.value || '';
        const buttons = [
            ['bold', 'Ж'], ['italic', 'К'], ['underline', 'Ч'],
            ['insertUnorderedList', '• список'], ['insertOrderedList', '1. список'],
            ['formatBlock', 'H2', 'h2'], ['formatBlock', 'P', 'p'], ['createLink', 'Ссылка']
        ];
        buttons.forEach(([cmd, label, value]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.addEventListener('click', () => {
                if (cmd === 'createLink') {
                    const href = prompt('Введите URL');
                    if (href) document.execCommand(cmd, false, href);
                } else {
                    document.execCommand(cmd, false, value || null);
                }
                editor.focus();
            });
            toolbar.appendChild(button);
        });
        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(wrapper, textarea.nextSibling);
        wrapper.appendChild(toolbar);
        wrapper.appendChild(editor);
        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', () => { textarea.value = editor.innerHTML; });
        }
    });

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            let ok = true;
            form.querySelectorAll('input, select, textarea').forEach((field) => {
                field.classList.remove('is-invalid');
                if (field.hasAttribute('required') && !String(field.value || '').trim()) {
                    ok = false;
                    field.classList.add('is-invalid');
                }
                if (field.type === 'email' && field.value && !/^\S+@\S+\.\S+$/.test(field.value)) {
                    ok = false;
                    field.classList.add('is-invalid');
                }
            });
            if (!ok) {
                event.preventDefault();
                alert('Проверьте обязательные поля.');
            }
        });
    });
})();
