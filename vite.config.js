import ViteRestart from 'vite-plugin-restart';
import legacy from '@vitejs/plugin-legacy'

export default ({ command }) => ({
  base: command === 'serve' ? '' : '/dist/',
  build: {
    manifest: true,
    outDir: './www/dist',
    rollupOptions: {
      input: {
        app: './src/js/app.js'
      }
    }
  },
  plugins: [
    legacy({
      targets: ['defaults', 'not IE 11']
    }),
    ViteRestart({
      reload: [
          './templates/**/*',
          './src/www/**/*'
      ],
    })
  ],
  publicDir: './src/www',
  assetsDir: 'bundle',
  server: {
    host: '0.0.0.0'
  }
});
