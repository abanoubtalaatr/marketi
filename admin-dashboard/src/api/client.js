import axios from 'axios';

const api = axios.create({
  baseURL: '/api/v1',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

api.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  login: (identifier, password) => api.post('/login', { identifier, password }),
};

export const adminApi = {
  dashboard: () => api.get('/admin/dashboard'),
  users: (params) => api.get('/admin/users', { params }),
  createUser: (data) => api.post('/admin/users', data),
  updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
  deleteUser: (id) => api.delete(`/admin/users/${id}`),
  categories: () => api.get('/admin/categories'),
  createCategory: (data) => api.post('/admin/categories', data),
  updateCategory: (id, data) => api.put(`/admin/categories/${id}`, data),
  deleteCategory: (id) => api.delete(`/admin/categories/${id}`),
  brands: () => api.get('/admin/brands'),
  createBrand: (data) => api.post('/admin/brands', data),
  updateBrand: (id, data) => api.put(`/admin/brands/${id}`, data),
  deleteBrand: (id) => api.delete(`/admin/brands/${id}`),
  products: (params) => api.get('/admin/products', { params }),
  createProduct: (data) => api.post('/admin/products', data),
  updateProduct: (id, data) => api.put(`/admin/products/${id}`, data),
  deleteProduct: (id) => api.delete(`/admin/products/${id}`),
  orders: (params) => api.get('/admin/orders', { params }),
  updateOrderStatus: (id, status) => api.put(`/admin/orders/${id}/status`, { status }),
  refundOrder: (id) => api.post(`/admin/orders/${id}/refund`),
  payments: (params) => api.get('/admin/payments', { params }),
  revenue: () => api.get('/admin/payments/revenue'),
  notifications: () => api.get('/admin/notifications'),
  sendNotification: (data) => api.post('/admin/notifications', data),
  supportTickets: (params) => api.get('/admin/support/tickets', { params }),
  replyTicket: (id, admin_reply) => api.post(`/admin/support/tickets/${id}/reply`, { admin_reply }),
  closeTicket: (id) => api.post(`/admin/support/tickets/${id}/close`),
};

export default api;
