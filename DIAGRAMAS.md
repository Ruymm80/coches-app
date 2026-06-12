# Diagramas — Coches.app

Diagramas en formato **Mermaid** (renderizables en GitHub, VSCode, o https://mermaid.live).

---

## 1. Diagrama de clases (Modelos + Controladores)

```mermaid
classDiagram
    %% =================== MODELOS ===================
    class User {
        +int id
        +string name
        +string email
        +string password
        +Role role
        +string phone
        +string province
        +string avatar
        +isAdmin() bool
        +unreadMessagesCount() int
        +coches() HasMany
        +favoritos() HasMany
        +chatsAsBuyer() HasMany
        +chatsAsSeller() HasMany
    }

    class Coche {
        +int id
        +int user_id
        +string title
        +string slug
        +string brand
        +string model
        +string description
        +int price
        +int year
        +int mileage_km
        +FuelType fuel_type
        +Transmission transmission
        +BodyType body_type
        +string color
        +string province
        +ListingStatus status
        +bool featured
        +int views_count
        +scopeActive() Builder
        +isFavoritedBy(User) bool
        +user() BelongsTo
        +imagenes() HasMany
        +imagenPrincipal() HasOne
        +chats() HasMany
    }

    class Imagen {
        +int id
        +int coche_id
        +string path
        +int sort_order
        +bool is_primary
        +coche() BelongsTo
        +getUrlAttribute() string
    }

    class Chat {
        +int id
        +int coche_id
        +int buyer_id
        +int seller_id
        +datetime last_message_at
        +coche() BelongsTo
        +buyer() BelongsTo
        +seller() BelongsTo
        +mensajes() HasMany
        +otroParticipante(User) User
        +unreadCountFor(User) int
    }

    class Mensaje {
        +int id
        +int chat_id
        +int sender_id
        +string body
        +datetime read_at
        +chat() BelongsTo
        +sender() BelongsTo
    }

    class Favorito {
        +int id
        +int user_id
        +int coche_id
        +user() BelongsTo
        +coche() BelongsTo
    }

    %% =================== CONTROLADORES ===================
    class AuthController {
        +loginForm() View
        +login(LoginRequest) RedirectResponse
        +registerForm() View
        +register(Request) RedirectResponse
        +logout(Request) RedirectResponse
    }

    class CocheController {
        +home() View
        +index(Request, CocheFilter) View
        +show(Coche) View
        +mine(Request) View
        +create() View
        +store(StoreCocheRequest) RedirectResponse
        +edit(Coche) View
        +update(UpdateCocheRequest, Coche) RedirectResponse
        +destroy(Coche) RedirectResponse
        +markSold(Coche) RedirectResponse
    }

    class ChatController {
        +index(Request) View
        +show(Chat) View
        +start(EnviarMensajeRequest, Coche) RedirectResponse
        +reply(EnviarMensajeRequest, Chat) RedirectResponse
    }

    class AdminController {
        +dashboard() View
        +usersIndex(Request) View
        +userEdit(User) View
        +userUpdate(UpdateUserByAdminRequest, User) RedirectResponse
        +userDestroy(Request, User) RedirectResponse
        +cochesIndex(Request) View
        +cocheUpdateStatus(Request, Coche) RedirectResponse
        +cocheToggleFeatured(Coche) RedirectResponse
        +cocheDestroy(Coche) RedirectResponse
    }

    class PerfilController {
        +dashboard(Request) View
        +edit(Request) View
        +update(ProfileUpdateRequest) RedirectResponse
        +destroy(Request) RedirectResponse
        +favoritos(Request) View
        +toggleFavorito(Request, Coche) RedirectResponse
    }

    %% =================== RELACIONES MODELO ===================
    User "1" --o "0..*" Coche : publica
    User "1" --o "0..*" Favorito : guarda
    User "1" --o "0..*" Chat : participa
    Coche "1" --o "1..*" Imagen : contiene
    Coche "1" --o "0..*" Favorito
    Coche "1" --o "0..*" Chat : asociado
    Chat "1" --o "1..*" Mensaje : contiene
    Favorito "*" --> "1" User
    Favorito "*" --> "1" Coche

    %% =================== RELACIONES CONTROLADOR-MODELO ===================
    AuthController ..> User : autentica/crea
    CocheController ..> Coche : CRUD
    CocheController ..> Imagen : gestiona
    ChatController ..> Chat : CRUD
    ChatController ..> Mensaje : crea
    AdminController ..> User : modera
    AdminController ..> Coche : modera
    PerfilController ..> User : actualiza
    PerfilController ..> Favorito : toggle
```

---

## 2. Diagrama de entidad-relación (BD)

```mermaid
erDiagram
    USERS ||--o{ COCHES : "publica"
    USERS ||--o{ FAVORITOS : "guarda"
    USERS ||--o{ CHATS : "buyer"
    USERS ||--o{ CHATS : "seller"
    USERS ||--o{ MENSAJES : "envia"
    COCHES ||--|{ IMAGENES : "contiene"
    COCHES ||--o{ FAVORITOS : "marcado"
    COCHES ||--o{ CHATS : "asociado"
    CHATS ||--|{ MENSAJES : "contiene"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role
        string phone
        string province
        string avatar
        timestamp email_verified_at
        timestamps created_updated_at
    }

    COCHES {
        bigint id PK
        bigint user_id FK
        string title
        string slug UK
        string brand
        string model
        text description
        int price
        smallint year
        int mileage_km
        string fuel_type
        string transmission
        string body_type
        string color
        string province
        string status
        boolean featured
        int views_count
        timestamps created_updated_at
    }

    IMAGENES {
        bigint id PK
        bigint coche_id FK
        string path
        smallint sort_order
        boolean is_primary
        timestamps created_updated_at
    }

    FAVORITOS {
        bigint id PK
        bigint user_id FK
        bigint coche_id FK
        timestamps created_updated_at
    }

    CHATS {
        bigint id PK
        bigint coche_id FK
        bigint buyer_id FK
        bigint seller_id FK
        timestamp last_message_at
        timestamps created_updated_at
    }

    MENSAJES {
        bigint id PK
        bigint chat_id FK
        bigint sender_id FK
        text body
        timestamp read_at
        timestamps created_updated_at
    }
```

---

## 3. Diagramas de estados

### 3.1 Estados de un Coche (anuncio)

```mermaid
stateDiagram-v2
    [*] --> Draft : usuario crea anuncio
    Draft --> Active : usuario publica
    Active --> Sold : marcar vendido
    Active --> Expired : caducidad / admin
    Active --> Draft : usuario despublica
    Sold --> Active : reactivar (admin)
    Expired --> Active : renovar (admin)
    Draft --> [*] : eliminar
    Active --> [*] : eliminar
    Sold --> [*] : eliminar
    Expired --> [*] : eliminar

    note right of Draft
        Solo visible para el dueño
        y para administradores
    end note

    note right of Active
        Visible en catálogo público
        Aparece en búsquedas/filtros
    end note

    note right of Sold
        Marcado como vendido
        Visible en perfil dueño
        No aparece en búsquedas
    end note
```

### 3.2 Estados de un Chat / Mensaje (lectura)

```mermaid
stateDiagram-v2
    [*] --> Iniciado : comprador envia primer mensaje
    Iniciado --> Activo : vendedor responde
    Iniciado --> AbandonadoSinRespuesta : sin respuesta
    Activo --> Activo : intercambio mensajes
    Activo --> Cerrado : coche vendido / archivado

    state Mensaje {
        [*] --> NoLeido : se crea (read_at = null)
        NoLeido --> Leido : destinatario abre chat
        Leido --> [*]
    }
```

### 3.3 Estados de un Usuario

```mermaid
stateDiagram-v2
    [*] --> NoVerificado : registro
    NoVerificado --> Verificado : confirma email
    Verificado --> Suspendido : admin desactiva
    Suspendido --> Verificado : admin reactiva
    Verificado --> [*] : eliminar cuenta
    Suspendido --> [*] : admin elimina

    state Verificado {
        [*] --> Usuario : rol = user
        Usuario --> Admin : promoción por admin
        Admin --> Usuario : degradación por admin
    }
```

---

## Cómo usar estos diagramas

1. **Mermaid Live**: pega cualquier bloque en https://mermaid.live → exporta a PNG/SVG
2. **GitHub**: GitHub renderiza Mermaid nativo si pegas en un .md
3. **VS Code**: extensión "Markdown Preview Mermaid Support"
4. **Para LaTeX / TFG**: exporta a PNG desde mermaid.live y embebe en tu documento
