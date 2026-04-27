import { test as setup, expect } from '@playwright/test'
import fs from 'node:fs/promises'
import path from 'node:path'
import { createCustomerSession, CUSTOMER_STORAGE_STATE, TEST_CUSTOMER } from './helpers/auth'

setup('authenticate customer and persist storage state', async ({ page }) => {
    await fs.mkdir(path.dirname(CUSTOMER_STORAGE_STATE), { recursive: true })

    await createCustomerSession(page, TEST_CUSTOMER.username, TEST_CUSTOMER.password)

    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' })

    await expect(page).not.toHaveURL(/\/login/)

    await page.context().storageState({ path: CUSTOMER_STORAGE_STATE })
})
