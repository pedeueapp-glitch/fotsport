/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    orange: '#F37021',
                    'orange-hover': '#D65D18',
                    blue: '#0055A5',
                    'blue-hover': '#004484',
                    dark: '#1A1A1A',
                }
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
