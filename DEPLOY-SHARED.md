# Despliegue en hosting compartido (GoogieHost, InfinityFree, etc.)

Guía paso a paso. El código ya está adaptado: `public/index.php` detecta si está en hosting compartido y `setup.php` instala la base de datos desde el navegador.

> ⚠ **Antes de nada**: comprueba en el panel de tu hosting que dispones de **PHP 8.3 o superior**. Laravel 13 no arranca con versiones inferiores. Si solo hay PHP 8.0–8.2, dímelo y bajamos el proyecto a Laravel 11.

---

## 0. Lo que vas a subir

Tu hosting tendrá una estructura tipo:

```
/home/tu_usuario/
├─ coches-app/          ← proyecto Laravel (sin la carpeta public/)
│  ├─ app/
│  ├─ bootstrap/
│  ├─ config/
│  ├─ database/
│  ├─ resources/
│  ├─ routes/
│  ├─ storage/
│  ├─ vendor/           ← OBLIGATORIO, lo generamos en local
│  ├─ .env              ← tu .env de producción
│  └─ artisan
└─ public_html/         ← lo que sirve el dominio
   ├─ build/            ← copia de public/build/
   ├─ favicon.ico
   ├─ .htaccess
   ├─ index.php
   ├─ robots.txt
   └─ setup.php         ← se borra después de instalar
```

Si tu hosting llama al directorio web `htdocs/` o `www/` en vez de `public_html/`, el principio es el mismo.

---

## 1. Preparar el paquete en local

Desde la carpeta del proyecto en tu PC:

```bash
# 1. Dependencias de producción (sin paquetes de dev)
composer install --no-dev --optimize-autoloader --prefer-dist

# 2. Compilar assets (Tailwind + JS)
npm ci
npm run build
```

Esto crea `vendor/` ligero (~40 MB) y `public/build/` con el CSS y JS minificados.

## 2. Preparar el `.env` de producción

1. Copia `.env.production` a `.env`:
   ```bash
   cp .env.production .env
   ```
2. Edita `.env` con los datos reales del hosting:

   ```env
   APP_NAME="Coches.app"
   APP_ENV=production
   APP_KEY=base64:NQ6qRchUfzW9A9FDrLGHyIY+XEZmKqSfGO6Vv0POd24=
   APP_DEBUG=false
   APP_URL=https://TU-SUBDOMINIO.epizy.com

   DB_CONNECTION=mysql
   DB_HOST=sqlxxx.epizy.com          # el que te diga el panel
   DB_PORT=3306
   DB_DATABASE=epiz_xxxxx_coches
   DB_USERNAME=epiz_xxxxx
   DB_PASSWORD=tu_password_real

   SETUP_TOKEN=algo-largo-y-aleatorio
   ```

> **Nota sobre `APP_KEY`**: ya hay una generada de ejemplo. Si quieres una nueva ejecuta en local `php artisan key:generate --show` y pegas el valor.

> **Sobre `SETUP_TOKEN`**: cámbialo por algo aleatorio. Sirve para que solo tú puedas ejecutar `setup.php`.

## 3. Crear la base de datos en GoogieHost

En el panel de GoogieHost (probablemente CPanel-like):

1. Busca **MySQL Databases**.
2. **Create New Database** → nombre p.ej. `coches`. Te dará un nombre completo tipo `epiz_xxxxx_coches`.
3. **Create User** → nombre y contraseña.
4. **Add User to Database** → marca **All Privileges**.
5. Anota: host MySQL, nombre BD, usuario, contraseña → llévalos al `.env`.

## 4. Subir los archivos por FTP

Usa **FileZilla** con los datos FTP del panel.

### a) Subir el proyecto a `coches-app/` (carpeta hermana de `public_html/`)

**Sube todo el proyecto EXCEPTO la carpeta `public/`**:

```
app/  bootstrap/  config/  database/  resources/  routes/
storage/  vendor/  artisan  composer.json  composer.lock
.env  .env.example
```

**No subas**: `node_modules/`, `tests/`, `.git/`, `public/`.

> En FileZilla, configura que muestre archivos ocultos para que veas `.env` (Servidor → Forzar mostrar archivos ocultos).

### b) Subir el contenido de `public/` a `public_html/`

Sube **el contenido** de `public/` (no la carpeta, sino lo de dentro) al directorio web del hosting (`public_html/` o `htdocs/`):

```
public_html/
├─ build/             ← public/build/ entero
├─ favicon.ico
├─ .htaccess
├─ index.php
├─ robots.txt
└─ setup.php
```

