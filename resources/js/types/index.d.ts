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
    createdProject: Project;
};

export interface FlashMessage {
    heading: string;
    description: string;
    variant: 'success' | 'danger';
    duraton: number;
}

export interface Project {
    id: number;
    slug: string;
    name: string;
    description: string;
    start_date: string;
    due_date: string;
    status_id: number | null;
    priority_id: number | null;
    is_private: boolean;
    supervisor_id: number | null;
    assignees: number[];
    viewers: number[];
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
