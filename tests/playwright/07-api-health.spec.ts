import { test, expect, request as playwrightRequest, type APIResponse } from '@playwright/test'
import { TEST_CUSTOMER } from './helpers/auth'

async function withAuthAttemptsRetry(action: () => Promise<APIResponse>, attempts = 2): Promise<APIResponse> {
    let response = await action()

    for (let attempt = 1; (attempt < attempts) && (response.status() === 429); attempt++) {
        const retryAfter = Number(response.headers()['retry-after'] ?? 60)

        await new Promise((resolve) => setTimeout(resolve, (retryAfter + 1) * 1000))

        response = await action()
    }

    return response
}

test.describe('Pemeriksaan Kesehatan API', () => {
    test('GET /up mengembalikan 200', async ({ request }) => {
        const res = await request.get('/up')
        expect(res.status()).toBe(200)
    })

    test('GET /api/auth/login-meta mengembalikan 200', async ({ request }) => {
        const res = await withAuthAttemptsRetry(() => request.get('/api/auth/login-meta'))
        expect(res.status()).toBe(200)
    })

    test('GET /api/auth/register-meta mengembalikan 200', async ({ request }) => {
        const res = await withAuthAttemptsRetry(() => request.get('/api/auth/register-meta'))
        expect(res.status()).toBe(200)
    })

    test('POST /api/auth/login dengan kredensial salah mengembalikan 401 atau 422', async ({ request }) => {
        const res = await withAuthAttemptsRetry(() => request.post('/api/auth/login', {
            data: { username: 'nobody', password: 'wrongpass' },
        }))

        expect([401, 422, 400]).toContain(res.status())
    })

    test('GET /api/articles mengembalikan 200', async ({ request }) => {
        const res = await request.get('/api/articles')
        expect(res.status()).toBe(200)
    })

    test('GET /api/auth/me tanpa login mengembalikan 401 atau 403', async () => {
        // Pakai context terpisah agar tidak membawa cookie browser lain
        const ctx = await playwrightRequest.newContext({
            baseURL: process.env.APP_URL ?? 'http://localhost:8000',
            extraHTTPHeaders: { Accept: 'application/json' },
        })
        const res = await ctx.get('/api/auth/me')
        expect([401, 403]).toContain(res.status())
        await ctx.dispose()
    })

    test('POST /api/auth/login dengan kredensial benar mengembalikan token dan /me sukses', async ({ request }) => {
        await request.get('/sanctum/csrf-cookie')
        const loginRes = await withAuthAttemptsRetry(() => request.post('/api/auth/login', {
            data: {
                username: TEST_CUSTOMER.username,
                password: TEST_CUSTOMER.password,
            },
        }))

        expect([200, 201]).toContain(loginRes.status())

        const body = await loginRes.json()
        const token = body?.data?.access_token

        expect(token).toBeTruthy()

        const ctx = await playwrightRequest.newContext({
            baseURL: process.env.APP_URL ?? 'http://localhost:8000',
            extraHTTPHeaders: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
            },
        })
        const res = await ctx.get('/api/auth/me')
        expect(res.status()).toBe(200)
        const meBody = await res.json()
        expect(meBody).toHaveProperty('data')
        expect(meBody.data).toHaveProperty('username')
        await ctx.dispose()
    })
})
