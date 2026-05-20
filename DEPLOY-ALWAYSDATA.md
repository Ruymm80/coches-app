# Despliegue en AlwaysData (gratis para siempre)

AlwaysData ofrece un plan gratis con SSH, PHP 8.3, MySQL y 100 MB de disco. Es de las pocas opciones gratuitas reales donde Laravel funciona bien porque tienes terminal y puedes correr `composer` y `php artisan`.

## Límites del plan gratis (lo que es bueno saber)

| Recurso | Límite | ¿Suficiente? |
|---|---|---|
| Disco | 100 MB | ✅ proyecto ocupa ~45 MB |
| MySQL | 1 base de datos, 10 MB | ✅ esquema + 100 anuncios ≈ 1 MB |
| Tráfico mensual | sin límite oficial | ✅ |
| Subdominio | `tuname.alwaysdata.net` gratis | ✅ |
| SSH | ✅ activo en el free | ✅ |
| Cron | máx. 1 tarea/hora | irrelevante para este proyecto |

> ⚠ Si en el panel solo ves PHP ≤ 8.2 disponible, dímelo y bajamos el proyecto a Laravel 11.

---

## 1. Crear cuenta

1. Ve a https://www.alwaysdata.com/en/register/
2. Elige plan **Public — Free**.
3. Anota tu **nombre de cuenta** (lo elegirás tú). Llamémosle `mialias` en el resto de la guía. Te dará automáticamente:
   - Subdominio: `mialias.alwaysdata.net`
   - Host SSH: `ssh-mialias.alwaysdata.net`
   - Host MySQL: `mysql-mialias.alwaysdata.net`
   - Usuario por defecto: `mialias`

Confirma el email y entra al panel: https://admin.alwaysdata.com/

## 2. Crear la base de datos MySQL

En el panel:

1. **Databases → MySQL → Add a new database**
2. Nombre: `mialias_coches`
3. **Add a new user → New user** con permisos sobre esa BD. Anota usuario y contraseña.

Te quedan estos datos:

```
DB_HOST=mysql-mialias.alwaysdata.net
DB_PORT=3306
DB_DATABASE=mialias_coches
DB_USERNAME=mialias_xxxxx
DB_PASSWORD=tu_password
```

## 3. Configurar PHP y el sitio

En el panel:

1. **Sites** → debe haber uno por defecto (`mialias.alwaysdata.net`).
2. Click sobre él → **Edit**.
3. Configura:
   - **Type**: `PHP`
   - **PHP version**: **8.3** (o la más alta disponible)
   - **Path**: `/coches-app/public` (¡importante, apunta a `public/`, no al raíz!)
4. Guardar.

Si no aparece PHP 8.3 en el desplegable, abre un ticket en su soporte (suelen activarla en 24h) o usa lo que haya y comprueba con `php -v` por SSH.

## 4. Conectar por SSH

En tu PC (PowerShell, WSL, Git Bash):

```bash
ssh mialias@ssh-mialias.alwaysdata.net
```

Te pide la contraseña de tu cuenta (la del registro).

> **Opcional (recomendado): SSH key**
> En **panel → Remote access → SSH** puedes pegar tu clave pública. Genera una con `ssh-keygen -t ed25519` si no la tienes.

Al entrar verás algo así:

```
mialias@ssh:~$
```

## 5. Subir el proyecto

Tienes dos opciones, elige la que prefieras:

### Opción A) Git (recomendado si tienes el repo en GitHub)

```bash
cd ~
git clone https://github.com/TU_USUARIO/coches-app.git
cd coches-app
```

### Opción B) SFTP con FileZilla

1. Conexión: protocolo **SFTP**, host `ssh-mialias.alwaysdata.net`, usuario `mialias`, puerto 22.
2. Sube la carpeta `coches-app/` entera a `/home/mialias/` (lo verás como `~/`).
3. **No subas**: `node_modules/`, `vendor/`, `tests/`, `.git/`. Los generaremos en el servidor (o los puedes subir si tu local ya tiene `vendor` listo).

## 6. Instalar dependencias en el servidor

Por SSH dentro de `~/coches-app/`:

```bash
# Verifica PHP y Composer
php -v             # debe ser 8.3.x
which composer || curl -sS https://getcomposer.org/installer | php -- --install-dir=$HOME/bin --filename=composer
export PATH=$HOME/bin:$PATH

# Dependencias PHP de producción
composer install --no-dev --optimize-autoloader --prefer-dist
```

