type Theme = 'light' | 'dark';

export function getSystemTheme(): Theme {
    const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
    const systemTheme = mediaQueryList.matches ? 'dark' : 'light';
    return systemTheme;
}
