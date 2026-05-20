<?php
/**
 * Instalador de un solo uso para hosting compartido (GoogieHost, etc.)
 *
 * COLOCACIÓN: este archivo debe ir junto a index.php (en public/ o public_html/).
 *
 * USO:
 *   1. Asegúrate de que .env tiene los datos correctos de MySQL y un SETUP_TOKEN.
 *   2. Visita en el navegador:  https://tu-dominio/setup.php?token=EL_TOKEN
 *   3. Verás los logs de migrate:fresh --seed.
 *   4. **BORRA ESTE ARCHIVO** del servidor inmediatamente después.
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(300); // las migraciones pueden tardar en shared hosting

// Detectar raíz del proyecto (misma lógica que index.php)
$base = is_file(__DIR__.'/../vendor/autoload.php')
    ? __DIR__.'/..'
    : __DIR__.'/../coches-app';

if (! is_file($base.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('No se encuentra la aplicación.');
}

require $base.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $base.'/bootstrap/app.php';

$app->loadEnvironmentFrom('.env');
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expected = env('SETUP_TOKEN');
$given = $_GET['token'] ?? '';

if (! $expected || $expected === 'cambiame-por-algo-aleatorio') {
    http_response_code(500);
    exit("SETUP_TOKEN no está configurado en .env. Edita .env y pon un valor aleatorio.");
}

if (! is_string($given) || ! hash_equals($expected, $given)) {
    http_response_code(403);
    exit("Token inválido. Visita esta URL con ?token=TU_SETUP_TOKEN");
}

header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><meta charset=utf-8><title>Setup Coches.app</title>";
echo "<style>body{font-family:ui-monospace,Menlo,Consolas,monospace;background:#0b1020;color:#e6e9f2;padding:20px;line-height:1.45}h1{color:#7dd3fc}pre{background:#111733;border:1px solid #1f2a4a;padding:12px;border-radius:8px;white-space:pre-wrap;word-break:break-word}.ok{color:#86efac}.warn{color:#fbbf24}.err{color:#fca5a5}</style>";
echo "<h1>Instalación de Coches.app</h1>";
echo "<p>Base: <code>$base</code></p>";

$output = new \Symfony\Component\Console\Output\BufferedOutput();

function step(string $title, callable $fn): void {
    echo "<h2>$title</h2><pre>";
    try {
        $result = $fn();
        echo htmlspecialchars((string) $result);
        echo "</pre><p class='ok'>✔ OK</p>";
    } catch (\Throwable $e) {
        echo htmlspecialchars($e->getMessage());
        echo "</pre><p class='err'>✘ Error: ".htmlspecialchars($e->getMessage())."</p>";
        exit;
    }
}

step('1. Verificar conexión MySQL', function () {
    \DB::connection()->getPdo();
    return 'Conectado a: '.\DB::connection()->getDatabaseName();
});

step('2. migrate:fresh --seed --force', function () use ($kernel, $output) {
    $kernel->call('migrate:fresh', ['--seed' => true, '--force' => true], $output);
    return $output->fetch();
});

step('3. config:cache', function () use ($kernel, $output) {
    $kernel->call('config:cache', [], $output);
    return $output->fetch();
});

step('4. route:cache', function () use ($kernel, $output) {
    $kernel->call('route:cache', [], $output);
    return $output->fetch();
});

step('5. view:cache', function () use ($kernel, $output) {
    $kernel->call('view:cache', [], $output);
    return $output->fetch();
});

echo "<h2 class='ok'>✅ Listo</h2>";
echo "<p class='warn'>Ahora <b>BORRA este archivo (setup.php)</b> del servidor por seguridad.</p>";
echo "<p>Visita tu home: <a href='/'>/</a></p>";
echo "<p>Credenciales de prueba:<br>Admin: <code>admin@coches.test</code> / <code>password</code><br>User:  <code>user@coches.test</code> / <code>password</code></p>";
