import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    flash: FlashMessage;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: UserRole;
    created_at: string;
    updated_at: string;
}

export type UserRole = 'admin' | 'supervisor' | 'staff';

export type BreadcrumbItemType = BreadcrumbItem;

export interface FlashMessage {
    messageType: 'normal' | 'createdProject' | 'createdTask' | 'createdSubTask';
    heading: string;
    description: string;
    variant: 'success' | 'danger' | 'info' | 'warning';
    duration: number;
    context: FlashMessageContext;
}

export interface FlashMessageContext {
    project?: Project;
    task?: Task;
    subTask?: SubTask;
}

export interface Project {
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number;
    status?: Status;
    priority_id: number;
    priority?: Priority;
    is_private: boolean;
    supervisor_id: number;
    supervisor?: User;
    assignees: User[];
    viewers: User[];
    updated_by: number;
    created_at: string;
    updated_at: string;
}

export interface Task {
    project_id: number;
    parent?: Project;
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number;
    status?: Status;
    priority_id: number;
    priority?: Priority;
    is_private: boolean;
    supervisor_id: number;
    supervisor?: User;
    assignees: User[];
    viewers: User[];
    updated_by: number;
    created_at: string;
    updated_at: string;
}

export interface SubTask {
    task_id: number;
    parent?: Project;
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number;
    status?: Status;
    priority_id: number;
    priority?: Priority;
    is_private: boolean;
    supervisor_id: number;
    supervisor?: User;
    assignees: User[];
    viewers: User[];
    updated_by: number;
    created_at: string;
    updated_at: string;
}

export interface Status {
    id: number;
    name: 'Backlog' | 'In Progress' | 'On Hold' | 'In Review' | 'Done';
    color: 'gray' | 'blue' | 'green' | 'yellow' | 'red';
    created_at: string;
    updated_at: string;
}

export interface Priority {
    id: number;
    name: 'Low' | 'Medium' | 'High' | 'Urgent';
    color: 'gray' | 'blue' | 'green' | 'yellow' | 'red';
    created_at: string;
    updated_at: string;
}

export interface Comment {
    id: string;
    content: string;
    user: User;
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number;
    to?: number;
    next_page_url?: string | null;
    prev_page_url?: string | null;
}

type SortDirection = 'asc' | 'desc';