## 5. Permisos

En el File Manager del panel, click derecho sobre estas carpetas y pon permisos **775** (recursivo) en:

- `coches-app/storage/`
- `coches-app/bootstrap/cache/`

Esto permite a PHP escribir caché, logs y sesiones.

## 6. Lanzar el instalador

Abre en el navegador:

```
https://TU-SUBDOMINIO.epizy.com/setup.php?token=TU_SETUP_TOKEN
```

Verás un panel con 5 pasos:

1. ✔ Verificar conexión MySQL
2. ✔ migrate:fresh --seed --force (crea tablas y siembra 100 anuncios + usuarios)
3. ✔ config:cache
4. ✔ route:cache
5. ✔ view:cache

Si todo sale verde, ve al paso 7.

**Si algún paso falla**:

| Error | Causa probable | Solución |
|---|---|---|
| `SQLSTATE[HY000] [2002]` | DB_HOST mal | Mira el host exacto en el panel del hosting |
| `Access denied for user` | DB_USERNAME / DB_PASSWORD mal | Reescribe en `.env` y recarga |
| `Unknown database` | DB_DATABASE mal | El nombre lleva un prefijo tipo `epiz_xxxxx_` |
| `Permission denied` en `storage/logs` | permisos | Cambia a 775 en `storage/` recursivo |
| `Class not found` | `vendor/` no subió completo | Vuelve a subirlo, comprueba que `vendor/autoload.php` existe |
| `No application encryption key` | `.env` sin `APP_KEY` o no se subió | Revisa que `.env` esté en `coches-app/.env` |

## 7. Borrar `setup.php` 🔥

**Importante**: una vez termine la instalación, **borra** `public_html/setup.php`. Si lo dejas, cualquiera con el token podría reinstalar tu BD.

Desde FileZilla: clic derecho → Eliminar.

## 8. Probar

Visita `https://TU-SUBDOMINIO.epizy.com/`. Deberías ver la home con los 100 anuncios.

Login de prueba:
- **Admin**: `admin@coches.test` / `password`
- **User**: `user@coches.test` / `password`

---

## Actualizaciones posteriores

Cuando cambies código en local:

```bash
# En local
composer install --no-dev --optimize-autoloader --prefer-dist
npm run build
```

Sube los archivos cambiados por FTP (FileZilla solo sube los que han cambiado si activas "Sincronizar carpetas").

**Si añades migraciones nuevas**, no hay CLI, así que tienes dos opciones:

1. Re-subir `setup.php`, ejecutar con el token y borrar otra vez (esto borra y resemilla todo).
2. O usa **phpMyAdmin** del hosting para aplicar los SQL a mano.

Para que `setup.php` solo ejecute las migraciones nuevas sin borrar datos, cámbialo a `migrate --force` en la línea correspondiente (`migrate:fresh` → `migrate`).

---

## Limitaciones conocidas en hosting compartido gratis

- **Sin SSH/CLI**: nada de `php artisan` por terminal.
- **Sin symlinks**: `php artisan storage:link` no funciona. Las imágenes que suban los usuarios desde la web NO se mostrarán hasta que copies manualmente `coches-app/storage/app/public/` a `public_html/storage/`. Los 100 anuncios del seeder usan URLs externas de loremflickr, así que esto no afecta al seed.
- **Recursos limitados**: la primera petición puede tardar 2–3 s (Laravel sin caché de OPcache).
- **Sin mail real**: el `.env` deja `MAIL_MAILER=log`, así que los emails de "verificar email" se quedan en `storage/logs/laravel.log`. Si quieres envío real, configura SMTP de GoogieHost.
- **Sin queues/cron**: no afecta a este proyecto.

---

## Checklist final

- [ ] PHP 8.3+ habilitado en el panel del hosting
- [ ] Base de datos MySQL creada con usuario asignado
- [ ] `composer install --no-dev --prefer-dist` ejecutado en local
- [ ] `npm run build` ejecutado en local
- [ ] `.env` copiado de `.env.production` con datos reales del hosting
- [ ] Proyecto subido a `coches-app/` (carpeta hermana de `public_html/`)
- [ ] Contenido de `public/` subido a `public_html/`
- [ ] Permisos 775 en `storage/` y `bootstrap/cache/`
- [ ] `https://tu-dominio/setup.php?token=...` ejecutado con éxito
- [ ] `setup.php` borrado del servidor
- [ ] Login con `admin@coches.test` / `password` funciona
