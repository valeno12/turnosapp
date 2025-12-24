/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            keyframes: {
                'collapsible-down': {
                    from: { height: '0', opacity: '0' },
                    to: {
                        height: 'var(--radix-collapsible-content-height)',
                        opacity: '1',
                    },
                },
                'collapsible-up': {
                    from: {
                        height: 'var(--radix-collapsible-content-height)',
                        opacity: '1',
                    },
                    to: { height: '0', opacity: '0' },
                },
            },
            animation: {
                'collapsible-down': 'collapsible-down 0.2s ease-out',
                'collapsible-up': 'collapsible-up 0.2s ease-out',
            },
        },
    },
    plugins: [],
};
