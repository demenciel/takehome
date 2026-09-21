import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import { SITE_ORIGIN } from './src/lib/catalog.ts';
import { astroRedirects } from './src/lib/redirects.ts';

export default defineConfig({
  site: SITE_ORIGIN,
  output: 'static',
  trailingSlash: 'never',
  redirects: astroRedirects(),
  vite: {
    plugins: [tailwindcss()],
  },
});
