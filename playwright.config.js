import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './e2e/workflow',
  workers: 1,
  fullyParallel: false,
  timeout: 20_000,
  retries: 0,
  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    headless: false,
  },
});
