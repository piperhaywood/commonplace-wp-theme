import { readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import * as sass from 'sass';
import postcss from 'postcss';
import autoprefixer from 'autoprefixer';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode, command }) => {
  const env = { ...loadEnv(mode, process.cwd(), ''), ...process.env };
  const origin = env.WP_ORIGIN || 'http://wordpress-unit-test.test';
  const devOrigin = env.VITE_DEV_SERVER || 'http://localhost:5173';
  const url = new URL(devOrigin);

  if (command === 'serve' && url.protocol === 'https:' &&
      (!env.VITE_SSL_KEY || !env.VITE_SSL_CERT)) {
    throw new Error('HTTPS Vite development requires VITE_SSL_KEY and VITE_SSL_CERT.');
  }

  return {
    base: './',
    publicDir: false,
    server: {
      host: url.hostname,
      port: Number(url.port || (url.protocol === 'https:' ? 443 : 80)),
      strictPort: true,
      origin: devOrigin,
      cors: { origin },
      https: command === 'serve' && env.VITE_SSL_KEY && env.VITE_SSL_CERT ? {
        key: readFileSync(env.VITE_SSL_KEY),
        cert: readFileSync(env.VITE_SSL_CERT),
      } : undefined,
    },
    build: {
      outDir: 'commonplace-theme/dist',
      emptyOutDir: true,
      rollupOptions: {
        input: {
          scripts: resolve('js/main.js'),
          style: resolve('css/main.scss'),
          editor: resolve('css/editor.scss'),
        },
        output: {
          entryFileNames: '[name].js',
          assetFileNames: '[name][extname]',
        },
      },
    },
    plugins: [{
      name: 'commonplace-theme-header',
      apply: 'build',
      writeBundle() {
        const pkg = JSON.parse(readFileSync('package.json', 'utf8'));
        const header = [
          '/**',
          ` * Theme Name: ${pkg.title}`,
          ` * Author: ${pkg.author.name}`,
          ` * Author URI: ${pkg.author.url}`,
          ` * Description: ${pkg.description}`,
          ` * Version: ${pkg.version}`,
          ` * License: ${pkg.license}`,
          ` * Textdomain: ${pkg.name}`,
          ' */',
          '',
        ].join('\n');
        writeFileSync('commonplace-theme/style.css', header);
      },
    }, {
      name: 'commonplace-development',
      async configureServer(server) {
        // WordPress reads editor styles from disk and embeds them in its iframe.
        // Keep a local CSS file rather than asking PHP to fetch Vite over HTTPS.
        async function editorStyles() {
          const result = sass.compile('css/editor.scss', { style: 'expanded' });
          const css = await postcss([autoprefixer()]).process(result.css, { from: undefined });
          mkdirSync('commonplace-theme/dist', { recursive: true });
          writeFileSync('commonplace-theme/dist/editor-dev.css', css.css);
        }
        await editorStyles();
        server.watcher.add(resolve('commonplace-theme'));
        server.watcher.add(resolve('css'));
        server.watcher.on('change', async (file) => {
          if (file.endsWith('.scss')) {
            try { await editorStyles(); }
            catch (error) { server.config.logger.error(error.message); }
          }
        });
        server.watcher.on('change', (file) => {
          if (file.endsWith('.php')) server.ws.send({ type: 'full-reload' });
        });
      },
    }],
  };
});
