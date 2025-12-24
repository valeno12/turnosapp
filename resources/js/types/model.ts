export interface Resource {
    id: number;
    tenant_id: number;
    name: string;
    capacity: number;
    is_self_assignable: boolean;
    notes: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Service {
    id: number;
    tenant_id: number;
    name: string;
    duration_minutes: number;
    color: string;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface SubscriptionPlan {
    id: number;
    tenant_id: number;
    name: string;
    classes_per_week: number;
    base_price: string; // ← String porque Decimal viene como string desde Laravel
    allows_makeups: boolean;
    description: string | null;
    is_active: boolean;
    services?: Service[]; // ← Opcional porque solo viene con ->with('services')
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    tenant_id: number;
    name: string;
    email: string | null;
    role: 'admin' | 'instructor';
    permissions?: Permission[];
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    description: string;
    group: string;
}

export interface Schedule {
    id: number;
    tenant_id: number;
    service_id: number;
    resource_id: number;
    user_id: number | null;
    day_of_week: number;
    start_time: string;
    capacity: number;
    is_active: boolean;
    service?: Service;
    resource?: Resource;
    instructor?: User;
    created_at: string;
    updated_at: string;
}

export interface Student {
    id: number;
    tenant_id: number;
    name: string;
    email: string | null;
    phone: string | null;
    birth_date: string | null;
    health_notes: string | null;
    emergency_contact: string | null;
    emergency_phone: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Booking {
    id: number;
    tenant_id: number;
    student_id: number;
    schedule_id: number;
    resource_id: number | null;
    date: string; // "2024-12-16"
    status: 'confirmed' | 'cancelled' | 'attended' | 'no_show';
    notes: string | null;
    student?: Student;
    schedule?: Schedule;
    resource?: Resource;
    created_at: string;
    updated_at: string;
}

export interface ScheduleException {
    id: number;
    tenant_id: number;
    schedule_id: number;
    date: string; // "2024-12-25"
    reason: string | null;
    created_at: string;
    updated_at: string;
}
