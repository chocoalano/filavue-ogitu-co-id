import { expect, Page, Response } from '@playwright/test'

export const TEST_CUSTOMER = {
    username: 'ogitu1',
    password: 'ogitu@2026',
    email: 'zenithsinergiutama@gmail.com',
}

export const TEST_PRODUCT_SLUG = 'biozenion-pendant-green'
export const CUSTOMER_STORAGE_STATE = 'tests/playwright/.auth/customer.json'

async function retryDelayFromResponse(response: Response | null): Promise<number> {
    if (!response) {
        return 3
    }

    const headers = await response.allHeaders()

    return Number(headers['retry-after'] ?? 3)
}

export async function gotoAuthPage(page: Page, path: string, attempts = 3): Promise<Response | null> {
    let response: Response | null = null

    for (let attempt = 1; attempt <= attempts; attempt++) {
        response = await page.goto(path, { waitUntil: 'domcontentloaded' })

        if (response?.status() !== 429) {
            return response
        }

        await page.waitForTimeout((await retryDelayFromResponse(response) + 1) * 1000)
    }

    return response
}

export async function createCustomerSession(page: Page, username: string, password: string): Promise<void> {
    await page.goto('/', { waitUntil: 'domcontentloaded' })

    const csrfToken = await page.locator('meta[name="csrf-token"]').getAttribute('content')

    expect(csrfToken).toBeTruthy()

    const response = await page.request.post('/login', {
        form: {
            _token: csrfToken ?? '',
            username,
            password,
        },
        maxRedirects: 0,
    })

    expect([302, 303]).toContain(response.status())
}

/**
 * Login via the storefront login page using username + password.
 * Targets "Masuk ke Akun" button to avoid ambiguity with newsletter subscribe.
 */
export async function loginAs(
    page: Page,
    username: string,
    password: string,
    options: { assumeOnLoginPage?: boolean } = {},
): Promise<void> {
    if (!options.assumeOnLoginPage) {
        const response = await gotoAuthPage(page, '/login')

        expect(response?.status()).toBe(200)
    }

    const usernameInput = page.locator('input[autocomplete="username"], input[placeholder*="username" i]').first()

    await expect(usernameInput).toBeVisible({ timeout: 10_000 })

    await usernameInput.fill(username)
    await page.locator('#password').fill(password)
    await page.getByRole('button', { name: /Masuk ke Akun/i }).click()

    await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20_000 })
}
