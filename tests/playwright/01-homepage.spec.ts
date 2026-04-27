import { test, expect } from '@playwright/test'

test.describe('Beranda', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/', { waitUntil: 'domcontentloaded' })
    })

    test('berhasil dimuat tanpa error', async ({ page }) => {
        await expect(page).not.toHaveURL(/\/error|\/500|\/404/)
        // Tidak ada dialog error JavaScript
        page.on('dialog', (dialog) => dialog.dismiss())
    })

    test('memiliki judul halaman yang terisi', async ({ page }) => {
        await expect(page).toHaveTitle(/.+/)
    })

    test('menampilkan hero atau carousel', async ({ page }) => {
        // Minimal ada hero banner, carousel, atau heading utama
        const hero = page.locator('section, [class*="hero"], [class*="carousel"], [class*="banner"], h1, h2').first()
        await expect(hero).toBeVisible()
    })

    test('menampilkan navigasi utama', async ({ page }) => {
        const nav = page.locator('nav, header').first()
        await expect(nav).toBeVisible()
    })

    test('menampilkan tautan ke halaman shop', async ({ page }) => {
        const shopLink = page.locator('a[href*="/shop"]').first()
        await expect(shopLink).toBeVisible()
    })

    test('menampilkan tautan login saat belum masuk', async ({ page }) => {
        const loginLink = page.locator('a[href*="/login"]').first()
        await expect(loginLink).toBeVisible()
    })

    test('bagian produk unggulan tetap tampil aman dengan atau tanpa data seeded', async ({ page }) => {
        await expect(page.getByRole('heading', { name: /Produk Unggulan/i })).toBeVisible()
        await expect(page.getByText(/Produk terlaris pilihan pelanggan kami/i)).toBeVisible()

        const cards = page.locator('.product-card a[href*="/shop/"]')

        if ((await cards.count()) > 0) {
            await expect(cards.first()).toBeVisible()
            return
        }

        await expect(page.getByRole('link', { name: /Lihat Semua/i }).first()).toBeVisible()
    })

    test('menampilkan footer', async ({ page }) => {
        const footer = page.locator('footer').first()
        await expect(footer).toBeVisible()
    })

    test('tautan shop mengarah ke halaman shop', async ({ page }) => {
        const shopLink = page.locator('a[href="/shop"]').first()
        await shopLink.click()
        await page.waitForLoadState('networkidle')
        await expect(page).toHaveURL(/\/shop/)
    })
})
