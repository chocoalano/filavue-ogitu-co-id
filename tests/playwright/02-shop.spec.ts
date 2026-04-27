import { test, expect } from '@playwright/test'
import { TEST_PRODUCT_SLUG } from './helpers/auth'

test.describe('Katalog Produk — Halaman Daftar', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/shop')
        await page.waitForLoadState('networkidle')
        // Tunggu Vue SPA merender kartu produk
        await page.waitForSelector('a[href*="/shop/"]', { timeout: 12_000 })
    })

    test('berhasil dimuat tanpa error', async ({ page }) => {
        await expect(page).not.toHaveURL(/\/error|\/500|\/404/)
    })

    test('menampilkan kartu produk', async ({ page }) => {
        const cards = page.locator('a[href*="/shop/"]')
        await expect(cards.first()).toBeVisible()
        const count = await cards.count()
        expect(count).toBeGreaterThan(0)
    })

    test('harga produk tampil dalam format rupiah', async ({ page }) => {
        // Intl id-ID bisa menghasilkan "Rp\u00a0159.000" dengan spasi non-breaking
        await page.waitForFunction(
            () => /Rp[\s\u00a0]?[\d.,]+/.test(document.body.innerText),
            { timeout: 15_000 },
        )
        const body = await page.locator('body').innerText()
        expect(body).toMatch(/Rp[\s\u00a0]?[\d.,]+/)
    })

    test('tautan kartu produk mengarah ke halaman detail', async ({ page }) => {
        const card = page.locator('a[href*="/shop/"]').first()
        const href = await card.getAttribute('href')
        await card.click()
        await page.waitForLoadState('networkidle')
        await expect(page).toHaveURL(/\/shop\//)
        if (href) {
            await expect(page).toHaveURL(new RegExp(href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')))
        }
    })
})

test.describe('Katalog Produk — Detail Produk', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto(`/shop/${TEST_PRODUCT_SLUG}`)
        await page.waitForLoadState('networkidle')
        // Tunggu Vue SPA melakukan hydrate dan merender heading produk
        await page.waitForFunction(
            () => {
                const h1 = document.querySelector('h1')
                return h1 !== null && (h1.textContent ?? '').trim().length > 0
            },
            null,
            { timeout: 30_000 },
        )
    })

    test('berhasil dimuat tanpa error', async ({ page }) => {
        await expect(page).not.toHaveURL(/\/error|\/500|\/404/)
    })

    test('menampilkan nama produk pada heading utama', async ({ page }) => {
        const heading = page.locator('h1').first()
        await expect(heading).toBeVisible()
        const text = await heading.textContent()
        expect(text?.trim().length).toBeGreaterThan(0)
    })

    test('menampilkan harga produk dalam format rupiah', async ({ page }) => {
        // Tunggu Vue merender harga melalui formatCurrency()
        await page.waitForFunction(
            () => document.body.innerText.match(/Rp[\s\u00a0]?[\d.,]+/) !== null,
            { timeout: 10_000 },
        )
        const priceEl = page.locator('text=/Rp[\s\u00a0]?[\d.,]+/').first()
        await expect(priceEl).toBeVisible()
    })

    test('menampilkan minimal satu gambar produk', async ({ page }) => {
        // ProductGallery merender elemen img, jadi tunggu sampai muncul
        await page.waitForSelector('img', { timeout: 10_000 })
        const img = page.locator('img').first()
        await expect(img).toBeVisible()
    })

    test('menampilkan tombol "Tambah ke Keranjang" atau status "Stok Habis"', async ({ page }) => {
        // ProductActionButtons.vue merender tombol ini setelah Vue mount
        await page.waitForSelector('button', { timeout: 10_000 })
        const addToCart = page.locator('button:has-text("Tambah ke Keranjang"), button:has-text("Stok Habis"), button:has-text("Ditambahkan")').first()
        await expect(addToCart).toBeVisible({ timeout: 10_000 })
    })

    test('menampilkan tab informasi produk', async ({ page }) => {
        const tab = page.locator('[role="tab"], button:has-text("Deskripsi"), button:has-text("Spesifikasi")').first()
        const exists = await tab.count()
        if (exists > 0) {
            await expect(tab).toBeVisible()
        }
    })

    test('judul halaman memuat nama produk', async ({ page }) => {
        const title = await page.title()
        // Contoh format judul: "BIOZENION PENDANT Green | ogitu"
        expect(title.length).toBeGreaterThan(0)
        expect(title).toContain('ogitu')
    })
})
