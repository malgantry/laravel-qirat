import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/sass/app.scss',
        'resources/js/app.js',
      ],
      refresh: [
        'resources/views/**',
        'resources/js/**',
        'resources/sass/**',
      ],
    }),
  ],
  server: {
    hmr: { host: 'localhost' },
  },
});
