# Pieces Service

Servicio encargado de gestionar proyectos, bloques y piezas de fabricacion. Expone una API REST protegida que solo responde si el usuario esta autenticado a traves del auth-service.

## Tecnologias

- PHP 8.4.6
- Laravel 13.7.0
- MySQL 8.0
- Composer 2.x

## Requisitos previos

- PHP 8.2 o superior
- Composer
- MySQL
- XAMPP o cualquier servidor local
- El auth-service corriendo en el puerto 8001

## Instalacion

Clona el repositorio

git clone https://github.com/Andresq11/Pieces-service.git

Entra a la carpeta

cd pieces-service

Instala las dependencias

composer install

Copia el archivo de configuracion

cp .env.example .env

Genera la clave de la aplicacion

php artisan key:generate

Abre el .env y configura la base de datos asi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pieces_service
DB_USERNAME=root
DB_PASSWORD=

Tambien agrega estas variables para conectarse al auth-service

AUTH_DB_HOST=127.0.0.1
AUTH_DB_PORT=3306
AUTH_DB_DATABASE=auth_service
AUTH_DB_USERNAME=root
AUTH_DB_PASSWORD=

Crea la base de datos pieces_service en MySQL o phpMyAdmin

Corre las migraciones

php artisan migrate

Levanta el servidor

php artisan serve --port=8002

El servicio queda corriendo en http://127.0.0.1:8002

## Endpoints

Todos los endpoints requieren el header Authorization: Bearer TU_TOKEN

Proyectos

GET /api/v1/projects - Lista todos los proyectos
POST /api/v1/projects - Crea un proyecto nuevo
GET /api/v1/projects/{id} - Muestra un proyecto
PUT /api/v1/projects/{id} - Actualiza un proyecto
DELETE /api/v1/projects/{id} - Elimina un proyecto

Bloques

GET /api/v1/projects/{project_id}/blocks - Lista los bloques de un proyecto
POST /api/v1/projects/{project_id}/blocks - Crea un bloque
GET /api/v1/projects/{project_id}/blocks/{id} - Muestra un bloque
PUT /api/v1/projects/{project_id}/blocks/{id} - Actualiza un bloque
DELETE /api/v1/projects/{project_id}/blocks/{id} - Elimina un bloque

Piezas

GET /api/v1/pieces - Lista todas las piezas con filtros opcionales
GET /api/v1/blocks/{block_id}/pieces - Lista las piezas de un bloque
POST /api/v1/blocks/{block_id}/pieces - Crea una pieza
GET /api/v1/blocks/{block_id}/pieces/{id} - Muestra una pieza
PUT /api/v1/blocks/{block_id}/pieces/{id} - Actualiza una pieza
DELETE /api/v1/blocks/{block_id}/pieces/{id} - Elimina una pieza

Reportes

GET /api/v1/reports/pending-by-project - Piezas pendientes agrupadas por proyecto
GET /api/v1/reports/totals-by-status - Total de piezas por estado

## Como funciona la autenticacion

Este servicio no maneja usuarios ni tokens propios. Cada vez que llega una peticion, el middleware ValidateToken consulta al auth-service en /api/me con el token recibido. Si el auth-service confirma que el token es valido, la peticion sigue adelante. Si no, responde con un error 401.

Para mejorar el rendimiento, el resultado de esa validacion se guarda en cache por 60 segundos, asi no se hace una peticion al auth-service en cada request.

## Variables de entorno

APP_NAME - Nombre de la app - PiecesService
APP_URL - URL de la app - http://localhost:8002
DB_CONNECTION - Motor de base de datos - mysql
DB_HOST - Host de la base de datos - 127.0.0.1
DB_PORT - Puerto de MySQL - 3306
DB_DATABASE - Nombre de la base de datos - pieces_service
DB_USERNAME - Usuario de MySQL - root
DB_PASSWORD - Contrasena de MySQL - vacio por defecto
AUTH_DB_DATABASE - Base de datos del auth-service - auth_service

## Decisiones tecnicas

Se decidio validar los tokens consultando al auth-service por HTTP en lugar de compartir la base de datos directamente, porque eso respeta mejor el principio de separacion de responsabilidades en microservicios.

Se implemento paginacion en los listados para evitar traer demasiados registros de una sola vez.

El versionado de la API en /api/v1 se hizo para que en el futuro se puedan agregar cambios sin romper integraciones existentes.

Las migraciones estan separadas por tabla y siguen el orden correcto para respetar las llaves foraneas: primero projects, luego blocks y por ultimo pieces.