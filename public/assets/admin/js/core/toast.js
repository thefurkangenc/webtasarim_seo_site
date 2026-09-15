/** Sağ üstte beliren geçici bildirim. */

const VARIANTS = {
    success: { classes: 'bg-success-500 border-success-500', icon: 'check_circle' },
    error: { classes: 'bg-danger-500 border-danger-500', icon: 'error' },
    warning: { classes: 'bg-warning-500 border-warning-500', icon: 'warning' },
    info: { classes: 'bg-primary-500 border-primary-500', icon: 'info' },
};

function container() {
    let element = document.getElementById('admin-toast-container');

    if (! element) {
        element = document.createElement('div');
        element.id = 'admin-toast-container';
        element.className = 'fixed z-[1401] top-[20px] ltr:right-[20px] rtl:left-[20px] flex flex-col gap-[10px]';
        document.body.append(element);
    }

    return element;
}

function show(message, variant = 'info', duration = 4000) {
    const { classes, icon } = VARIANTS[variant] ?? VARIANTS.info;

    const toast = document.createElement('div');
    toast.className = `${classes} text-white rounded-md shadow-3xl py-[12px] px-[17px] border flex items-center gap-[8px] max-w-[360px] transition-all opacity-0 translate-y-[-10px]`;
    toast.setAttribute('role', 'status');

    const iconElement = document.createElement('i');
    iconElement.className = 'material-symbols-outlined !text-[20px] leading-none';
    iconElement.textContent = icon;

    const text = document.createElement('span');
    text.textContent = message;

    toast.append(iconElement, text);
    container().append(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-[-10px]');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-[-10px]');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

export const toast = {
    success: (message) => show(message, 'success'),
    error: (message) => show(message, 'error'),
    warning: (message) => show(message, 'warning'),
    info: (message) => show(message, 'info'),
};
