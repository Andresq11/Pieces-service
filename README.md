# Pieces Service

Este es el servicio que maneja toda la logica de proyectos, bloques y piezas de fabricacion. Expone una API REST protegida que solo responde si el usuario esta autenticado a traves del auth-service. Corre en el puerto 8002.

## Tecnologias

- PHP 8.4.6
- Laravel 13.7.0
- MySQL 8.0
- Composer 2.x

## Requisitos previos

- PHP 8.2 o superior
- Composer
- MySQL
- XAMPP corriendo con Apache y MySQL activos
- El auth-service corriendo en el puerto 8001

## Descripcion del servicio

Este servicio es el segundo de tres que forman la arquitectura de microservicios del proyecto. Se encarga de todo lo relacionado con proyectos, bloques y piezas. No maneja usuarios ni autenticacion, eso lo delega al auth-service.

Cada vez que llega una peticion este servicio verifica el token consultando al auth-service antes de responder. Si el token es valido procesa la peticion, si no devuelve un error 401.

---

## Endpoints principales

Todos los endpoints requieren el header Authorization: Bearer TU_TOKEN

### Proyectos

GET /api/v1/projects - lista todos los proyectos paginados
POST /api/v1/projects - crea un proyecto nuevo
GET /api/v1/projects/{id} - muestra un proyecto
PUT /api/v1/projects/{id} - actualiza un proyecto
DELETE /api/v1/projects/{id} - elimina un proyecto

### Bloques

GET /api/v1/projects/{project_id}/blocks - lista los bloques de un proyecto
POST /api/v1/projects/{project_id}/blocks - crea un bloque
GET /api/v1/projects/{project_id}/blocks/{id} - muestra un bloque
PUT /api/v1/projects/{project_id}/blocks/{id} - actualiza un bloque
DELETE /api/v1/projects/{project_id}/blocks/{id} - elimina un bloque

### Piezas

GET /api/v1/pieces - lista todas las piezas con filtros opcionales por proyecto y estado
GET /api/v1/blocks/{block_id}/pieces - lista las piezas de un bloque
POST /api/v1/blocks/{block_id}/pieces - crea una pieza nueva
GET /api/v1/blocks/{block_id}/pieces/{id} - muestra una pieza
PUT /api/v1/blocks/{block_id}/pieces/{id} - actualiza una pieza
DELETE /api/v1/blocks/{block_id}/pieces/{id} - elimina una pieza

### Reportes

GET /api/v1/reports/pending-by-project - piezas pendientes agrupadas por proyecto
GET /api/v1/reports/totals-by-status - total de piezas por estado

Ejemplo de respuesta de /api/v1/pieces:

```json
{
    "data": [
        {
            "id": 1,
            "block_id": 1,
            "name": "Viga V-01",
            "theoretical_weight": "150.00",
            "real_weight": "148.50",
            "weight_difference": "-1.50",
            "status": "Fabricada",
            "manufactured_at": "2026-05-04T10:30:00.000000Z"
        }
    ],
    "current_page": 1,
    "last_page": 1,
    "total": 1
}
```

---

## Variables de entorno

APP_NAME - nombre de la aplicacion - PiecesService
APP_TIMEZONE - zona horaria - America/Bogota
DB_CONNECTION - motor de base de datos - mysql
DB_HOST - host de MySQL - 127.0.0.1
DB_PORT - puerto de MySQL - 3306
DB_DATABASE - nombre de la base de datos - pieces_service
DB_USERNAME - usuario de MySQL - root
DB_PASSWORD - contrasena de MySQL - vacio si no tiene contrasena
AUTH_DB_HOST - host de la base de datos del auth-service - 127.0.0.1
AUTH_DB_PORT - puerto - 3306
AUTH_DB_DATABASE - base de datos del auth-service - auth_service
AUTH_DB_USERNAME - usuario - root
AUTH_DB_PASSWORD - contrasena - vacio

---

## Pasos de ejecucion

Paso 1 - Clona el repositorio

git clone https://github.com/Andresq11/Pieces-service.git
cd pieces-service

Paso 2 - Instala las dependencias

composer install

Paso 3 - Copia el archivo de configuracion

En Windows: copy .env.example .env
En Mac o Linux: cp .env.example .env

Paso 4 - Genera la clave de la aplicacion

php artisan key:generate

Paso 5 - Configura el .env

APP_NAME=PiecesService
APP_TIMEZONE=America/Bogota
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pieces_service
DB_USERNAME=root
DB_PASSWORD=
AUTH_DB_HOST=127.0.0.1
AUTH_DB_PORT=3306
AUTH_DB_DATABASE=auth_service
AUTH_DB_USERNAME=root
AUTH_DB_PASSWORD=

Paso 6 - Crea la base de datos pieces_service en phpMyAdmin con cotejamiento utf8mb4_general_ci

Paso 7 - Corre las migraciones

php artisan migrate

Esto crea las tablas projects, blocks y pieces en la base de datos pieces_service.

Paso 8 - Levanta el servidor

php artisan serve --port=8002

Asegurate de que el auth-service este corriendo en el puerto 8001 antes de hacer cualquier peticion.

---

## Como funciona el flujo completo

### Como se protegen las rutas

Todas las rutas estan dentro de un grupo protegido por el middleware validate.token en routes/api.php:

