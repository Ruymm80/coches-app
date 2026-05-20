<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Detección de la raíz del proyecto
|--------------------------------------------------------------------------
| Soporta dos formas de despliegue:
|
| 1) Estructura estándar (Laravel propio o VM):
|       proyecto/
|         ├─ vendor/
|         ├─ bootstrap/
|         └─ public/index.php     <-- este archivo
|    base path -> __DIR__/..
|
| 2) Hosting compartido (GoogieHost, InfinityFree, etc.):
|       /home/usuario/
|         ├─ coches-app/          (proyecto sin public/)
|         │   ├─ vendor/
|         │   └─ bootstrap/
|         └─ public_html/
|             └─ index.php        <-- este archivo
|    base path -> __DIR__/../coches-app
|
| El archivo intenta primero la opción 1; si no encuentra vendor/, salta a la 2.
*/

$base = is_file(__DIR__.'/../vendor/autoload.php')
    ? __DIR__.'/..'
    : __DIR__.'/../coches-app';

if (! is_file($base.'/vendor/autoload.php')) {
    http_response_code(500);
    exit('No se ha encontrado la aplicación. Sube el proyecto a "coches-app/" junto a "public_html/".');
}

// Modo mantenimiento
if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader
require $base.'/vendor/autoload.php';

// Bootstrap Laravel
/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
