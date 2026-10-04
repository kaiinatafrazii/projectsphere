/**
 * ProjectSphere - API Client for PHP Backend
 */

// Dynamically determine API root
const getApiBase = () => {
    if (window.location.port === '5173') {
        // Vite Dev server proxies /api to Apache backend
        return '/api';
    }
    // Hosted on Apache / XAMPP (e.g. http://localhost/projectsphere/)
    const path = window.location.pathname;
    if (path.includes('/projectsphere')) {
        return '/projectsphere/api';
    }
    return '/api';
};

export const API_BASE = getApiBase();

export const getAssetUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const clean = path.replace(/^\/+/, '');
    if (window.location.port === '5173') {
        return `/${clean}`;
    }
    const pathname = window.location.pathname;
    if (pathname.includes('/projectsphere')) {
        return `/projectsphere/${clean}`;
    }
    return `/${clean}`;
};

// Generic fetch wrapper with credentials
async function request(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    const token = localStorage.getItem('ps_token');

    const headers = {
        ...(options.headers || {})
    };

    // Auto set content-type for JSON if not FormData
    if (!(options.body instanceof FormData) && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const config = {
        credentials: 'include',
        ...options,
        headers
    };

    const res = await fetch(url, config);
    const data = await res.json().catch(() => ({ success: false, message: 'Invalid server response' }));

    if (!res.ok && !data.message) {
        data.message = `Request failed with status ${res.status}`;
    }

    return data;
}

// 1. Auth API
export const apiLogin = (credentials) => request('/auth.php?action=login', {
    method: 'POST',
    body: JSON.stringify(credentials)
});

export const apiRegister = (userData) => request('/auth.php?action=register', {
    method: 'POST',
    body: JSON.stringify(userData)
});

export const apiGetMe = () => request('/auth.php?action=me');

export const apiLogout = () => request('/auth.php?action=logout', { method: 'POST' });

// 2. Stats API
export const apiGetStats = (type = 'public') => request(`/stats.php?type=${type}`);

// 3. Categories API
export const apiGetCategories = () => request('/categories.php');
export const apiCreateCategory = (data) => request('/categories.php', { method: 'POST', body: JSON.stringify(data) });
export const apiUpdateCategory = (id, data) => request(`/categories.php?id=${id}`, { method: 'PUT', body: JSON.stringify(data) });
export const apiDeleteCategory = (id) => request(`/categories.php?id=${id}`, { method: 'DELETE' });

// 4. Projects API
export const apiGetProjects = (params = {}) => {
    const query = new URLSearchParams(params).toString();
    return request(`/projects.php${query ? `?${query}` : ''}`);
};

export const apiGetProjectDetails = (id) => request(`/projects.php?id=${id}`);
export const apiGetMyProjects = () => request('/projects.php?my=1');
export const apiGetAllAdminProjects = (params = {}) => {
    const query = new URLSearchParams({ all: 1, ...params }).toString();
    return request(`/projects.php?${query}`);
};

export const apiSubmitProject = (formData) => request('/projects.php', {
    method: 'POST',
    body: formData
});

export const apiEditProject = (id, formData) => request(`/projects.php?action=edit&id=${id}`, {
    method: 'POST',
    body: formData
});

export const apiDeleteProject = (id) => request(`/projects.php?id=${id}`, {
    method: 'DELETE'
});

export const apiUpdateProjectStatus = (projectId, status, rejectionReason = '') => request('/projects.php?action=status', {
    method: 'POST',
    body: JSON.stringify({ project_id: projectId, status, rejection_reason: rejectionReason })
});

// 5. Evaluations & Rankings API
export const apiSubmitEvaluation = (evalData) => request('/evaluations.php', {
    method: 'POST',
    body: JSON.stringify(evalData)
});

export const apiGetRankings = (category = 0) => request(`/rankings.php${category ? `?category=${category}` : ''}`);
export const apiRecalculateRankings = () => request('/rankings.php?action=recalculate', { method: 'POST' });

// 6. Students API
export const apiGetStudents = (search = '') => request(`/students.php${search ? `?search=${encodeURIComponent(search)}` : ''}`);
export const apiUpdateStudentProfile = (data) => request('/students.php', {
    method: 'PUT',
    body: JSON.stringify({ action: 'profile', ...data })
});
export const apiUpdatePassword = (data) => request('/students.php', {
    method: 'PUT',
    body: JSON.stringify({ action: 'password', ...data })
});

// 7. Feedback API
export const apiGetFeedback = (search = '') => request(`/feedback.php${search ? `?search=${encodeURIComponent(search)}` : ''}`);
export const apiPostFeedback = (data) => request('/feedback.php', {
    method: 'POST',
    body: JSON.stringify(data)
});
