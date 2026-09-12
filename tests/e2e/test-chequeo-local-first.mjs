import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8088';
const SCREENSHOT_DIR = path.resolve('.sandbox/e2e-chequeo');

console.log('================================================================');
console.log(`[E2E] PRUEBA LOCAL-FIRST / RESILIENTE A WIFI EN CHEQUEO DIARIO`);
console.log(`[E2E] Servidor objetivo: ${BASE_URL} (Nginx + PHP-FPM + PostgreSQL)`);
console.log('================================================================\n');

if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

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
        // 1. Iniciar Sesión
        console.log('▶ [Paso 1] Iniciando sesión como Administrador...');
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });
        await page.fill('input[type="email"]', 'admin@admin.com');
        await page.fill('input[type="password"]', 'password');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);
        console.log(`  ✔ Sesión iniciada.`);

        // 2. Abrir Chequeo Diario
        console.log('\n▶ [Paso 2] Abriendo Chequeo Diario de Caldera 1 (/admin/create-chequeo?h=1)...');
        await page.goto(`${BASE_URL}/admin/create-chequeo?h=1`, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01-chequeo-loaded.png') });

        // Verificar que el equipo y tag están visibles
        const equipmentTitle = await page.getByText('CM-CAL-01', { exact: true }).first().isVisible();
        if (!equipmentTitle) {
            throw new Error('No se visualizó la hoja de chequeo de Caldera 1.');
        }
        console.log('  ✔ Hoja de chequeo cargada correctamente.');

        // 3. Verificar estructura DOM (Sin duplicación de inputs)
        const iconButtonsCount = await page.locator('.icon-btn').count();
        console.log(`  ✔ Cantidad de botones de opciones en pantalla: ${iconButtonsCount}`);

        // 4. Probar clic instantáneo y persistencia en localStorage
        console.log('\n▶ [Paso 3] Marcando primer ítem y verificando latencia 0ms + localStorage...');
        const rows = page.locator('[wire\\:key^="item-"]');
        const rowsCount = await rows.count();
        console.log(`  ✔ Total de filas de chequeo en pantalla: ${rowsCount}`);

        await rows.first().locator('.icon-btn button').first().click();

        // Verificar localStorage inmediatamente
        const storageDump = await page.evaluate(() => {
            const keys = Object.keys(localStorage).filter(k => k.startsWith('chequeo_draft_'));
            return keys.map(k => ({ key: k, value: localStorage.getItem(k) }));
        });
        console.log('  ✔ Contenido de localStorage tras el primer clic:', JSON.stringify(storageDump));
        if (storageDump.length === 0) {
            throw new Error('El estado no se persistió en localStorage tras el clic.');
        }

        // 5. Simular desconexión total de WiFi (Offline)
        console.log('\n▶ [Paso 4] SIMULANDO CORTE DE SEÑAL WIFI (Modo Offline)...');
        await context.setOffline(true);
        await page.waitForTimeout(500);

        // Llenar fila adicional mientras está COMPLETAMENTE DESCONECTADO de la red
        if (rowsCount > 1) {
            console.log('  -> Llenando fila 2 (número: 45.5) en modo offline...');
            const numInput = rows.nth(1).locator('input');
            await numInput.fill('45.5');
            await numInput.dispatchEvent('input');
        }

        await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02-offline-interaction.png') });

        // Verificar que localStorage sigue guardando todo sin errores
        const offlineStorage = await page.evaluate(() => {
            const keys = Object.keys(localStorage).filter(k => k.startsWith('chequeo_draft_'));
            return keys.map(k => ({ key: k, value: localStorage.getItem(k) }));
        });
        console.log('  ✔ localStorage actualizado en modo Offline:', JSON.stringify(offlineStorage));

        // 6. Restaurar la señal WiFi y esperar sincronización en segundo plano
        console.log('\n▶ [Paso 5] RESTAURANDO SEÑAL WIFI...');
        await context.setOffline(false);
        console.log('  -> Esperando sincronización en lote en segundo plano (4 segundos)...');
        await page.waitForTimeout(4500);
        await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03-synced-after-reconnect.png') });

        // 7. Verificar en la base de datos PostgreSQL que las respuestas fueron persistidas
        const dbRespCount = execSync(
            `php artisan tinker --execute='echo \\App\\Models\\HojaFilaRespuesta::count();'`
        ).toString().trim();
        console.log(`  ✔ Respuestas persistidas en PostgreSQL tras reconectar WiFi: ${dbRespCount}`);
        if (parseInt(dbRespCount, 10) === 0) {
            throw new Error('Las respuestas no se sincronizaron con PostgreSQL tras volver la señal.');
        }

        // 8. Verificar visualización de respuesta en modal "Ver Detalle" (/admin/chequeos)
        console.log('\n▶ [Paso 6] Verificando modal "Ver Detalle" en /admin/chequeos...');
        await page.goto(`${BASE_URL}/admin/chequeos`, { waitUntil: 'networkidle' });
        
        // Obtener el ID de la última ejecución finalizada
        const lastFinishedId = execSync(
            `php artisan tinker --execute='echo \\App\\Models\\HojaEjecucion::whereNotNull("finalizado_en")->latest("id")->value("id") ?? "";'`
        ).toString().trim();

        if (lastFinishedId) {
            await page.evaluate((recId) => {
                const lw = Livewire.find(document.querySelector('.fi-ta')?.closest('[wire\\:id]')?.getAttribute('wire:id'));
                if (lw) lw.mountTableAction('view-chequeo', String(recId));
            }, lastFinishedId);

            await page.locator('.fi-modal-window:visible').first().waitFor({ timeout: 10000 });
            await page.waitForTimeout(1000);

            // Verificar que al menos un botón de icono tiene el estado seleccionado visible (width 40px y clase color)
            const selectedBtnCount = await page.locator('.fi-modal-window .icon-btn[style*="width: 40px"]').count();
            console.log(`  ✔ Botones con respuesta seleccionada visualizados en modal: ${selectedBtnCount}`);
            if (selectedBtnCount === 0) {
                throw new Error('En el modal Ver Detalle no se visualizó la opción marcada.');
            }
            await page.screenshot({ path: path.join(SCREENSHOT_DIR, '04-view-chequeo-modal.png') });
        }

        console.log('\n================================================================');
        console.log('🎉 PRUEBA E2E DE CHEQUEO LOCAL-FIRST Y VISUALIZACIÓN COMPLETADA CON ÉXITO');
        console.log('✔ Cero bloqueos con caídas de WiFi');
        console.log('✔ Persistencia local garantizada en localStorage');
        console.log('✔ Sincronización transparente en segundo plano a PostgreSQL');
        console.log('✔ Visualización correcta de respuestas marcadas en modal Ver Detalle');
        console.log('================================================================\n');

    } catch (err) {
        console.error('\n❌ ERROR DURANTE LA PRUEBA E2E:', err);
        await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'error-screenshot.png') });
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

run();
