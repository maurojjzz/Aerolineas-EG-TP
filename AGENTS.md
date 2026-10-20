# AGENTS.md

Volaré is a server-rendered airline booking app: PHP 8.2 + MySQL 8, no framework, no Composer/npm build, no automated tests or CI. UI copy, comments, and flash messages are Spanish (Argentine); keep new user-facing text in Spanish.

## Run and verify

- `cp .env.example .env` and fill it. Add `MAIL_HOST`, `MAIL_USER`, `MAIL_PASS`, `MAIL_PORT`, `MAIL_FROM`, `MAIL_FROM_NAME`; PHPMailer requires them but `.env.example` omits them.
- `docker compose up --build` → http://localhost:8000. MySQL is published on host port `3307`.
- Config is read from `../../.env` with `parse_ini_file()` in `src/config/{conexion,cloudinary,mailer}.php`, not a dotenv package. `DB_*` can also come from real env vars (Compose sets `DB_HOST=db`); Mail and Cloudinary only read the file.
- Keep `DB_NAME=aerolinea`: `sql/aerolinea.sql` hardcodes `CREATE DATABASE aerolinea`.
- The dump is imported only when the MySQL volume is first created. After editing `sql/aerolinea.sql`, run `docker compose down -v && docker compose up --build` (destroys local data).
- `conexion.php` has no `DB_PORT`; host PHP cannot reach the Compose DB on `3307`. Run the app through Compose, or publish MySQL on `3306` for host PHP.
- If `php` is not available locally (it is not in this environment), syntax-check in the container: `docker compose exec web php -l path/to/file.php`.
- There is no test suite, linter, or CI; verify changes manually in the browser.

## Request flow and structure

- Main router: `index.php?pagina=...` switches to a view in `src/views/` and applies guards from `src/config/auth.php` (`requireAuth`, `requireRol`, `soloInvitados`).
- Form actions are standalone endpoints: `src/routes/<area>.php?accion=...`. Most include `src/config/app.php` and `conexion.php`, instantiate a controller with the global `$link`, then redirect or emit JSON. They are not routed through `index.php`.
- `src/config/conexion.php` creates the global `$link` mysqli connection as a side effect. SQL uses procedural mysqli prepared statements and lives in controllers (and a few views); `src/models/` are plain DTOs with getters/setters, not Active Record.
- No autoloader or namespaces (except vendored PHPMailer). Add explicit `require_once`. PHPMailer lives in `src/lib/PHPMailer`, so don't run Composer.
- User feedback goes through `flash_set()` / `flash_get()` and `src/views/components/alertToast.php`; controllers normally redirect after POST.
- Admin areas use layouts that switch on `$_GET['seccion']` (e.g. `src/views/admin/aerolineaLayout.php`). Adding an admin subpage requires a case in `index.php` and an entry in that layout's `$vistas` map.
- Role areas: admin `aerolinea`, `promociones`, `novedades`, `usuarios`; CEO `ceo` (`seccion=home|promociones|promocion-detalle`), `vuelos`, `vuelo`, `promociones`; client `cliente`, `cliente-novedades`, `cliente-novedad-detalle`, `buscar-vuelos`, `reservar`, `mis-reservas`, `pago`.
- Always build internal links/assets with `url('...')` and navigate with `redirect('index.php?...')`. `APP_BASE` is auto-detected from `DOCUMENT_ROOT`, supporting both `/` (Docker) and `/aerolinea` (XAMPP); `APP_BASE_URL` in `.env.example` is unused.
- Roles are `cliente`, `ceo`, `admin`. `requireRol()` protects entrypoints; `src/config/policies.php` has airline ownership rules. CEO queries are scoped by `$_SESSION['usuario']['idAerolinea']`.
- CSRF token is `$_SESSION['csrf_token']`. Include a hidden `csrf_token` and validate with `hash_equals()` in state-changing handlers. Coverage is inconsistent (`src/routes/aerolinea.php` has none), so copy a route that does it.

## Data and behavior gotchas

- Table names are mostly singular, but the news table is `novedades`; columns are Spanish camelCase (`idUsuario`, `fechaHoraSalidaVuelo`).
- Status values are exact and case-sensitive: reserva `pendiente de pago` / `confirmada` / `cancelada`; promocion `Pendiente` / `Aprobada` / `Denegada`; novedades `borrador` / `publicada` / `archivada`.
- New users start with `emailVerificado=0` (and `activo=0`). Clients are activated by the emailed verification link; CEOs also require an admin to set `activo=1`. Without SMTP, update those columns directly for local testing.
- Time zone is hardcoded to `America/Argentina/Buenos_Aires` in PHP. In `docker-compose.yml`, `command: --default-time-zone=...` is nested under `environment:`, so that flag is not applied; don't assume DB `NOW()` matches PHP.
- Some views/JS are dead or duplicated. For example, the live profile is `src/views/usuarioPerfil.php`; `src/views/cliente/perfil.php` is not wired and its `editarPerfilCliente` action does not exist. Trace includes before editing.
