/**
 * Autoffiliate Token-Based API Client
 * Manages Bearer Tokens, API Keys, and unified requests.
 */

export function getAuthToken(): string | null {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem('auth_token');
}

export function setAuthToken(token: string): void {
    if (typeof window === 'undefined') return;
    localStorage.setItem('auth_token', token);
}

export function clearAuthToken(): void {
    if (typeof window === 'undefined') return;
    localStorage.removeItem('auth_token');
}

export async function apiRequest<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const token = getAuthToken();
    const csrf = (typeof document !== 'undefined' ? (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content : '') || '';

    const headers: Record<string, string> = {
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        ...((options.headers as Record<string, string>) || {}),
    };

    // If body is not FormData, default to application/json
    if (options.body && !(options.body instanceof FormData) && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
    }

    const res = await fetch(endpoint, {
        ...options,
        headers,
    });

    if (res.status === 401) {
        // Token is invalid or expired
        if (typeof window !== 'undefined' && window.location.pathname !== '/login') {
            console.warn('Session or Bearer token expired.');
        }
    }

    if (!res.ok) {
        const errorData = await res.json().catch(() => ({}));
        throw new Error(errorData.error || errorData.message || `HTTP ${res.status}`);
    }

    return res.json() as Promise<T>;
}

export const api = {
    // Authentication
    auth: {
        login: async (email: string, password: string, device_name = 'web_client') => {
            const data = await apiRequest<{ success: boolean; token: string; user: any }>('/api/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password, device_name }),
            });
            if (data.token) {
                setAuthToken(data.token);
            }
            return data;
        },
        register: async (name: string, email: string, password: string) => {
            const data = await apiRequest<{ success: boolean; token: string; user: any }>('/api/auth/register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password }),
            });
            if (data.token) {
                setAuthToken(data.token);
            }
            return data;
        },
        me: () => apiRequest<{ success: boolean; user: any }>('/api/auth/me'),
        logout: async () => {
            try {
                await apiRequest('/api/auth/logout', { method: 'POST' });
            } finally {
                clearAuthToken();
            }
        },
        listTokens: () => apiRequest<{ success: boolean; tokens: Array<any> }>('/api/auth/tokens'),
        createToken: (name: string) =>
            apiRequest<{ success: boolean; id: number; name: string; token: string; message: string }>('/api/auth/tokens', {
                method: 'POST',
                body: JSON.stringify({ name }),
            }),
        revokeToken: (id: number | string) =>
            apiRequest<{ success: boolean; message: string }>(`/api/auth/tokens/${id}`, {
                method: 'DELETE',
            }),
    },

    // Health
    health: () => apiRequest<{ status: string; version: string }>('/api/health'),

    // Extraction
    extract: (url: string) =>
        apiRequest<{
            success: boolean;
            product_title: string;
            product_description: string;
            product_price: string;
            shop_name: string;
            affiliate_url: string;
            canonical_url: string;
            media_files: string[];
        }>('/api/extract', {
            method: 'POST',
            body: JSON.stringify({ url }),
        }),

    // Posts & Drafts
    listPosts: (limit = 50, offset = 0) => apiRequest<Array<any>>(`/api/posts?limit=${limit}&offset=${offset}`),
    getPost: (id: string) => apiRequest<any>(`/api/posts/${id}`),
    deletePost: (id: string) => apiRequest<{ success: boolean }>(`/api/posts/${id}`, { method: 'DELETE' }),
    updateCaption: (post_id: string, caption: string, tags?: string, product_price?: string) =>
        apiRequest<{ status: string; post: any }>('/api/posts/caption', {
            method: 'PUT',
            body: JSON.stringify({ post_id, caption, tags, product_price }),
        }),
    generateDraft: (post_id: string, caption_style = 'viral_ai') =>
        apiRequest<{
            post_id: string;
            caption: string;
            caption_style: string;
            tags: string;
            recommended_hashtags: string[];
            ai_usage: any;
        }>('/api/draft/generate', {
            method: 'POST',
            body: JSON.stringify({ post_id, caption_style }),
        }),
    publish: (post_id: string, target_account_ids?: string[]) =>
        apiRequest<any>('/api/publish', {
            method: 'POST',
            body: JSON.stringify({ post_id, target_account_ids }),
        }),

    // Media
    extractPostMedia: (post_id: string, url?: string) =>
        apiRequest<{
            success: boolean;
            post_id: string;
            media_count: number;
            new_media_count: number;
            media_files: string[];
            product_title?: string;
        }>('/api/posts/extract-media', {
            method: 'POST',
            body: JSON.stringify({ post_id, url }),
        }),
    deletePostMedia: (post_id: string, filename: string) =>
        apiRequest<{ success: boolean; media_files: string[]; media_count: number }>('/api/posts/media/delete', {
            method: 'POST',
            body: JSON.stringify({ post_id, filename }),
        }),
    uploadPostMedia: async (post_id: string, file: File) => {
        const formData = new FormData();
        formData.append('post_id', post_id);
        formData.append('file', file);
        return apiRequest<{ success: boolean; post_id: string; media_files: string[] }>('/api/posts/media/upload', {
            method: 'POST',
            body: formData,
        });
    },

    // Settings & Tokens
    getSettings: () => apiRequest<Record<string, string>>('/api/settings'),
    updateSettings: (data: Record<string, string>) =>
        apiRequest<{ status: string }>('/api/settings', {
            method: 'POST',
            body: JSON.stringify(data),
        }),
    verifyToken: () => apiRequest<{ valid: boolean; page_name?: string; page_id?: string; error?: string }>('/api/token/verify'),
    verifyAccountToken: (id: string) =>
        apiRequest<{ valid: boolean; page_name?: string; page_id?: string; error?: string }>('/api/token/verify-account', {
            method: 'POST',
            body: JSON.stringify({ id }),
        }),
    exchangeToken: (data: { fb_page_token: string; fb_app_id?: string; fb_app_secret?: string; fb_page_id?: string }) =>
        apiRequest<any>('/api/token/exchange', {
            method: 'POST',
            body: JSON.stringify(data),
        }),

    // Integrations
    getIntegrations: () => apiRequest<{ integrations: Array<any> }>('/api/integrations'),
    toggleIntegration: (id: string, is_enabled: boolean) =>
        apiRequest<{ status: string; id: string; is_enabled: boolean }>('/api/integrations/toggle', {
            method: 'POST',
            body: JSON.stringify({ id, is_enabled }),
        }),
    addFacebookPage: (data: { page_name: string; page_id: string; page_token: string }) =>
        apiRequest<{ status: string; id: string; name: string }>('/api/integrations/facebook/add', {
            method: 'POST',
            body: JSON.stringify(data),
        }),
    deleteIntegration: (id: string) =>
        apiRequest<{ status: string; id: string }>(`/api/integrations/${id}`, {
            method: 'DELETE',
        }),

    // Analytics & Webhooks
    getAiAnalytics: () => apiRequest<any>('/api/analytics/ai'),
    testWebhook: () => apiRequest<any>('/api/webhooks/test', { method: 'POST' }),
};
