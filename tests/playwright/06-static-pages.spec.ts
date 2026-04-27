import { test, expect } from '@playwright/test'

const HALAMAN_PUBLIK = [
    { name: 'Beranda', path: '/' },
    { name: 'Halaman Shop', path: '/shop' },
]

test.describe('Halaman Publik — HTTP dan Render', () => {
    for (const { name, path } of HALAMAN_PUBLIK) {
        test(`${name} (${path}) mengembalikan 200 dan tampil tanpa crash`, async ({ page }) => {
            const response = await page.goto(path)
            await page.waitForLoadState('networkidle')

            // Status HTTP harus 200
            expect(response?.status()).toBe(200)

            // Tidak boleh masuk ke halaman error server
            await expect(page).not.toHaveURL(/\/error|\/500/)

            // Elemen <body> harus berisi konten
            const body = page.locator('body')
            await expect(body).toBeVisible()
            const html = await body.innerHTML()
            expect(html.trim().length).toBeGreaterThan(100)
        })
    }
})

test.describe('Artikel', () => {
    test('halaman daftar artikel berhasil dimuat', async ({ page }) => {
        const response = await page.goto('/articles')
        await page.waitForLoadState('networkidle')
        // Boleh 200 atau redirect, yang penting bukan 500
        expect(response?.status()).not.toBe(500)
    })
})

test.describe('Penanganan 404', () => {
    test('halaman yang tidak ada mengembalikan 404 atau redirect yang aman', async ({ page }) => {
        const response = await page.goto('/this-page-definitely-does-not-exist-12345')
        await page.waitForLoadState('networkidle')
        const status = response?.status() ?? 0
        // 404 atau 302 ke halaman lain masih diterima
        expect([200, 302, 404].includes(status)).toBeTruthy()
        // Tidak boleh error server 500
        expect(status).not.toBe(500)
    })
})
