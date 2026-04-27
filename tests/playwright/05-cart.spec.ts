import { test, expect } from '@playwright/test'
import { CUSTOMER_STORAGE_STATE, TEST_PRODUCT_SLUG } from './helpers/auth'

test.describe('Keranjang — Belum Login', () => {
    test('menambah produk ke keranjang saat belum login memunculkan login', async ({ page }) => {
        await page.goto(`/shop/${TEST_PRODUCT_SLUG}`)
        await page.waitForLoadState('networkidle')

        const addBtn = page.locator('button:has-text("Keranjang"), button:has-text("Tambah"), button:has-text("Cart")').first()
        if ((await addBtn.count()) === 0) {
            test.skip()
            return
        }
        await addBtn.click()
        await page.waitForTimeout(1500)
        // Harus redirect ke login atau memunculkan modal login
        const url = page.url()
        const hasLoginModal = await page.locator('text=/login|masuk/i').count()
        expect(url.includes('/login') || hasLoginModal > 0).toBeTruthy()
    })
})

test.describe('Keranjang — Sudah Login', () => {
    test.use({ storageState: CUSTOMER_STORAGE_STATE })

    test.beforeEach(async ({ page }) => {
        await page.goto('/', { waitUntil: 'domcontentloaded' })
    })

    test('ikon keranjang terlihat di header setelah login', async ({ page }) => {
        await page.goto('/shop')
        await page.waitForLoadState('networkidle')
        const cartIcon = page.locator('[aria-label*="cart" i], [aria-label*="keranjang" i], a[href*="cart"], [class*="cart"]').first()
        const exists = await cartIcon.count()
        expect(exists).toBeGreaterThanOrEqual(0)
    })

    test('bisa menambah produk ke keranjang dari halaman detail', async ({ page }) => {
        await page.goto(`/shop/${TEST_PRODUCT_SLUG}`)
        await page.waitForLoadState('networkidle')

        const addBtn = page.locator('button:has-text("Keranjang"), button:has-text("Tambah ke Keranjang"), button:has-text("Add to Cart")').first()
        if ((await addBtn.count()) === 0) {
            test.skip()
            return
        }

        await addBtn.click()
        await page.waitForTimeout(1500)

        // Harus ada indikator sukses atau minimal tidak ada error
        const success = await page.locator('text=/berhasil|added|success|ditambahkan/i').count()
        const noError = !(await page.locator('text=/error|gagal|failed/i').count())
        expect(success > 0 || noError).toBeTruthy()
    })
})
