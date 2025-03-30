// Returns Tailwind color classes for badges based on color name
// Supports red, yellow, blue, purple, green, and gray (default)
// Includes light/dark mode variants and hover states
export const getBadgeColors = (colorName: string | undefined) => {
    if (!colorName) return 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
    switch (colorName.toLowerCase()) {
        case 'red':
            return 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800';
        case 'yellow':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-300 dark:hover:bg-yellow-800';
        case 'blue':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800';
        case 'purple':
            return 'bg-purple-100 text-purple-800 hover:bg-purple-200 dark:bg-purple-900 dark:text-purple-300 dark:hover:bg-purple-800';
        case 'green':
            return 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-300 dark:hover:bg-green-800';
        case 'gray':
        default:
            return 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700';
    }
};
