// Centralized TypeScript type definitions for API requests and responses

// ==========================================
// Common Types
// ==========================================

export interface ApiResponse<T> {
  data: T
  message?: string
  status: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  status: number
}

// ==========================================
// Student Types
// ==========================================

export interface Student {
  id: string
  name: string
  nisn: string
  birth_date?: string
  enrollment_date?: string
  status: 'active' | 'inactive' | 'graduated' | 'dropped_out'
  class_id?: string
  class?: string
  parent_id?: string
  user_id?: string
  email?: string
  avatar?: string
  enrollmentYear?: string
  created_at?: string
  updated_at?: string
}

export interface CreateStudentRequest {
  name: string
  nisn: string
  birth_date?: string
  enrollment_date?: string
  status?: string
  class_id?: string
  parent_id?: string
  user_id?: string
}

export interface UpdateStudentRequest {
  name?: string
  nisn?: string
  birth_date?: string
  enrollment_date?: string
  status?: string
  class_id?: string
  parent_id?: string
  user_id?: string
}

// ==========================================
// Teacher Types
// ==========================================

export interface Teacher {
  id: string
  name: string
  nip: string
  expertise?: string
  join_date: string
  status: 'active' | 'inactive' | 'retired'
  user_id?: string
  created_at?: string
  updated_at?: string
}

export interface CreateTeacherRequest {
  name: string
  nip: string
  expertise?: string
  join_date: string
  status?: string
  user_id?: string
}

export interface UpdateTeacherRequest {
  name?: string
  nip?: string
  expertise?: string
  join_date?: string
  status?: string
  user_id?: string
}

// ==========================================
// Attendance Types
// ==========================================

export interface AttendanceRecord {
  id: string
  date: string
  status: 'present' | 'absent' | 'late' | 'excused'
  notes?: string
  student_id?: string
  staff_id?: string
  created_at?: string
  updated_at?: string
}

export interface MarkAttendanceRequest {
  date: string
  status: 'present' | 'absent' | 'late' | 'excused'
  notes?: string
  student_id?: string
  staff_id?: string
}

export interface AttendanceQueryParams {
  date?: string
  start_date?: string
  end_date?: string
  status?: string
  student_id?: string
  staff_id?: string
  page?: number
  per_page?: number
}

// ==========================================
// Class Types
// ==========================================

export interface Class {
  id: string
  name: string
  level: string
  homeroom_teacher_id?: string
  academic_year?: string
  created_at?: string
  updated_at?: string
}

// ==========================================
// User Types
// ==========================================

export interface User {
  id: string
  name: string
  email: string
  is_active: boolean
  role?: string
  created_at?: string
  updated_at?: string
}

// ==========================================
// Auth Types
// ==========================================

export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  user: User
  token: {
    access_token: string
    token_type: 'bearer'
    expires_in: number
  }
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
}

// ==========================================
// WebSocket Types
// ==========================================

export interface WebSocketMessage {
  type: string
  data: unknown
  timestamp?: string
}

export type EventCallback = (...args: unknown[]) => void

// ==========================================
// API Service Types
// ==========================================

export interface ApiServiceConfig {
  baseURL: string
  timeout?: number
  headers?: Record<string, string>
}

export interface QueryParams {
  [key: string]: string | number | boolean | undefined
}
