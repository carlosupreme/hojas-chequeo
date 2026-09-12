import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8088';
const DOWNLOAD_DIR = path.resolve('.sandbox/downloads');

console.log('================================================================');
console.log(`[E2E] INICIANDO PRUEBAS DE DESCARGA DE BASE DE DATOS`);
console.log(`[E2E] Servidor objetivo: ${BASE_URL} (Nginx + PHP-FPM + PostgreSQL)`);
console.log('================================================================\n');

if (!fs.existsSync(DOWNLOAD_DIR)) {
    fs.mkdirSync(DOWNLOAD_DIR, { recursive: true });
}

async function run() {
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const context = await browser.newContext({
        acceptDownloads: true,
        viewport: { width: 1280, height: 800 }
    });

    const page = await context.newPage();

    try {
        // -------------------------------------------------------------
        // 1. Login en Filament
        // -------------------------------------------------------------
        console.log('▶ [Paso 1] Navegando a la pantalla de login (/admin/login)...');
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '01-login-screen.png') });

        console.log('▶ [Paso 2] Iniciando sesión como Administrador (admin@admin.com)...');
        await page.fill('input[type="email"]', 'admin@admin.com');
        await page.fill('input[type="password"]', 'password');
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '02-credentials-filled.png') });

        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);

        console.log(`✔ [Paso 2] Sesión iniciada con éxito. URL actual: ${page.url()}`);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '03-dashboard.png') });

        // -------------------------------------------------------------
        // 2. Navegación a Actualizar Sistema
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 3] Navegando a la página de Actualizar Sistema (/admin/system-update)...');
        await page.goto(`${BASE_URL}/admin/system-update`, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '04-system-update-page.png') });

        // -------------------------------------------------------------
        // 3. Prueba Modal UI - Formato TGZ
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 4] Probando descarga en formato TGZ mediante el botón y modal de Filament...');
        const downloadBtn = page.getByRole('button', { name: 'Descargar Base de Datos' }).first();
        await downloadBtn.click();
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '05-modal-tgz-open.png') });

        const submitModalBtn = page.getByRole('button', { name: 'Descargar Respaldo' });
        console.log('  -> Haciendo clic en "Descargar Respaldo" y esperando evento de descarga del navegador...');

        const [downloadTgz] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            submitModalBtn.click()
        ]);

        const tgzName = downloadTgz.suggestedFilename();
        const tgzPath = path.join(DOWNLOAD_DIR, tgzName);
        await downloadTgz.saveAs(tgzPath);
        console.log(`  ✔ Archivo TGZ recibido por el navegador: ${tgzName}`);

        const tgzSize = fs.statSync(tgzPath).size;
        console.log(`  ✔ Tamaño del archivo TGZ: ${(tgzSize / 1024).toFixed(2)} KB (${tgzSize} bytes)`);
        if (tgzSize < 1000) {
            throw new Error(`El archivo TGZ descargado es anormalmente pequeño: ${tgzSize} bytes`);
        }

        // Verificar integridad del archivo tar.gz
        const tgzListing = execSync(`tar -ztvf "${tgzPath}"`).toString().trim();
        console.log(`  ✔ Integridad de compresión TGZ válida. Contenido:\n    ${tgzListing}`);

        // Extraer y validar el volcado SQL
        execSync(`tar -xzf "${tgzPath}" -C "${DOWNLOAD_DIR}"`);
        const sqlName = tgzListing.split(/\s+/).pop();
        const extractedSql = path.join(DOWNLOAD_DIR, sqlName);
        if (fs.existsSync(extractedSql)) {
            const sampleTables = execSync(`grep -E "^CREATE TABLE" "${extractedSql}" | head -n 5`).toString().trim();
            console.log(`  ✔ Tablas verificadas dentro del volcado SQL:\n    ${sampleTables.split('\n').join('\n    ')}`);
            fs.unlinkSync(extractedSql);
        }

        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '06-after-tgz-download.png') });

        // -------------------------------------------------------------
        // 4. Prueba Modal UI - Formato ZIP
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 5] Probando descarga en formato ZIP mediante el modal...');
        await downloadBtn.click();
        await page.waitForTimeout(600);

        // Seleccionar opción ZIP
        await page.locator('input[type="radio"][value="zip"]').click();
        await page.waitForTimeout(300);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '07-modal-zip-selected.png') });

        console.log('  -> Haciendo clic en "Descargar Respaldo" (formato ZIP)...');
        const [downloadZip] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            submitModalBtn.click()
        ]);

        const zipName = downloadZip.suggestedFilename();
        const zipPath = path.join(DOWNLOAD_DIR, zipName);
        await downloadZip.saveAs(zipPath);
        console.log(`  ✔ Archivo ZIP recibido por el navegador: ${zipName}`);

        const zipSize = fs.statSync(zipPath).size;
        console.log(`  ✔ Tamaño del archivo ZIP: ${(zipSize / 1024).toFixed(2)} KB (${zipSize} bytes)`);
        if (zipSize < 1000) {
            throw new Error(`El archivo ZIP descargado es anormalmente pequeño: ${zipSize} bytes`);
        }

        // Verificar integridad con unzip
        const zipListing = execSync(`unzip -l "${zipPath}"`).toString().trim();
        console.log(`  ✔ Contenido del archivo ZIP:\n    ${zipListing.split('\n').slice(0, 5).join('\n    ')}`);
        const testZipIntegrity = execSync(`unzip -t "${zipPath}"`).toString().trim();
        console.log(`  ✔ Test de integridad ZIP: ${testZipIntegrity.split('\n').pop()}`);

        await page.waitForTimeout(1000);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '08-after-zip-download.png') });

        // -------------------------------------------------------------
        // 5. Prueba de Endpoint Directo HTTP Streaming
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 6] Probando endpoint directo de streaming HTTP (/admin/system-update/download-database?format=tgz)...');
        const [directDownload] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            page.goto(`${BASE_URL}/admin/system-update/download-database?format=tgz`).catch(e => {
                if (!e.message.includes('Download is starting')) throw e;
            })
        ]);

        const directName = directDownload.suggestedFilename();
        const directPath = path.join(DOWNLOAD_DIR, `direct_${directName}`);
        await directDownload.saveAs(directPath);
        console.log(`  ✔ Descarga directa por streaming completada: ${directName} (${(fs.statSync(directPath).size / 1024).toFixed(2)} KB)`);

        console.log('\n================================================================');
        console.log('🎉 TODAS LAS PRUEBAS COMPLETADAS EXITOSAMENTE');
        console.log('✔ Nginx reverse proxy');
        console.log('✔ PHP-FPM FastCGI backend');
        console.log('✔ PostgreSQL conexión y ejecución de pg_dump');
        console.log('✔ Descarga en formato TGZ validada');
        console.log('✔ Descarga en formato ZIP validada');
        console.log('✔ Endpoint HTTP streaming directo validado');
        console.log('✔ Integridad de compresión y tablas verificadas');
        console.log('================================================================\n');

    } catch (err) {
        console.error('\n❌ ERROR DURANTE LA EJECUCIÓN DEL TEST:', err);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, 'error-screenshot.png') });
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

run();
