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
export interface Tenant {
    id: number;
    name: string;
    subdomain: string;
    domain: string | null;
    logo_url: string | null;
    primary_color: string | null;
    cancellation_hours: number | null;
    schedule_change_policy: string | null;
    schedule_change_cutoff_days: number | null;
    pricing_rules: any;
    is_active: boolean;
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
