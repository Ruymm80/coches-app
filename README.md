# Coches.app — Marketplace de coches de segunda mano

Proyecto DAW. Aplicación Laravel inspirada en Coches.net donde los usuarios publican anuncios de coches, los marcan como vendidos, gestionan favoritos y se comunican entre sí por mensajería interna. Incluye un panel de administración para moderar contenido y usuarios.

## Stack

- **PHP** 8.3+
- **Laravel** 13.x
- **Base de datos**: SQLite (por defecto; sin servidor)
- **Frontend**: Blade + Tailwind CSS v4 + Alpine.js
- **Auth scaffolding**: Laravel Breeze (Blade)
- **Build**: Vite

## Requisitos del sistema

- PHP 8.3 o superior con las extensiones: `pdo_sqlite`, `sqlite3`, `gd`, `mbstring`, `openssl`, `fileinfo`
- Composer 2.x
- Node 18+ con npm

> En Windows, recuerda descomentar `extension=pdo_sqlite`, `extension=sqlite3` y `extension=gd` en tu `php.ini`.

## Instalación

```bash
# 1. Dependencias
composer install
npm install

# 2. Configuración
cp .env.example .env
php artisan key:generate

# 3. Base de datos (SQLite por defecto)
php artisan migrate:fresh --seed

# 4. Storage para imágenes subidas
php artisan storage:link

# 5. Compilar assets
npm run build
```

## Arrancar en desarrollo

```bash
# Servidor PHP
php artisan serve

# En otra terminal, Vite con hot-reload
npm run dev
```

Abre [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Credenciales de prueba

El seeder crea un admin y un usuario de demo, además de 10 usuarios y unos 30 anuncios con imágenes (placeholder vía picsum.photos).

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | `admin@coches.test` | `password` |
| Usuario | `user@coches.test` | `password` |

## Estructura funcional

### Público (sin login)
- `/` — home con destacados y últimos anuncios
- `/coches` — catálogo con filtros (marca, modelo, precio, año, km, combustible, cambio, carrocería, provincia, orden)
- `/coches/{slug}` — ficha del anuncio (galería, datos, vendedor, similares)

### Usuario autenticado (`/mi-cuenta/*`)
- Dashboard con métricas personales
- CRUD de anuncios propios + subida de hasta 8 imágenes
- Marcar anuncio como vendido
- Favoritos (toggle ♥ desde la ficha)
- Mensajería interna con badge de no leídos en la nav

### Admin (`/admin/*`)
- Dashboard con métricas globales
- Gestión de usuarios: buscar, editar, cambiar de rol, eliminar
- Moderación de anuncios: filtrar por estado, cambiar estado, destacar/quitar destacado, eliminar
- Edita cualquier anuncio reusando el formulario del usuario (bypass de policy)

## Modelo de datos

| Tabla | Descripción |
|-------|-------------|
| `users` | Extendida con `role` (user/admin), `phone`, `province`, `avatar` |
| `listings` | Anuncios: marca, modelo, precio, año, km, combustible, cambio, carrocería, color, provincia, estado, destacado |
| `listing_images` | Imágenes ligadas a un anuncio (sort_order + is_primary) |
| `favorites` | Pivot usuario ↔ anuncio (unique compuesto) |
| `conversations` | Una por (anuncio, comprador) |
| `messages` | Mensajes con `read_at` para marcar como leídos |

Enums PHP en `app/Enums/`: `Role`, `FuelType`, `Transmission`, `BodyType`, `ListingStatus`.

## Arquitectura

```
app/
├─ Enums/                   # Backed enums PHP
├─ Http/
│  ├─ Controllers/
│  │  ├─ Admin/             # DashboardController, UserController, ListingController
│  │  ├─ User/              # DashboardController, ListingController, FavoriteController, ConversationController
│  │  ├─ HomeController.php
│  │  └─ ListingController.php   # catálogo público
│  ├─ Middleware/EnsureAdmin.php
│  └─ Requests/             # FormRequests con validación + atributos en español
├─ Models/                  # User, Listing, ListingImage, Favorite, Conversation, Message
├─ Policies/                # ListingPolicy, ConversationPolicy (admin bypass via before())
├─ Services/ImageService.php
├─ Support/ListingFilter.php
└─ Providers/AppServiceProvider.php   # View composer del badge + paginador Tailwind

resources/views/
├─ layouts/{app,navigation,guest}
├─ components/              # listing-card, listing-filters, price-format + Breeze
├─ home.blade.php
├─ listings/{index,show}
├─ account/                 # área de usuario
├─ admin/                   # panel admin
├─ auth/                    # Breeze
├─ profile/                 # Breeze (extendido con phone/province)
└─ errors/                  # 403, 404, 500 personalizados
```

## Tests

Suite completa de Feature tests (52 casos, 122 asserts):

```bash
php artisan test
```

- `UserAreaSmokeTest` — CRUD propio, autorización, marcar vendido, favoritos
- `MessagingTest` — conversaciones, mensajes, autorización, marcado leído, contador no leídos
- `AdminPanelTest` — acceso solo admin, gestión usuarios y moderación anuncios
- Tests de Breeze (login, registro, perfil, password reset, verificación)

## Scripts útiles

```bash
# Resetear DB + reseed
php artisan migrate:fresh --seed

# Listar todas las rutas
php artisan route:list --except-vendor

# Ver logs en tiempo real
php artisan pail
```

## Notas técnicas

- **Slugs únicos** se generan automáticamente al crear/actualizar un anuncio (`Listing::boot()`).
- **Imágenes**: las del seeder son URLs externas (picsum.photos) y se respetan; las subidas reales van a `storage/app/public/listings/{id}/`.
- **last_message_at** se actualiza con un observer en `Message::created` para ordenar la bandeja por actividad reciente.
- **Cuenta de mensajes no leídos** se inyecta en la navegación vía `View::composer`.
- **Tailwind v4** con `@tailwindcss/vite`; los temas y plugins van en `resources/css/app.css`.