```php
Route::middleware('validate.token')->group(function () {
    Route::get('/v1/projects', [ProjectController::class, 'index']);
    Route::post('/v1/projects', [ProjectController::class, 'store']);
    // ... resto de rutas
});
```

Ese middleware esta en app/Http/Middleware/ValidateToken.php y hace lo siguiente:

```php
public function handle(Request $request, Closure $next)
{
    $token = $request->bearerToken();

    if (!$token) {
        return response()->json(['message' => 'Token requerido.'], 401);
    }

    $cacheKey = 'token_' . md5($token);

    $valid = Cache::remember($cacheKey, 60, function () use ($token) {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->get('http://127.0.0.1:8001/api/me');

        return $response->status() === 200;
    });

    if (!$valid) {
        return response()->json(['message' => 'No autorizado.'], 401);
    }

    return $next($request);
}
```

Extrae el token del header, lo guarda en cache por 60 segundos para no consultar al auth-service en cada request, y si el auth-service confirma que es valido deja pasar la peticion al controlador.

### Como se registra una pieza

Cuando el usuario llena el formulario en el frontend y hace clic en registrar, el frontend manda una peticion POST a /api/v1/blocks/{block_id}/pieces. El controlador en app/Http/Controllers/PieceController.php hace esto:

```php
public function store(Request $request, $block_id)
{
    $request->validate([
        'name'               => 'required|string',
        'theoretical_weight' => 'required|numeric',
        'real_weight'        => 'nullable|numeric',
    ]);

    $realWeight        = $request->real_weight;
    $theoreticalWeight = $request->theoretical_weight;
    $difference        = $realWeight ? $realWeight - $theoreticalWeight : null;

    $piece = Piece::create([
        'block_id'           => $block_id,
        'name'               => $request->name,
        'theoretical_weight' => $theoreticalWeight,
        'real_weight'        => $realWeight,
        'weight_difference'  => $difference,
        'status'             => $realWeight ? 'Fabricada' : 'Pendiente',
        'manufactured_at'    => $realWeight ? now() : null,
    ]);

    return response()->json($piece, 201);
}
```

Calcula automaticamente la diferencia entre el peso real y el teorico. Si el usuario ingreso peso real el estado queda como Fabricada y se guarda la fecha y hora actual con now(). Si no ingreso peso real el estado queda como Pendiente.

### Como funcionan las relaciones entre modelos

Un proyecto tiene muchos bloques y un bloque tiene muchas piezas. Eso se define en los modelos asi:

En app/Models/Project.php:

```php
public function blocks()
{
    return $this->hasMany(Block::class);
}
```

En app/Models/Block.php:

```php
public function project()
{
    return $this->belongsTo(Project::class);
}

public function pieces()
{
    return $this->hasMany(Piece::class);
}
```

En app/Models/Piece.php:

```php
public function block()
{
    return $this->belongsTo(Block::class);
}
```

Eso permite hacer consultas como Piece::with('block.project') para traer la pieza con su bloque y el proyecto del bloque en una sola consulta.

---

## Archivos que modifique y por que

### app/Http/Middleware/ValidateToken.php
ubicado en: pieces-service/app/Http/Middleware/ValidateToken.php

Este fue el archivo mas importante que cree. En lugar de compartir la base de datos con el auth-service decidi validar el token haciendo una peticion HTTP al endpoint /api/me del auth-service. Agregue cache de 60 segundos para no hacer una peticion en cada request y mejorar el rendimiento.

### config/app.php y .env
ubicado en: pieces-service/config/app.php

Cambie el timezone a America/Bogota igual que en el auth-service porque las fechas de fabricacion de las piezas aparecian con 5 horas de diferencia con la hora real de Colombia.

### app/Http/Controllers/PieceController.php
ubicado en: pieces-service/app/Http/Controllers/PieceController.php

Agregue el metodo all que devuelve todas las piezas con filtros opcionales por proyecto y estado. Esto lo use en la pagina de lista del frontend para mostrar todas las piezas sin importar el bloque. Cambie el paginate a 100 para que quepan suficientes piezas sin necesidad de paginacion en el frontend.

### config/database.php
ubicado en: pieces-service/config/database.php

Agregue una conexion adicional llamada auth_mysql que apunta a la base de datos del auth-service. Esto lo necesitaba para que Sanctum pudiera validar los tokens contra la base de datos correcta.

### bootstrap/app.php
ubicado en: pieces-service/bootstrap/app.php

Registre el archivo routes/api.php y el middleware validate.token porque en Laravel 13 ninguno de los dos viene configurado por defecto.

---

## Decisiones tecnicas

Decidi validar los tokens consultando al auth-service por HTTP en lugar de compartir la base de datos directamente. Eso respeta mejor el principio de separacion de responsabilidades en microservicios. Cada servicio es dueno de su propia base de datos.

Use cache de 60 segundos en la validacion del token para no hacer una peticion al auth-service en cada request. Sin eso la aplicacion seria muy lenta porque cada peticion tendria que esperar la respuesta del auth-service.

El versionado de la API en /api/v1 lo hice para que en el futuro se puedan agregar cambios sin romper integraciones existentes.

Las migraciones estan separadas por tabla y siguen el orden correcto para respetar las llaves foraneas: primero projects, luego blocks y por ultimo pieces.

Configure el timezone en America/Bogota porque las fechas de fabricacion aparecian con 5 horas de diferencia con la hora real de Colombia.