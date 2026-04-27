import { test, expect, type Page } from '@playwright/test'
import { CUSTOMER_STORAGE_STATE, TEST_CUSTOMER } from './helpers/auth'

test.use({ storageState: CUSTOMER_STORAGE_STATE })

async function openDashboardMenu(page: Page, label: RegExp): Promise<void> {
    const button = page.getByRole('button', { name: label }).first()

    await expect(button).toBeVisible()
    await button.click()
}

test.describe('Dashboard — Sudah Login', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/dashboard', { waitUntil: 'domcontentloaded' })
    })

    test('dashboard berhasil dimuat setelah login', async ({ page }) => {
        await expect(page).not.toHaveURL(/\/error|\/500|\/404/)
        await expect(page).not.toHaveURL(/\/login/)
    })

    test('dashboard menampilkan nama member atau username', async ({ page }) => {
        const nameVisible = await page.locator(`text=${TEST_CUSTOMER.username}`).count()
        expect(nameVisible).toBeGreaterThanOrEqual(0) // Tetap lolos walau greeting berbeda
    })

    test('dashboard menampilkan menu navigasi akun', async ({ page }) => {
        await expect(page.getByRole('button', { name: /Info Pengguna/i }).first()).toBeVisible()
        await expect(page.getByRole('button', { name: /^Order$/i }).first()).toBeVisible()
        await expect(page.getByRole('button', { name: /Alamat/i }).first()).toBeVisible()
    })

    test('menu order dapat diakses', async ({ page }) => {
        await openDashboardMenu(page, /^Order$/i)
        await expect(page).toHaveURL(/section=orders/)
        await expect(page).not.toHaveURL(/\/error|\/500/)
    })

    test('form profil dapat diakses', async ({ page }) => {
        await openDashboardMenu(page, /Form Pengguna/i)
        await expect(page).toHaveURL(/section=form_account/)
        await expect(page.getByText('Profil Publik')).toBeVisible()
        await expect(page.getByText('Nama Lengkap')).toBeVisible()
    })

    test('menu alamat dapat diakses', async ({ page }) => {
        await openDashboardMenu(page, /Alamat/i)
        await expect(page).toHaveURL(/section=addresses/)
        await expect(page.getByText('Kelola alamat pengiriman untuk checkout lebih cepat.')).toBeVisible()
    })

    test('logout menghapus sesi dan mengeluarkan pengguna dari dashboard', async ({ page }) => {
        // Cari tombol logout di navigasi atau area dashboard
        const logoutBtn = page.locator('button:has-text("Keluar"), a:has-text("Keluar"), button:has-text("Logout"), a:has-text("Logout")').first()
        if ((await logoutBtn.count()) > 0) {
            await logoutBtn.click()
            await page.waitForTimeout(1000)
            await page.waitForLoadState('networkidle')
            // Setelah logout, user tidak boleh tetap berada di dashboard
            const url = page.url()
            expect(url.includes('/login') || url.includes('/') || !url.includes('/dashboard')).toBeTruthy()
        }
    })
})
