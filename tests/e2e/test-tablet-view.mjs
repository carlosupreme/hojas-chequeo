import { chromium } from 'playwright';
import path from 'node:path';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8088';

async function run() {
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        viewport: { width: 1280, height: 900 }
    });
    const page = await context.newPage();

    try {
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });
        await page.fill('input[type="email"]', 'admin@admin.com');
        await page.fill('input[type="password"]', 'password');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);

        await page.goto(`${BASE_URL}/admin/create-chequeo?h=1`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);

        const viewports = [
            { name: 'tablet-768', width: 768, height: 1024 },
            { name: 'tablet-800', width: 800, height: 1280 },
            { name: 'mobile-390', width: 390, height: 844 },
        ];

        for (const vp of viewports) {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await page.waitForTimeout(600);

            const shotPath = `/home/carlos/.gemini/antigravity-cli/brain/dc600d1e-6269-4ff9-9097-32c425f24b53/scratch/${vp.name}-after.png`;
            await page.screenshot({ path: shotPath, fullPage: true });
            console.log(`Screenshot saved for ${vp.name} to ${shotPath}`);
        }
    } catch (e) {
        console.error('Error:', e);
    } finally {
        await context.close();
    }

    await browser.close();
}

run();
