/**
 * fetch sarmalayıcı. Tüm admin AJAX çağrıları buradan geçer.
 *
 * Dönen değer sunucunun JSON gövdesinin tamamıdır:
 *   { success, message, data, meta? }
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export class HttpError extends Error {
    constructor(message, status, payload = null) {
        super(message);
        this.name = 'HttpError';
        this.status = status;
        this.payload = payload;
    }
}

export class ValidationError extends HttpError {
    constructor(message, errors) {
        super(message, 422);
        this.name = 'ValidationError';
        this.errors = errors;
    }
}

/** HTML kaçışı — innerHTML'e basılan her kullanıcı verisi bundan geçmeli. */
export function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

async function parse(response) {
    if (response.status === 204) {
        return null;
    }

    const text = await response.text();

    try {
        return text ? JSON.parse(text) : null;
    } catch {
        // Sunucu HTML döndürdü (genelde işlenmemiş bir hata sayfası).
        return null;
    }
}

async function request(method, url, body = null) {
    const options = {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        },
    };

    if (body instanceof FormData) {
        // Laravel FormData ile PUT/PATCH okuyamaz; method spoofing gerekir.
        if (method === 'PUT' || method === 'PATCH') {
            body.append('_method', method);
            options.method = 'POST';
        }
        options.body = body;
    } else if (body !== null) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const payload = await parse(response);

    if (response.ok) {
        return payload ?? {};
    }

    if (response.status === 422 && payload?.errors) {
        throw new ValidationError(payload.message ?? 'Girilen bilgileri kontrol edin.', payload.errors);
    }

    if (response.status === 419) {
        throw new HttpError('Oturumunuz sona erdi. Sayfayı yenileyin.', 419, payload);
    }

    if (response.status === 401) {
        throw new HttpError('Oturumunuz kapandı. Tekrar giriş yapın.', 401, payload);
    }

    throw new HttpError(
        payload?.message ?? 'Beklenmeyen bir hata oluştu.',
        response.status,
        payload,
    );
}

export const http = {
    get(url, params = {}) {
        const query = new URLSearchParams(
            Object.entries(params).filter(([, value]) => value !== null && value !== undefined && value !== ''),
        ).toString();

        return request('GET', query ? `${url}?${query}` : url);
    },

    /** Blade parçası döndüren uç noktalar için (modal gövdesi gibi). */
    async html(url) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (! response.ok) {
            throw new HttpError(
                response.status === 403 ? 'Bu işlem için yetkiniz yok.' : 'İçerik yüklenemedi.',
                response.status,
            );
        }

        return response.text();
    },

    post: (url, body) => request('POST', url, body),
    put: (url, body) => request('PUT', url, body),
    patch: (url, body) => request('PATCH', url, body),
    delete: (url) => request('DELETE', url),
};
