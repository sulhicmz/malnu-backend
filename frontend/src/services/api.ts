import axios from 'axios';

// Base API configuration
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:9501/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add JWT token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid, redirect to login
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;

// API endpoints
export const studentApi = {
  getAll: (params?: { class_id?: string; status?: string; search?: string; page?: number; limit?: number }) => 
    api.get('/school/students', { params }),
  getById: (id: string) => api.get(`/school/students/${id}`),
  create: (data: any) => api.post('/school/students', data),
  update: (id: string, data: any) => api.put(`/school/students/${id}`, data),
  delete: (id: string) => api.delete(`/school/students/${id}`),
};

export const teacherApi = {
  getAll: (params?: { subject_id?: string; class_id?: string; status?: string; search?: string; page?: number; limit?: number }) => 
    api.get('/school/teachers', { params }),
  getById: (id: string) => api.get(`/school/teachers/${id}`),
  create: (data: any) => api.post('/school/teachers', data),
  update: (id: string, data: any) => api.put(`/school/teachers/${id}`, data),
  delete: (id: string) => api.delete(`/school/teachers/${id}`),
};

// Add other API endpoints as needed
export const attendanceApi = {
  getAll: (params?: any) => api.get('/attendance/staff-attendances', { params }),
  markAttendance: (data: any) => api.post('/attendance/staff-attendances/mark-attendance', data),
};