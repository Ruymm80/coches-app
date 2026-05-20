# Desplegar en Railway (10 minutos)

## Lo que necesitas

- Cuenta GitHub con el repo `coches-app` subido
- Cuenta en https://railway.com/ (gratis con login GitHub; pide tarjeta para verificar pero no cobra)
- $5 de crédito gratis al mes — para este proyecto sobra (~$1–2/mes de gasto real)

## Antes de empezar: subir los archivos nuevos al repo

Desde tu PC, en la carpeta del proyecto:

```bash
git add Procfile nixpacks.toml DEPLOY-RAILWAY.md
git commit -m "Configuración para Railway"
git push
```

---

## Pasos en la web de Railway

### 1. Crea el proyecto

1. Entra en https://railway.com/new
2. Click **"Deploy from GitHub repo"**
3. Da permisos a Railway para ver tu repo si te lo pide
4. Selecciona **`coches-app`**

Railway empezará a desplegar automáticamente. **Va a fallar la primera vez** porque aún no tiene base de datos ni `.env`. Es normal, no te asustes.

### 2. Añade MySQL

1. Dentro del proyecto, click en el botón **"+ Create"** (arriba a la derecha)
2. **Database → Add MySQL**
3. Se crea un servicio MySQL al lado de tu app.

### 3. Conecta tu app a MySQL (variables de entorno)

1. Click en el servicio **coches-app** (el de tu código, no el de MySQL).
2. Pestaña **Variables**.
3. Click en **+ New Variable** y añade estas, una por una:

| Variable | Valor |
|---|---|
| `APP_KEY` | `base64:NQ6qRchUfzW9A9FDrLGHyIY+XEZmKqSfGO6Vv0POd24=` |
| `APP_NAME` | `Coches.app` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_LOCALE` | `es` |
| `APP_FALLBACK_LOCALE` | `es` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQL_DATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |

> Las `${{MySQL.XXX}}` son **referencias** — Railway las sustituye por los valores reales del servicio MySQL automáticamente. Escríbelas literalmente con las llaves dobles tal cual.

### 4. Expón el puerto público

1. Sigue en el servicio de tu app.
2. Pestaña **Settings** → sección **Networking**.
3. Click en **Generate Domain** → te da una URL tipo `https://coches-app-production-xxxx.up.railway.app`.

### 5. Añade `APP_URL` con esa URL

1. Vuelve a **Variables**.
2. Añade `APP_URL` con el valor de la URL que te acaba de dar Railway (sin `/` al final).

### 6. Redespliega

Click en el botón **Deploy** (arriba a la derecha del servicio) o haz `git push` con cualquier cambio menor. Railway:

1. Instala dependencias (composer + npm)
2. Compila assets (Vite)
3. Cachea config/routes/views
4. Ejecuta `php artisan migrate --force && db:seed --force` al arrancar (lo dice tu `Procfile`)
5. Arranca `php artisan serve`

Mira la pestaña **Deployments → View Logs**. Cuando veas:

```
INFO  Server running on [http://0.0.0.0:xxxx]
```

Está listo.

### 7. Abre tu app

Visita la URL que generaste en el paso 4. Deberías ver la home con los 100 anuncios.

Login de prueba:
- **Admin**: `admin@coches.test` / `password`
- **User**: `user@coches.test` / `password`

---

## Si algo falla

### "Application failed to start" en los logs

Lee los logs (pestaña **Deployments → Logs**). Errores comunes:

| Error en logs | Causa | Solución |
|---|---|---|
| `No application encryption key has been specified` | falta `APP_KEY` | añádela en Variables (paso 3) |
| `SQLSTATE[HY000] [2002] Connection refused` | la app arrancó antes que MySQL | espera 30 s y dale a **Redeploy** |
| `SQLSTATE[HY000] Access denied` | una de las `DB_*` no se sustituyó bien | revisa que tengan `${{MySQL.XXX}}` exacto |
| `Class "PDO" not found` | falta extensión PHP | añade en `nixpacks.toml` el paquete que falte |

### Para reiniciar la base de datos (volver a tener los 100 anuncios)

En **Variables**, cambia temporalmente el comando del Procfile. La forma más rápida:

1. Conéctate al MySQL desde Railway: pestaña del servicio MySQL → **Data** → puedes hacer queries.
2. O cambia `Procfile` a:
   ```
   web: php artisan migrate:fresh --force --seed && php artisan serve --host=0.0.0.0 --port=$PORT
   ```
3. `git push`, esperar, luego volver a dejarlo como estaba (`migrate --force` sin `fresh`) para no perder datos en cada despliegue.

---

## Coste real para tu proyecto

Railway cobra por uso (CPU + RAM + tráfico). Para una app DAW con poco tráfico:

- App PHP: ~$0.5–1.5/mes
- MySQL: ~$0.5–1/mes
- **Total: ~$1–2.5/mes**

Y tienes $5 de crédito gratis cada mes, así que **te cuesta $0**. Si dejas de usarlo, en **Settings → Sleep** puedes activar que se duerma cuando no se usa para gastar todavía menos.

---

## Para apagarlo / eliminarlo

- **Pausar**: Settings → Sleep (se duerme y se despierta al recibir tráfico)
- **Borrar todo**: Settings → Danger → Delete project

---

## Checklist final

- [ ] `Procfile` y `nixpacks.toml` en el repo y subidos
- [ ] Proyecto creado en Railway desde el repo de GitHub
- [ ] Servicio MySQL añadido
- [ ] Las 16 variables de entorno configuradas (paso 3)
- [ ] Dominio público generado y `APP_URL` con esa URL
- [ ] Redeploy ejecutado
- [ ] Home visible con los 100 anuncios
- [ ] Login funciona con `admin@coches.test` / `password`