Si no tienes Node en local y necesitas compilar assets (Tailwind/JS), AlwaysData también lo trae:

```bash
node -v            # si responde, está disponible
npm ci
npm run build
rm -rf node_modules    # libera espacio: estos 100MB son sagrados
```

> Si `node` no está disponible globalmente, lo más simple es hacer `npm run build` en tu PC local y subir `public/build/` por SFTP.

## 7. Configurar `.env`

```bash
cp .env.production .env
nano .env
```

Edita estos valores con los datos reales:

```env
APP_NAME="Coches.app"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mialias.alwaysdata.net

DB_CONNECTION=mysql
DB_HOST=mysql-mialias.alwaysdata.net
DB_PORT=3306
DB_DATABASE=mialias_coches
DB_USERNAME=mialias_xxxxx
DB_PASSWORD=tu_password_real
```

> El `APP_KEY` ya viene de ejemplo en `.env.production`. Si quieres uno nuevo, ejecuta `php artisan key:generate`.

Guarda (`Ctrl+O`, `Enter`, `Ctrl+X`).

## 8. Migrar y sembrar la base de datos

```bash
php artisan key:generate          # opcional si ya hay APP_KEY
php artisan migrate:fresh --seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Si todo va bien verás los logs de las migraciones y al final:

```
Total anuncios: 100
```

## 9. Permisos

```bash
chmod -R 775 storage bootstrap/cache
```

(AlwaysData ejecuta PHP con tu usuario, así que no hay líos de `www-data` vs `mialias`.)

## 10. Comprobar

Abre en el navegador:

```
https://mialias.alwaysdata.net
```

Deberías ver la home con los 100 anuncios.

Login:
- **Admin**: `admin@coches.test` / `password`
- **User**: `user@coches.test` / `password`

---

## Actualizaciones futuras

Cuando cambies código en local y subas a GitHub:

```bash
ssh mialias@ssh-mialias.alwaysdata.net
cd coches-app
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force          # solo si hay migraciones nuevas
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Vigilar el disco (100 MB es ajustado)

Cada par de semanas:

```bash
du -sh ~/coches-app/storage/logs/*.log    # los logs crecen
du -sh ~/coches-app                        # total del proyecto
df -h ~                                    # cuánto te queda
```

Si `storage/logs/laravel.log` crece mucho:

```bash
truncate -s 0 ~/coches-app/storage/logs/laravel.log
```

## HTTPS

AlwaysData te da HTTPS automático con Let's Encrypt en el subdominio `mialias.alwaysdata.net` sin configurar nada.

Si más adelante quieres un dominio propio: panel → **Domains → Add** → configurar Nameservers o A record → en **Sites** asocias el dominio al mismo path.

---

## Troubleshooting

| Error | Causa | Solución |
|---|---|---|
| `500 Server Error` al abrir la home | Permisos en `storage/` | `chmod -R 775 storage bootstrap/cache` |
| `SQLSTATE[HY000] [2002]` | Host MySQL mal escrito | Usa exactamente el que da el panel (con `mysql-`) |
| `No application encryption key has been specified` | Falta `APP_KEY` | `php artisan key:generate` |
| Página en blanco / 404 en todas las rutas | Path del sitio mal | Panel → Sites → Path debe ser `/coches-app/public` |
| Imágenes subidas no se muestran | Falta `storage:link` | `php artisan storage:link` |
| Disk quota exceeded | te has pasado de 100 MB | Borra `node_modules`, vacía `storage/logs/*.log` |

---

## Checklist final

- [ ] Cuenta AlwaysData Free creada y email confirmado
- [ ] Base MySQL creada con usuario asignado
- [ ] Site con PHP 8.3 y path `/coches-app/public`
- [ ] SSH funcionando
- [ ] Proyecto en `~/coches-app/`
- [ ] `composer install --no-dev` ejecutado
- [ ] `.env` con datos reales del hosting
- [ ] `migrate:fresh --seed --force` ejecutado sin errores
- [ ] `storage:link` ejecutado
- [ ] `chmod 775` en `storage/` y `bootstrap/cache/`
- [ ] Cachés generadas (config/route/view)
- [ ] Home y login funcionan en `https://mialias.alwaysdata.net`
