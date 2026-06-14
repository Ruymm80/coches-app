# Despliegue en Oracle Cloud Free Tier

Guía paso a paso. Oracle Cloud regala una VM ARM (Ampere A1) con hasta **4 vCPU y 24 GB RAM gratis para siempre**, sin caducidad. Tarjeta de crédito obligatoria solo para verificar (no cobra nada del tier Always Free).

> **Heads-up honesto**: Oracle a veces dice "Out of capacity" al crear la VM ARM (mucha demanda). Si te pasa, reintenta cada pocas horas o cambia de región (`Frankfurt`, `Madrid`, `London`).

---

## 0. Lo que vas a tener al terminar

- Dominio público tipo `http://152.67.xxx.xxx`
- VM Ubuntu 22.04 con Nginx + PHP 8.4 + MySQL
- App accesible 24/7 gratis
- Tiempo de setup: **30-45 minutos** la primera vez

---

## 1. Crear cuenta Oracle Cloud

1. Ve a https://signup.oraclecloud.com/
2. Rellena email, país (España), nombre completo
3. Verifica email
4. **Datos de pago**: tarjeta de crédito (NO se cobra nada en Always Free) o débito que admita preautorizaciones de 1€
5. Elige una **Home Region** cercana. Recomendadas: `Spain Central (Madrid)`, `Germany Central (Frankfurt)`, `UK South (London)`
6. Espera confirmación (~5-10 min)

> Una vez dentro, te llevarán al panel: https://cloud.oracle.com/

---

## 2. Generar par de claves SSH (en tu PC)

En PowerShell:

```powershell
ssh-keygen -t ed25519 -f $HOME\.ssh\oracle_carros -C "oracle-carros"
# Pulsa Enter cuando pregunte por passphrase (sin passphrase, más simple)
```

Esto crea:
- `~/.ssh/oracle_carros` (privada, NO compartir)
- `~/.ssh/oracle_carros.pub` (pública, la subirás a Oracle)

Ver el contenido de la pública:
```powershell
type $HOME\.ssh\oracle_carros.pub
```

Copia esa línea entera (empieza por `ssh-ed25519 AAAA...`).

---

## 3. Crear la VM

Desde el panel de Oracle Cloud:

1. **Menú hamburguesa (☰) arriba izda → Compute → Instances**
2. Click **Create instance**
3. Rellena:

| Campo | Valor |
|---|---|
| **Name** | `carros-net-vm` |
| **Compartment** | (déjalo en root, el por defecto) |
| **Placement → Availability domain** | el primero que aparezca |
| **Image and shape → Image** | click **Edit** → **Change image** → **Ubuntu** → **Canonical Ubuntu 22.04** |
| **Image and shape → Shape** | click **Edit** → **Change shape** → tipo **Ampere** → **VM.Standard.A1.Flex** → ajusta a **2 OCPU, 12 GB memory** (sobra para el proyecto y deja la mitad libre por si quieres otra) |
| **Networking** | déjalo crear una VCN nueva y subnet automáticamente. Marca **Assign public IPv4 address** |
| **SSH keys** | selecciona **Paste public keys** → pega el contenido de `oracle_carros.pub` |
| **Storage** | déjalo en defaults (47 GB) |

4. Click **Create**
5. Espera 1-2 minutos. Cuando el estado pase de `PROVISIONING` a `RUNNING`, anota la **Public IP Address** (aparece a la derecha).

> Si te dice **"Out of capacity for shape VM.Standard.A1.Flex"**, reintenta más tarde o usa **VM.Standard.E2.1.Micro** (AMD, gratis pero más pequeña: 1 vCPU, 1 GB RAM — suficiente pero justo).

---

## 4. Abrir puertos 80 y 443 (firewall de Oracle)

Oracle bloquea por defecto todos los puertos excepto el 22 (SSH). Hay que abrir HTTP y HTTPS.

1. En el panel de la VM → pestaña **Networking** → click sobre la **Virtual cloud network** (VCN) listada
2. En la VCN → **Security Lists** → click sobre **Default Security List**
3. **Add Ingress Rules** → añade dos reglas:

| Source CIDR | IP Protocol | Destination Port Range | Description |
|---|---|---|---|
| `0.0.0.0/0` | TCP | `80` | HTTP |
| `0.0.0.0/0` | TCP | `443` | HTTPS |

4. **Add Ingress Rules** (guardar)

---

## 5. Conectar por SSH

```powershell
ssh -i $HOME\.ssh\oracle_carros ubuntu@<TU_PUBLIC_IP>
```

Te pide confirmar el fingerprint la primera vez (escribe `yes`). Entras en `ubuntu@carros-net-vm:~$`.

---

## 6. Configurar el firewall LOCAL (iptables)

Ubuntu en Oracle Cloud trae iptables muy restrictivo. Hay que abrir 80 y 443 también aquí:

```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save
```

---

## 7. Instalar el stack completo

Copia este bloque entero (PHP 8.4 desde el PPA de Ondrej):

```bash
# Sistema base
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl unzip git nginx mariadb-server

# PHP 8.4
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-zip php8.4-mysql php8.4-gd php8.4-bcmath

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20 (para Vite)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verifica versiones
php -v && composer -V && node -v && nginx -v && mysql -V
```

---

## 8. Configurar MySQL

```bash
sudo mysql_secure_installation
```

Acepta defaults (puedes pulsar Enter en casi todo). Pon una password root cuando te pregunte.

Crea la BD y el usuario:

```bash
sudo mysql -u root -p
```

