(function () {
    const money = new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' });

    if (window.jQuery) {
        window.jQuery(($) => {
            $('[data-slideshow]').attr('data-jquery-ready', '1');
            $('form[data-validate]').on('submit.jqueryCheck', function () {
                $(this).find('[required]').each(function () {
                    $(this).toggleClass('is-invalid', !String($(this).val() || '').trim());
                });
            });
        });
    }

    document.querySelectorAll('[data-slideshow]').forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll('.slide'));
        const dotsWrap = slider.querySelector('[data-slider-dots]');
        if (slides.length <= 1 || !dotsWrap) return;
        let index = 0;
        const dots = slides.map((_, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.addEventListener('click', () => show(i));
            dotsWrap.appendChild(btn);
            return btn;
        });
        function show(next) {
            slides[index].classList.remove('active');
            dots[index].classList.remove('active');
            index = next;
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }
        show(0);
        setInterval(() => show((index + 1) % slides.length), 4500);
    });

    document.querySelectorAll('[data-order-form]').forEach((form) => {
        const linesWrap = form.querySelector('[data-order-lines]');
        const template = form.querySelector('[data-order-line-template]');
        const addButton = form.querySelector('[data-add-order-line]');
        const total = form.querySelector('[data-total]');

        function calc() {
            let grandTotal = 0;
            const lines = Array.from(form.querySelectorAll('[data-order-line]'));
            lines.forEach((line) => {
                const select = line.querySelector('[data-product-select]');
                const qty = line.querySelector('[data-quantity]');
                const lineTotal = line.querySelector('[data-line-total]');
                const option = select && select.selectedIndex >= 0 ? select.options[select.selectedIndex] : null;
                const price = Number(option ? option.dataset.price : 0) || 0;
                const quantity = Number(qty ? qty.value : 0) || 0;
                const subtotal = price * quantity;
                grandTotal += subtotal;
                if (lineTotal) lineTotal.textContent = money.format(subtotal);
            });
            if (total) total.textContent = money.format(grandTotal);
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const lines = Array.from(form.querySelectorAll('[data-order-line]'));
            lines.forEach((line) => {
                const remove = line.querySelector('[data-remove-line]');
                if (remove) remove.disabled = lines.length === 1;
            });
        }

        function bindLine(line) {
            line.querySelectorAll('[data-product-select], [data-quantity]').forEach((field) => {
                field.addEventListener('change', calc);
                field.addEventListener('input', calc);
            });
            const remove = line.querySelector('[data-remove-line]');
            if (remove) {
                remove.addEventListener('click', () => {
                    if (form.querySelectorAll('[data-order-line]').length > 1) {
                        line.remove();
                        calc();
                    }
                });
            }
        }

        form.querySelectorAll('[data-order-line]').forEach(bindLine);
        if (addButton && template && linesWrap) {
            addButton.addEventListener('click', () => {
                const line = template.content.firstElementChild.cloneNode(true);
                linesWrap.appendChild(line);
                bindLine(line);
                calc();
            });
        }
        calc();
    });

    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.noValidate = true;
        form.addEventListener('submit', (event) => {
            let ok = true;
            form.querySelectorAll('input, select, textarea').forEach((field) => {
                field.classList.remove('is-invalid');
                if (field.disabled) return;
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
                alert('Проверьте обязательные поля формы.');
            }
        });
    });
})();
