// =========================================================
// == ARCHIVO GLOBAL DE TIPOS (VERSIÓN UNIFICADA)
// =========================================================

import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

// --- Tipos que YA TENÍAS ---
export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}


// =========================================================
// --- INICIO DE LOS TIPOS QUE FALTABAN PARA 'APPLICANTS' ---
// =========================================================

// --- 1. Tipos para los modelos de la base de datos ---
export interface Institution {
    id: number;
    name: string;
}

export interface Status {
    id: number;
    name: string;
    slug: string;
}

export interface Applicant {
    id: number;
    folio: number;
    full_name: string;
    score: number | null;
    year: number;
    institution: Institution; // Depende de la interfaz Institution
    status: Status;         // Depende de la interfaz Status
}

// --- 2. Tipos para la Paginación de Laravel ---
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: PaginationLink[];
    total: number;
    // ... puedes añadir más campos de paginación aquí si los necesitas
}

// --- 3. Tipo genérico para las Props de Página de Inertia ---
// Este es el tipo que `usePage` necesita para entender la estructura de tus props.
export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: Auth; // Reutiliza la interfaz Auth que ya tenías
    flash: {
        message: string | null;
    };
    // Puedes añadir más props compartidas aquí si es necesario
};

// =========================================================
// --- FIN DE LOS TIPOS AÑADIDOS ---
// =========================================================
