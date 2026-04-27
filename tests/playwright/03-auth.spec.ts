import { test, expect } from '@playwright/test'
import { gotoAuthPage } from './helpers/auth'

test.describe('Autentikasi — Halaman Login', () => {
    test('halaman login menampilkan field yang wajib', async ({ page }) => {
        const response = await gotoAuthPage(page, '/login')

        expect(response?.status()).toBe(200)
        await expect(page).not.toHaveURL(/\/error|\/500/)
        await expect(page.locator('input[autocomplete="username"]')).toBeVisible()
        await expect(page.locator('#password')).toBeVisible()
        await expect(page.getByRole('button', { name: /Masuk ke Akun/i })).toBeVisible()
    })
})

test.describe('Autentikasi — Halaman Registrasi', () => {
    test('halaman registrasi menampilkan field wajib dan tautan login', async ({ page }) => {
        const response = await gotoAuthPage(page, '/register')

        expect(response?.status()).toBe(200)
        await expect(page).not.toHaveURL(/\/error|\/500/)
        await expect(page.locator('input[autocomplete="name"]').first()).toBeVisible()
        await expect(page.locator('input[autocomplete="username"]').first()).toBeVisible()
        await expect(page.locator('input[type="email"]').first()).toBeVisible()
        await expect(page.getByRole('link', { name: /Masuk/i }).first()).toBeVisible()
    })
})

test.describe('Autentikasi — Lupa Kata Sandi', () => {
    test('halaman lupa kata sandi berhasil dimuat', async ({ page }) => {
        const response = await gotoAuthPage(page, '/forgot-password')

        expect(response?.status()).toBe(200)
        await expect(page).not.toHaveURL(/\/error|\/500/)
        await expect(page.locator('input[autocomplete="username"]').first()).toBeVisible()
        await expect(page.locator('input[autocomplete="tel"]').first()).toBeVisible()
    })
})

test.describe('Autentikasi — Redirect Halaman Terproteksi', () => {
    test('membuka /dashboard tanpa login diarahkan ke halaman login', async ({ request }) => {
        const response = await request.get('/dashboard', {
            maxRedirects: 0,
        })

        expect(response.status()).toBe(302)
        expect(response.headers().location).toContain('/login')
    })
})