Dentro del prompt de MySQL:

```sql
CREATE DATABASE carros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'carros'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_PASSWORD';
GRANT ALL PRIVILEGES ON carros.* TO 'carros'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Aumenta `max_allowed_packet` (las imágenes BLOB necesitan más de 1 MB):

```bash
sudo nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Busca la sección `[mysqld]` y añade (o cambia si existe):

```ini
max_allowed_packet = 16M
```

Guarda (Ctrl+O, Enter, Ctrl+X) y reinicia MySQL:

```bash
sudo systemctl restart mariadb
```

---

## 9. Clonar y configurar el proyecto

```bash
cd /var/www
sudo git clone https://github.com/Ruymm80/coches-app.git
sudo chown -R ubuntu:www-data coches-app
cd coches-app
```

Instala dependencias:

```bash
composer install --no-dev --optimize-autoloader --prefer-dist
npm ci
npm run build
```

Configura `.env`:

```bash
cp .env.production .env
nano .env
```

Edita estos valores:

```env
APP_NAME="Carros.net"
APP_ENV=production
APP_KEY=base64:NQ6qRchUfzW9A9FDrLGHyIY+XEZmKqSfGO6Vv0POd24=
APP_DEBUG=false
APP_URL=http://<TU_PUBLIC_IP>

APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carros
DB_USERNAME=carros
DB_PASSWORD=la_password_que_pusiste

PIXABAY_API_KEY=
```

Genera APP_KEY nuevo (opcional, por seguridad):

```bash
php artisan key:generate
```

Migrate + seed (las imágenes Pixabay/loremflickr tardan 1-3 min):

```bash
php artisan migrate:fresh --seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 10. Permisos

```bash
sudo chown -R ubuntu:www-data /var/www/coches-app
sudo find /var/www/coches-app -type f -exec chmod 644 {} \;
sudo find /var/www/coches-app -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

---

## 11. Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/carros
```

Pega:

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/coches-app/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activa:

```bash
sudo ln -s /etc/nginx/sites-available/carros /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t                 # test sintaxis
sudo systemctl reload nginx
sudo systemctl enable nginx php8.4-fpm
```

---

## 12. Probar

Abre en el navegador: **`http://<TU_PUBLIC_IP>`**

Login:
- Admin: `admin@coches.test` / `password`
- User: `user@coches.test` / `password`

---

## 13. (Opcional) HTTPS con Let's Encrypt

Solo si tienes un dominio apuntando a la IP. Para una IP pelada no funciona.

Si tienes dominio:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tudominio.com
```

Certbot edita el Nginx y renueva el cert solo cada 60 días.

---

## Actualizaciones futuras

Cuando hagas cambios en local y subas a GitHub:

```bash
ssh -i ~/.ssh/oracle_carros ubuntu@<TU_IP>
cd /var/www/coches-app
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force        # solo si hay migraciones nuevas
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.4-fpm
```

---

## Troubleshooting

| Error | Causa | Solución |
|---|---|---|
| No carga la web (timeout) | Firewall cloud O local cerrado | Repasa pasos 4 y 6 |
| `502 Bad Gateway` | PHP-FPM no escucha en socket | `sudo systemctl status php8.4-fpm` |
| `500 Server Error` | Permisos | `sudo chmod -R 775 storage bootstrap/cache` |
| `SQLSTATE[HY000] [1045] Access denied` | password mal en `.env` | Repasa `.env` |
| `PDOException Packet too large` | max_allowed_packet pequeño | Paso 8, sube a 16M, reinicia |
| Imágenes BLOB no cargan | `APP_URL` mal | Debe ser `http://<TU_IP>` exacto |
| Vite manifest not found | falta `npm run build` | corre `npm run build` |

Ver logs en tiempo real:

```bash
sudo tail -f /var/log/nginx/error.log /var/log/php8.4-fpm.log
```

---

## Checklist final

- [ ] Cuenta Oracle Cloud creada y verificada
- [ ] VM ARM A1 (o E2.1.Micro) corriendo
- [ ] SSH key pegada al crear la VM
- [ ] Puertos 80/443 abiertos en Security List (cloud)
- [ ] Puertos 80/443 abiertos en iptables (local)
- [ ] PHP 8.4 + Composer + Node + MariaDB instalados
- [ ] BD `carros` creada con usuario `carros`
- [ ] `max_allowed_packet = 16M` en MariaDB
- [ ] Proyecto clonado en `/var/www/coches-app`
- [ ] `composer install --no-dev` y `npm run build` ejecutados
- [ ] `.env` configurado con `APP_URL=http://<IP>` y credenciales BD
- [ ] `migrate:fresh --seed --force` ejecutado
- [ ] Cachés Laravel generadas
- [ ] Permisos 775 en `storage/` y `bootstrap/cache/`
- [ ] Nginx vhost activo, `nginx -t` OK
- [ ] App accesible en `http://<IP>` y login funciona

---

## Ventajas vs Azure / Railway

| Aspecto | Oracle | Azure free | Railway |
|---|---|---|---|
| Gratis para siempre | ✅ | 12 meses | ⚠ $5/mes crédito |
| Sin tarjeta requerida | ❌ | ❌ | ❌ |
| Recursos máximos free | **4 vCPU, 24 GB RAM** | 1 vCPU, 1 GB RAM | depende uso |
| Necesita conocer Linux | sí | sí | no |
| Tiempo setup | 30-45 min | 30-45 min | 10 min |

Para un proyecto que dejarás corriendo permanentemente, Oracle es la mejor opción gratis del mercado.
