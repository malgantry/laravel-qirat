import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: ['selector', '[data-theme="dark"]'],
  theme: {
    extend: {
      colors: {
        text: {
          main: 'var(--text-main)',
          muted: 'var(--text-muted)',
        },
      },
      fontFamily: {
        sans: ['Cairo', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [],
};
