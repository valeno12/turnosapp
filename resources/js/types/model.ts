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
