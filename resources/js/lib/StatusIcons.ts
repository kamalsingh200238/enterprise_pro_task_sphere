import {
    CheckSquare,
    GitPullRequest,
    ListTodo,
    Pause,
    Timer,
} from 'lucide-vue-next';

export const statusIcons = {
    Backlog: ListTodo,
    'In Progress': Timer,
    'On Hold': Pause,
    'In Review': GitPullRequest,
    Done: CheckSquare,
};
