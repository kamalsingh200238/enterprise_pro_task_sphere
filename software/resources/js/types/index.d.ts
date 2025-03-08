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
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface FlashMessage {
    messageType: 'normal' | 'createdProject' | 'createdTask';
    heading: string;
    description: string;
    variant: 'success' | 'danger' | 'info' | 'warning';
    duration: number;
    context: FlashMessageContext;
}

export interface FlashMessageContext {
    project?: Project;
    task?: Task;
}

export interface Project {
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status: Status;
    priority: Priority;
    is_private: boolean;
    supervisor: User;
    assignees: User[];
    viewers: User[];
    updated_by: User;
    created_at: string;
    updated_at: string;
}

export interface Task {
    project: Project;
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status: Status;
    priority: Priority;
    is_private: boolean;
    supervisor: User;
    assignees: User[];
    viewers: User[];
    updated_by: User;
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
