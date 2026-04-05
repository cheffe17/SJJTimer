import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Dynamic event type colors (sky=visit, violet=virtual_date, rose=live_date, amber=anniversary)
        ...['sky', 'violet', 'rose', 'amber'].flatMap(c => [
            `bg-${c}-50`, `bg-${c}-100`, `bg-${c}-400`, `bg-${c}-500`, `bg-${c}-600`,
            `text-${c}-500`, `text-${c}-600`, `text-${c}-700`,
            `border-${c}-200`, `border-${c}-300`, `border-${c}-400`,
            `hover:bg-${c}-50`, `hover:text-${c}-600`,
        ]),
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
