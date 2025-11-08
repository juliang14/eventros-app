import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

export default {
  prefix: 'tw-', // si lo estás usando para evitar conflictos
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [forms],
}
