(() => {
    const dialog = document.getElementById('order_items');
    if (!dialog) return;

    document.querySelectorAll('[data-open-order-items]').forEach(button => {
        button.addEventListener('click', () => {
            if (!dialog.open) dialog.showModal();
        });
    });

    dialog.querySelectorAll('[data-close-order-items]').forEach(button => {
        button.addEventListener('click', () => dialog.close());
    });

    // Safari and other browsers without native backdrop dismissal.
    if (!('closedBy' in HTMLDialogElement.prototype)) {
        dialog.addEventListener('click', event => {
            if (event.target !== dialog) return;

            const bounds = dialog.getBoundingClientRect();
            const inside = event.clientX >= bounds.left && event.clientX <= bounds.right
                && event.clientY >= bounds.top && event.clientY <= bounds.bottom;

            if (!inside) dialog.close();
        });
    }
})();
