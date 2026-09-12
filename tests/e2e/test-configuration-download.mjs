import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { execSync } from 'node:child_process';

const BASE_URL = process.env.APP_URL || 'http://127.0.0.1:8088';
const DOWNLOAD_DIR = path.resolve('.sandbox/downloads-config');

console.log('================================================================');
console.log(`[E2E] INICIANDO PRUEBAS DE DESCARGA DE CONFIGURACIÓN DEL SISTEMA`);
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
        viewport: { width: 1280, height: 900 }
    });

    const page = await context.newPage();

    try {
        // -------------------------------------------------------------
        // 1. Login en Filament
        // -------------------------------------------------------------
        console.log('▶ [Paso 1] Navegando a la pantalla de login (/admin/login)...');
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });

        console.log('▶ [Paso 2] Iniciando sesión como Administrador (admin@admin.com)...');
        await page.fill('input[type="email"]', 'admin@admin.com');
        await page.fill('input[type="password"]', 'password');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }),
            page.click('button[type="submit"]')
        ]);

        console.log(`✔ [Paso 2] Sesión iniciada con éxito. URL: ${page.url()}`);

        // -------------------------------------------------------------
        // 2. Navegación a Actualizar Sistema
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 3] Navegando a /admin/system-update...');
        await page.goto(`${BASE_URL}/admin/system-update`, { waitUntil: 'networkidle' });
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '01-system-update-page.png') });

        // Verificar existencia del botón de Configuración
        const configBtn = page.getByRole('button', { name: 'Descargar Configuración Actual' }).first();
        const exists = await configBtn.isVisible();
        if (!exists) {
            throw new Error('El botón "Descargar Configuración Actual" no está visible en la página.');
        }
        console.log('✔ Botón "Descargar Configuración Actual" encontrado en la interfaz.');

        // -------------------------------------------------------------
        // 3. Modal UI - Descarga TGZ
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 4] Abriendo modal de descarga de configuración...');
        await configBtn.click();
        await page.waitForTimeout(600);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, '02-modal-config-open.png') });

        const submitModalBtn = page.getByRole('button', { name: 'Descargar Configuración', exact: true });
        console.log('  -> Haciendo clic en "Descargar Configuración" (formato TGZ por defecto)...');

        const [downloadTgz] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            submitModalBtn.click()
        ]);

        const tgzName = downloadTgz.suggestedFilename();
        const tgzPath = path.join(DOWNLOAD_DIR, tgzName);
        await downloadTgz.saveAs(tgzPath);
        console.log(`  ✔ Archivo TGZ recibido: ${tgzName}`);

        const tgzSize = fs.statSync(tgzPath).size;
        console.log(`  ✔ Tamaño TGZ: ${(tgzSize / 1024).toFixed(2)} KB (${tgzSize} bytes)`);
        if (tgzSize < 1000) {
            throw new Error(`El archivo TGZ descargado es anormalmente pequeño: ${tgzSize} bytes`);
        }

        // Inspeccionar contenido del TGZ
        const tgzListing = execSync(`tar -ztvf "${tgzPath}"`).toString();
        console.log('  ✔ Archivos empaquetados en TGZ:');
        console.log('    - Nginx: ' + (tgzListing.includes('nginx') ? 'PRESENTE' : 'NO DETECTADO'));
        console.log('    - PHP: ' + (tgzListing.includes('php') ? 'PRESENTE' : 'NO DETECTADO'));
        console.log('    - PostgreSQL: ' + (tgzListing.includes('postgresql') ? 'PRESENTE' : 'NO DETECTADO'));
        console.log('    - MANIFEST.json: ' + (tgzListing.includes('MANIFEST.json') ? 'PRESENTE' : 'NO DETECTADO'));
        console.log('    - system_info.txt: ' + (tgzListing.includes('system_info.txt') ? 'PRESENTE' : 'NO DETECTADO'));

        if (!tgzListing.includes('nginx') || !tgzListing.includes('php') || !tgzListing.includes('postgresql')) {
            throw new Error('El paquete TGZ no contiene las configuraciones requeridas (Nginx, PHP, PostgreSQL).');
        }

        // Extraer y validar MANIFEST.json
        const extractDir = path.join(DOWNLOAD_DIR, 'extracted_tgz');
        if (fs.existsSync(extractDir)) {
            fs.rmSync(extractDir, { recursive: true, force: true });
        }
        fs.mkdirSync(extractDir, { recursive: true });
        execSync(`tar -xzf "${tgzPath}" -C "${extractDir}"`);

        const manifestPath = path.join(extractDir, 'MANIFEST.json');
        if (!fs.existsSync(manifestPath)) {
            throw new Error('MANIFEST.json no existe dentro del archivo comprimido.');
        }
        const manifestData = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        console.log(`  ✔ MANIFEST.json parseado correctamente. Servidor: ${manifestData.server_info?.uname}`);
        console.log(`    PHP versión: ${manifestData.server_info?.php_version}`);
        console.log(`    PostgreSQL versión: ${manifestData.server_info?.postgresql_version}`);

        // -------------------------------------------------------------
        // 4. Modal UI - Descarga ZIP
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 5] Probando descarga en formato ZIP...');
        await page.reload({ waitUntil: 'networkidle' });
        const configBtnZip = page.getByRole('button', { name: 'Descargar Configuración Actual' }).first();
        await configBtnZip.click();
        await page.waitForTimeout(600);

        await page.locator('input[type="radio"][value="zip"]').click();
        await page.waitForTimeout(300);

        const submitZipBtn = page.getByRole('button', { name: 'Descargar Configuración', exact: true });
        const [downloadZip] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            submitZipBtn.click()
        ]);

        const zipName = downloadZip.suggestedFilename();
        const zipPath = path.join(DOWNLOAD_DIR, zipName);
        await downloadZip.saveAs(zipPath);
        console.log(`  ✔ Archivo ZIP recibido: ${zipName}`);

        const zipSize = fs.statSync(zipPath).size;
        console.log(`  ✔ Tamaño ZIP: ${(zipSize / 1024).toFixed(2)} KB (${zipSize} bytes)`);

        const zipIntegrity = execSync(`unzip -t "${zipPath}"`).toString().trim();
        console.log(`  ✔ Integridad ZIP: ${zipIntegrity.split('\n').pop()}`);

        const zipListing = execSync(`unzip -l "${zipPath}"`).toString();
        if (!zipListing.includes('nginx') || !zipListing.includes('php') || !zipListing.includes('postgresql')) {
            throw new Error('El paquete ZIP no contiene las configuraciones requeridas (Nginx, PHP, PostgreSQL).');
        }

        // -------------------------------------------------------------
        // 5. Descarga directa vía ruta HTTP Nginx
        // -------------------------------------------------------------
        console.log('\n▶ [Paso 6] Probando endpoint directo GET /admin/system-update/download-configuration?format=tgz...');
        const [directTgz] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            page.goto(`${BASE_URL}/admin/system-update/download-configuration?format=tgz`).catch(() => {})
        ]);

        const directTgzName = directTgz.suggestedFilename();
        const directTgzPath = path.join(DOWNLOAD_DIR, 'direct_' + directTgzName);
        await directTgz.saveAs(directTgzPath);
        console.log(`  ✔ Descarga directa exitosa a través de Nginx (TGZ): ${directTgzName} (${fs.statSync(directTgzPath).size} bytes)`);

        console.log('\n▶ [Paso 7] Probando endpoint directo GET /admin/system-update/download-configuration?format=zip...');
        const [directZip] = await Promise.all([
            page.waitForEvent('download', { timeout: 30000 }),
            page.goto(`${BASE_URL}/admin/system-update/download-configuration?format=zip`).catch(() => {})
        ]);

        const directZipName = directZip.suggestedFilename();
        const directZipPath = path.join(DOWNLOAD_DIR, 'direct_' + directZipName);
        await directZip.saveAs(directZipPath);
        console.log(`  ✔ Descarga directa exitosa a través de Nginx (ZIP): ${directZipName} (${fs.statSync(directZipPath).size} bytes)`);

        console.log('\n================================================================');
        console.log('🎉 TODAS LAS PRUEBAS E2E DE DESCARGA DE CONFIGURACIÓN PASARON AL 100%');
        console.log('================================================================\n');

    } catch (error) {
        console.error('\n❌ ERROR DURANTE LA PRUEBA E2E:', error);
        await page.screenshot({ path: path.join(DOWNLOAD_DIR, 'error-screenshot.png') });
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

run();
