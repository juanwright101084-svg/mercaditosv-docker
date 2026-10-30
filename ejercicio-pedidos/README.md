# Ejercicio Pedidos API

API REST para un sistema de e-commerce básico desarrollada con **Laravel**, que permite gestionar clientes, un catálogo de productos, y procesar compras mediante la pasarela de pago **Stripe**. Incluye autenticación robusta con **JWT** y documentación completa vía **Swagger/OpenAPI**.

## Características

- CRUD completo de productos y categorías (lectura pública, escritura protegida)
- Registro y autenticación de usuarios con JWT (`tymon/jwt-auth`)
- Creación de órdenes con múltiples productos, cálculo automático de totales y control de stock
- Órdenes protegidas por propietario: cada usuario solo puede ver, editar o eliminar sus propias órdenes
- Procesamiento de pagos mediante Stripe (`stripe/stripe-php`)
- Historial de compras por usuario autenticado
- Documentación interactiva con Swagger UI (Auth, Orders, Payments, Products)
- Validaciones mediante Form Requests
- Manejo de errores consistente en formato JSON (401, 404, 422, 402)

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL
- Una cuenta de [Stripe](https://dashboard.stripe.com/register) (modo de prueba)

## Instalación

1. Clona el repositorio:
```bash
   git clone https://github.com/juanwright101084-svg/ejercicio-pedidos-api.git
   cd ejercicio-pedidos-api
```

2. Instala las dependencias:
```bash
   composer install
```

3. Copia el archivo de entorno y genera la clave de aplicación:
```bash
   copy .env.example .env
   php artisan key:generate
```

4. Crea una base de datos MySQL vacía (por ejemplo, `ejercicio_pedidos`) y configura las credenciales en tu `.env`:
```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ejercicio_pedidos
   DB_USERNAME=root
   DB_PASSWORD=
```

5. Genera la clave secreta de JWT:
```bash
   php artisan jwt:secret
```

6. Configura tus claves de Stripe (modo de prueba) en el `.env`:
```dotenv
   STRIPE_KEY=pk_test_tu_clave_publicable
   STRIPE_SECRET=sk_test_tu_clave_secreta
```
   Puedes obtenerlas en [dashboard.stripe.com/test/apikeys](https://dashboard.stripe.com/test/apikeys).

7. Corre las migraciones y los seeders:
```bash
   php artisan migrate
   php artisan db:seed
```
   Esto crea un usuario administrador de prueba, 5 categorías, 10 productos, y 3 clientes de ejemplo.

8. Genera la documentación de Swagger:
```bash
   php artisan l5-swagger:generate
```

9. Levanta el servidor:
```bash
   php artisan serve
```

## Documentación de la API

Una vez el servidor esté corriendo, accede a la documentación interactiva en:

http://127.0.0.1:8000/api/documentation


Desde ahí puedes probar todos los endpoints directamente, incluyendo los protegidos (usando el botón **Authorize** con un token JWT).

## Datos de prueba (creados por el seeder)

**Usuario administrador:**
Email: juan@test.com
Password: password123
Rol: admin


**Clientes de ejemplo** (usa cualquiera de sus `id` al crear una orden):

Cliente Prueba — cliente@test.com
Ana Perez — ana@test.com
Carlos Rodriguez — carlos@test.com


## Flujo de uso típico

1. **Login** — `POST /api/jwt/login` con el usuario de prueba, obtén tu token
2. **Explora el catálogo** — `GET /api/products` (público, no requiere token)
3. **Crea una orden** — `POST /api/orders` (requiere token), usando el `id` de uno de los clientes de ejemplo y un arreglo de `items` con `product_id` y `quantity`
4. **Procesa el pago** — `POST /api/payments` con el `order_id` de la orden creada. Usa `pm_card_visa` como `payment_method` para simular una tarjeta de prueba exitosa
5. **Consulta tu historial** — `GET /api/my-orders` (requiere token)
6. **Consulta tus órdenes** — `GET /api/orders` (requiere token; solo muestra las órdenes creadas por el usuario autenticado)

## Endpoints principales

| Método | Ruta | Descripción | Auth |
|---|---|---|---|
| POST | `/api/register` | Registro de usuario | No |
| POST | `/api/jwt/login` | Login (JWT) | No |
| GET | `/api/jwt/me` | Usuario autenticado actual | Sí |
| POST | `/api/jwt/logout` | Cerrar sesión | Sí |
| GET | `/api/products` | Listado de productos | No |
| GET | `/api/products/{id}` | Detalle de un producto | No |
| POST | `/api/products` | Crear producto | Sí |
| PUT | `/api/products/{id}` | Actualizar producto | Sí |
| DELETE | `/api/products/{id}` | Eliminar producto | Sí |
| GET/POST/PUT/DELETE | `/api/categories` | CRUD de categorías | Mixto |
| GET/POST/PUT/DELETE | `/api/orders` | CRUD de órdenes (solo propias) | Sí |
| POST | `/api/payments` | Procesar pago de una orden | Sí |
| GET | `/api/my-orders` | Historial de compras del usuario | Sí |

## Notas de seguridad

- Las contraseñas se almacenan hasheadas (Bcrypt) mediante el cast nativo de Laravel.
- Los endpoints de escritura (crear, editar, eliminar) requieren un token JWT válido.
- Las órdenes están protegidas por propietario: un usuario no puede ver, editar ni eliminar las órdenes de otro usuario (se devuelve 404, no 403, para no revelar la existencia de la orden ajena).
- El endpoint de pagos calcula el monto a cobrar en el servidor (a partir del total real de la orden), nunca confía en un monto enviado por el cliente.
- Las tarjetas de prueba de Stripe (`pm_card_visa`, entre otras) permiten simular pagos exitosos y rechazados sin usar dinero real.

## Tecnologías

- Laravel
- MySQL
- `tymon/jwt-auth` — autenticación JWT
- `stripe/stripe-php` — procesamiento de pagos
- `darkaonline/l5-swagger` — documentación OpenAPI