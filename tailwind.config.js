import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,jsx,ts,tsx}',
    ],

    theme: {
        extend: {
            colors: {
                cream: 'rgb(var(--color-bg) / <alpha-value>)',
                paper: 'rgb(var(--color-paper) / <alpha-value>)',
                'cream-dark': 'rgb(var(--color-surface) / <alpha-value>)',
                'cream-deeper': 'rgb(var(--color-border) / <alpha-value>)',
                ink: 'rgb(var(--color-text) / <alpha-value>)',
                'ink-light': 'rgb(var(--color-text-soft) / <alpha-value>)',
                'ink-muted': 'rgb(var(--color-muted) / <alpha-value>)',
                gold: 'rgb(var(--color-accent) / <alpha-value>)',
                'gold-dark': 'rgb(var(--color-accent-strong) / <alpha-value>)',
                'gold-light': 'rgb(var(--color-accent-soft) / <alpha-value>)',
                warm: 'rgb(var(--color-warm) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: 'var(--font-display)',
                heading: 'var(--font-heading)',
                body: 'var(--font-body)',
            },
            borderRadius: {
                ds: 'var(--radius-base)',
                'ds-card': 'var(--radius-card)',
                'ds-frame': 'var(--radius-frame)',
                'ds-badge': 'var(--radius-badge)',
            },
            boxShadow: {
                soft: 'var(--shadow-soft)',
                float: 'var(--shadow-float)',
            },
        },
    },

    plugins: [forms],
};
