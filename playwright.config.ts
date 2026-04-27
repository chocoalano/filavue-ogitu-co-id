import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
    testDir: './tests/playwright',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'tests/playwright/reports' }]],
    use: {
        baseURL: process.env.APP_URL ?? 'http://localhost:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'on-first-retry',
        actionTimeout: 10_000,
        navigationTimeout: 20_000,
    },
    projects: [
        {
            name: 'setup',
            testMatch: /.*\.setup\.ts/,
        },
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
            dependencies: ['setup'],
            testIgnore: /.*\.setup\.ts/,
        },
    ],
    // Output folder for screenshots/videos/traces
    outputDir: 'tests/playwright/results',
})
