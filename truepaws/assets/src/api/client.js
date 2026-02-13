import axios from 'axios';

// Get WordPress data from localized script
const wpData = window.truepawsData || {};

const api = axios.create({
  baseURL: wpData.apiUrl,
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpData.nonce,
  },
});

// Request interceptor to handle loading states
api.interceptors.request.use(
  (config) => {
    // You can add loading state management here if needed
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      console.error('Authentication error - please refresh the page');
    }
    return Promise.reject(error);
  }
);

// API endpoints
export const animalsAPI = {
  getAll: (params = {}) => api.get('/animals', { params }),
  getById: (id) => api.get(`/animals/${id}`),
  create: (data) => api.post('/animals', data),
  update: (id, data) => api.put(`/animals/${id}`, data),
  delete: (id) => api.delete(`/animals/${id}`),
  getTimeline: (id) => api.get(`/animals/${id}/timeline`),
  getPedigree: (id, generations = 3) => api.get(`/animals/${id}/pedigree`, { params: { generations } }),
  getAICareAdvice: (id) => api.get(`/animals/${id}/ai-care-advice`),
};

export const littersAPI = {
  getAll: () => api.get('/litters'),
  getById: (id) => api.get(`/litters/${id}`),
  create: (data) => api.post('/litters', data),
  update: (id, data) => api.put(`/litters/${id}`, data),
  delete: (id) => api.delete(`/litters/${id}`),
  whelp: (id, data) => api.post(`/litters/${id}/whelp`, data),
};

export const contactsAPI = {
  getAll: (params = {}) => api.get('/contacts', { params }),
  getById: (id) => api.get(`/contacts/${id}`),
  getPurchases: (id) => api.get(`/contacts/${id}/purchases`),
  create: (data) => api.post('/contacts', data),
  update: (id, data) => api.put(`/contacts/${id}`, data),
  delete: (id) => api.delete(`/contacts/${id}`),
};

export const eventsAPI = {
  create: (animalId, data) => api.post(`/animals/${animalId}/events`, data),
  update: (id, data) => api.put(`/events/${id}`, data),
  delete: (id) => api.delete(`/events/${id}`),
};

export const dashboardAPI = {
  getStats: () => api.get('/dashboard/stats'),
  getLatestEvents: () => api.get('/dashboard/latest-events'),
  getSalesReport: () => api.get('/dashboard/sales'),
};

export const settingsAPI = {
  getAll: () => api.get('/settings'),
  update: (data) => api.put('/settings', data),
  getBreeds: () => api.get('/settings/breeds'),
  addBreed: (breed) => api.post('/settings/breeds', { name: breed }),
  deleteBreed: (id) => api.delete(`/settings/breeds/${id}`),
};

export default api;