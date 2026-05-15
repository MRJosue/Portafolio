# Atelier Digital

Portafolio editorial claro/oscuro con blog/newsletter en Laravel.

## Ruta local

```text
C:\laragon\www\portfolio-signal
```

## Rutas web

- Home: `http://localhost:8010/editorial-black`
- Login: `http://localhost:8010/login`
- Admin local: `http://localhost:8010/admin` protegido por Breeze

## Ejecutar

```bash
php artisan serve --host=127.0.0.1 --port=8010
```

La base de datos usa SQLite en `database/database.sqlite`.

Los assets de Vite se instalan y compilan con pnpm:

```bash
pnpm install
pnpm build
```

## Datos

```bash
php artisan migrate:fresh --seed
```

El seeder mantiene como unico usuario a `ingjosue.cardona@gmail.com`.
La contrasena por defecto es `password123`; puedes cambiarla definiendo `ADMIN_PASSWORD` antes de ejecutar el seed.

## Estructura clave

- `resources/views/themes/editorial-black.blade.php`: vista final del portafolio.
- `public/css/editorial-black.css`: estilo claro/oscuro editorial.
- `public/js/editorial-theme.js`: cambio suave entre modo claro y oscuro.
- `resources/views/admin.blade.php`: panel local para crear posts y ver suscriptores.
- `app/Models/Post.php`: entradas de blog/newsletter.
- `app/Models/Subscriber.php`: suscripciones.
