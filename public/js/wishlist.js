(() => {
    'use strict';

    const drawer = document.getElementById('header-wishlist');
    if (!drawer) return;

    const storageKey = 'graphic-shop.wishlist.v1';
    const maxItems = 200;
    const list = drawer.querySelector('[data-wishlist-items]');
    const empty = drawer.querySelector('[data-wishlist-empty]');
    const status = document.querySelector('[data-wishlist-status]');
    const number = new Intl.NumberFormat('fa-IR');
    let statusTimer;

    const localUrl = value => {
        if (typeof value !== 'string') return null;
        try {
            const url = new URL(value, location.origin);
            return url.origin === location.origin && /^https?:$/.test(url.protocol) ? url.href : null;
        } catch {
            return null;
        }
    };

    const normalize = entries => {
        if (!Array.isArray(entries)) return [];
        const unique = new Map();
        for (const entry of entries.slice(0, maxItems)) {
            if (!entry || typeof entry !== 'object') continue;
            const id = String(entry.id);
            const url = localUrl(entry.url);
            const image = localUrl(entry.image);
            if (!/^[1-9]\d{0,19}$/.test(id) || !url || !image || typeof entry.title !== 'string'
                || !entry.title.trim() || !Number.isSafeInteger(entry.price) || entry.price < 0) continue;
            unique.set(id, { id, title: entry.title.slice(0, 255), price: entry.price, url, image });
        }
        return [...unique.values()];
    };

    const read = () => {
        try {
            return normalize(JSON.parse(localStorage.getItem(storageKey) || '[]'));
        } catch {
            return [];
        }
    };

    let items = read();

    const announce = message => {
        clearTimeout(statusTimer);
        status.textContent = message;
        status.classList.toggle('is-visible', !drawer.open);
        drawer.querySelector('[data-wishlist-feedback]').textContent = drawer.open ? message : '';
        statusTimer = setTimeout(() => status.classList.remove('is-visible'), 5000);
    };

    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };

    const render = () => {
        document.querySelectorAll('[data-open-wishlist]').forEach(button => {
            button.dataset.notify = number.format(items.length);
            button.setAttribute('aria-label', `علاقه‌مندی‌ها، ${number.format(items.length)} محصول`);
            button.querySelector('i').classList.toggle('zmdi-favorite', items.length > 0);
            button.querySelector('i').classList.toggle('zmdi-favorite-outline', items.length === 0);
        });
        drawer.querySelector('[data-wishlist-count]').textContent = number.format(items.length);
        document.querySelectorAll('[data-wishlist-toggle]').forEach(button => {
            const saved = items.some(item => item.id === button.dataset.productId);
            const action = saved ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها';
            button.setAttribute('aria-pressed', String(saved));
            button.setAttribute('aria-label', `${action}: ${button.dataset.productTitle}`);
            button.title = action;
            button.querySelector('i').classList.toggle('zmdi-favorite', saved);
            button.querySelector('i').classList.toggle('zmdi-favorite-outline', !saved);
            const label = button.querySelector('[data-wishlist-label]');
            if (label) label.textContent = action;
        });
        empty.hidden = items.length > 0;
        drawer.querySelector('[data-wishlist-content]').classList.toggle('shop-drawer-content-empty', items.length === 0);
        list.replaceChildren();
        for (const item of items) {
            const row = element('li', 'wishlist-item');
            const imageLink = element('a', 'wishlist-item-image');
            imageLink.href = item.url;
            imageLink.tabIndex = -1;
            imageLink.setAttribute('aria-hidden', 'true');
            const image = document.createElement('img');
            image.src = item.image;
            image.alt = '';
            image.width = 72;
            image.height = 88;
            image.loading = 'lazy';
            imageLink.append(image);
            const details = element('div', 'wishlist-item-details');
            const title = element('a', 'wishlist-item-title', item.title);
            title.href = item.url;
            details.append(title, element('span', 'wishlist-item-price', `${number.format(item.price)} تومان`));
            const remove = element('button', 'shop-drawer-icon-button');
            remove.type = 'button';
            remove.dataset.wishlistRemove = item.id;
            remove.setAttribute('aria-label', `حذف ${item.title} از علاقه‌مندی‌ها`);
            const icon = element('i', 'zmdi zmdi-delete');
            icon.setAttribute('aria-hidden', 'true');
            remove.append(icon);
            row.append(imageLink, details, remove);
            list.append(row);
        }
    };

    const save = message => {
        let persisted = true;
        try {
            localStorage.setItem(storageKey, JSON.stringify(items));
        } catch {
            persisted = false;
        }
        render();
        announce(persisted ? message : 'تغییر فقط تا زمان بستن این صفحه نگه داشته می‌شود؛ ذخیره‌سازی مرورگر در دسترس نیست.');
    };

    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-wishlist-toggle]');
        if (toggle) {
            const index = items.findIndex(item => item.id === toggle.dataset.productId);
            if (index >= 0) {
                items.splice(index, 1);
                save('محصول از علاقه‌مندی‌ها حذف شد.');
                return;
            }
            if (items.length >= maxItems) {
                announce('فهرست علاقه‌مندی‌ها پر است؛ ابتدا یکی از محصولات را حذف کنید.');
                return;
            }
            const entry = normalize([{
                id: toggle.dataset.productId, title: toggle.dataset.productTitle,
                price: Number(toggle.dataset.productPrice), url: toggle.dataset.productUrl,
                image: toggle.dataset.productImage,
            }])[0];
            if (!entry) {
                announce('اطلاعات محصول کامل نیست؛ صفحه را دوباره بارگذاری کنید.');
                return;
            }
            items.push(entry);
            save('محصول به علاقه‌مندی‌ها اضافه شد.');
        }
    });

    list.addEventListener('click', event => {
        const remove = event.target.closest('[data-wishlist-remove]');
        if (!remove) return;
        const index = items.findIndex(item => item.id === remove.dataset.wishlistRemove);
        items = items.filter(item => item.id !== remove.dataset.wishlistRemove);
        save('محصول از علاقه‌مندی‌ها حذف شد.');
        const remaining = list.querySelectorAll('[data-wishlist-remove]');
        (remaining[Math.min(index, remaining.length - 1)] || drawer.querySelector('[data-close-wishlist]')).focus();
    });

    document.querySelectorAll('[data-open-wishlist]').forEach(button => {
        button.addEventListener('click', () => {
            const cart = document.getElementById('header-basket');
            if (cart?.open) cart.close();
            drawer.querySelector('[data-wishlist-feedback]').textContent = '';
            if (!drawer.open) drawer.showModal();
            document.body.classList.add('shop-drawer-open');
            document.querySelectorAll('[data-open-wishlist]').forEach(trigger => trigger.setAttribute('aria-expanded', 'true'));
        });
    });
    drawer.querySelectorAll('[data-close-wishlist]').forEach(button => button.addEventListener('click', () => drawer.close()));
    drawer.addEventListener('close', () => {
        if (!document.getElementById('header-basket')?.open) document.body.classList.remove('shop-drawer-open');
        document.querySelectorAll('[data-open-wishlist]').forEach(trigger => trigger.setAttribute('aria-expanded', 'false'));
    });
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        drawer.addEventListener('click', event => {
            if (event.target !== drawer) return;
            const bounds = drawer.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) drawer.close();
        });
    }
    window.addEventListener('storage', event => {
        if (event.key === storageKey || event.key === null) {
            items = read();
            render();
        }
    });
    render();
})();
