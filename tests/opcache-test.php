<?php
header('Content-Type: text/plain; charset=utf-8');

echo "===========================================\n";
echo "VERIFICACIÓN DE OPCACHE Y JIT - PHP " . PHP_VERSION . "\n";
echo "===========================================\n\n";

// 1. Verificar si OPcache está cargado
if (!extension_loaded('Zend OPcache')) {
    echo "❌ ERROR: Extensión OPcache NO cargada\n";
    exit(1);
}
echo "✓ Extensión OPcache cargada\n\n";

// 2. Verificar configuración
echo "CONFIGURACIÓN:\n";
echo "-------------------------------------------\n";
echo "opcache.enable: " . ini_get('opcache.enable') . "\n";
echo "opcache.jit: " . ini_get('opcache.jit') . "\n";
echo "opcache.jit_buffer_size: " . ini_get('opcache.jit_buffer_size') . "\n";
echo "\n";

// 3. Estado de OPcache
$status = opcache_get_status(false);

if ($status === false) {
    echo "❌ ERROR: No se pudo obtener el estado de OPcache\n";
    exit(1);
}

echo "ESTADO DE OPCACHE:\n";
echo "-------------------------------------------\n";
echo "Habilitado: " . ($status['opcache_enabled'] ? '✓ SÍ' : '❌ NO') . "\n";
echo "Cache full: " . ($status['cache_full'] ? '❌ SÍ' : '✓ NO') . "\n";
echo "Scripts en caché: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
echo "Hit rate: " . round($status['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
echo "Hits: " . $status['opcache_statistics']['hits'] . "\n";
echo "Misses: " . $status['opcache_statistics']['misses'] . "\n";
echo "\n";

echo "MEMORIA:\n";
echo "-------------------------------------------\n";
echo "Usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
echo "Libre: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
echo "Desperdiciada: " . round($status['memory_usage']['wasted_memory'] / 1024 / 1024, 2) . " MB\n";
echo "\n";

// 4. JIT Status
echo "ESTADO DE JIT:\n";
echo "-------------------------------------------\n";
if (isset($status['jit'])) {
    echo "Habilitado: " . ($status['jit']['enabled'] ? '✓ SÍ' : '❌ NO') . "\n";
    echo "Activo: " . ($status['jit']['on'] ? '✓ SÍ' : '⚠ NO (se activa con carga)') . "\n";
    echo "Kind: " . $status['jit']['kind'] . " " . ($status['jit']['kind'] > 0 ? '✓' : '❌ DEBE SER > 0') . "\n";
    echo "Opt Level: " . $status['jit']['opt_level'] . " " . ($status['jit']['opt_level'] > 0 ? '✓' : '❌ DEBE SER > 0') . "\n";
    echo "Opt Flags: " . $status['jit']['opt_flags'] . "\n";
    echo "Buffer Size: " . round($status['jit']['buffer_size'] / 1024 / 1024, 2) . " MB\n";
    echo "Buffer Free: " . round($status['jit']['buffer_free'] / 1024 / 1024, 2) . " MB\n";
    echo "Buffer Used: " . round(($status['jit']['buffer_size'] - $status['jit']['buffer_free']) / 1024 / 1024, 2) . " MB\n";
} else {
    echo "❌ JIT no disponible en este build de PHP\n";
}

echo "\n===========================================\n";
echo "INTERPRETACIÓN:\n";
echo "-------------------------------------------\n";

if (isset($status['jit'])) {
    if ($status['jit']['enabled'] && $status['jit']['kind'] > 0 && $status['jit']['opt_level'] > 0) {
        echo "✓ JIT está correctamente configurado y funcionando\n";
    } elseif ($status['jit']['enabled'] && $status['jit']['kind'] == 0) {
        echo "⚠ JIT habilitado pero NO optimizado\n";
        echo "  Verifica: opcache.jit = 1255 en php.ini\n";
    } else {
        echo "❌ JIT NO está funcionando correctamente\n";
    }
}

echo "===========================================\n";
