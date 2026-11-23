
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  base: process.env.GITHUB_ACTIONS
    ? '/nature-holidays-production/'
    : '/',

  plugins: [react()],

  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/nature-holidays-php/public',
        changeOrigin: true,
      },
    },
  },
})
