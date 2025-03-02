export interface User {
    id: number;
    name: string;
    email: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash: FlashMessage;
};

export interface FlashMessage {
    messageType: 'normal' | 'createdProject';
    heading: string;
    description: string;
    variant: 'success' | 'danger' | 'info' | 'warning';
    duration: number;
    context: FlashMessageContext;
}

export interface FlashMessageContext {
    project: Project;
}

export interface Project {
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number;
    priority_id: number;
    is_private: boolean;
    supervisor_id: number;
    assignees: User[];
    viewers: User[];
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
