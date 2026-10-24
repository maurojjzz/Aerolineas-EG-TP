# Volaré

Aplicación web de reserva de vuelos desarrollada como trabajo práctico integrador. Permite buscar vuelos, reservar y pagar pasajes, administrar aerolíneas, vuelos, promociones y novedades, y consultar reportes según el rol del usuario.

- **Sitio en producción:** <https://volare.site.je/>
- **Repositorio:** <https://github.com/maurojjzz/Aerolineas-EG-TP>

## Integrantes

| Integrante | Legajo | email |
| --- | --- | --- |
| _Jimenez Mauro_ | _46828_ | _maurojim123@gmail.com_ |
| _Mayer Rodrigo_ | _36640_ | _rodrigojmayer@gmail.com_ |
| _Mazalán Ariel_ | _52867_ | _arielmazalan11@gmail.com_ |
| _Ribotta Tomas_ | _52309_ | _tomasribotta20@gmail.com_ |

## Descripción

Volaré es una aplicación server-side renderizada: cada página se genera en PHP y se entrega como HTML. No usa framework, Composer ni build de frontend. El objetivo es cubrir el circuito completo de una aerolínea: registro y verificación de usuarios, búsqueda de vuelos, reservas con vencimiento, pago simulado, promociones, novedades y paneles de gestión para CEOs y administradores.

## Tecnologías

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.2 |
| Base de datos | MySQL 8.0 con `mysqli` y consultas preparadas |
| Frontend | HTML5, CSS3 y JavaScript vanilla |
| Estilos | Bootstrap 5.3 y Bootstrap Icons |
| Correo | PHPMailer 6 (SMTP) |
| Imágenes | Cloudinary |
| Servidor | Apache (imagen `php:8.2-apache`) |
| Entorno local | Docker y Docker Compose |

## Funcionalidades

### Público general
- Página de inicio con buscador de vuelos, promociones destacadas y últimas novedades.
- Registro de clientes y solicitud de registro de CEOs.
- Verificación de cuenta y recuperación de contraseña por correo.
- Listado público de novedades y promociones, con detalle de cada una.
- Páginas de ayuda, contacto, términos y privacidad.

### Cliente
- Búsqueda de vuelos por origen, destino, fecha y cantidad de pasajeros, con filtros.
- Reserva de ida y vuelta, selección de promociones aplicables y resumen de precio.
- Pago simulado de la reserva y confirmación.
- Mis reservas, historial de compras y perfil personal.

### CEO de aerolínea
- Panel con métricas de la aerolínea.
- Alta, edición y eliminación de vuelos.
- Creación y edición de promociones, con alcance por aerolínea, ruta o vuelo.
- Reportes de ventas y ocupación.
- Aprobación por parte de un administrador antes de operar.

### Administrador
- ABM de aerolíneas, usuarios y CEOs.
- Aprobación o denegación de solicitudes de CEO y de promociones.
- Gestión de novedades (borrador, publicada, archivada).
- Reportes generales del sistema.
- Notificaciones internas.

## Estructura del proyecto

```text
.
├── index.php                 # Router principal (?pagina=...)
├── docker-compose.yml
├── Dockerfile
├── sql/aerolinea.sql         # Dump de la base de datos
├── public/
│   ├── css/                  # Hojas de estilo
│   ├── js/                   # JavaScript por área
│   └── img/                  # Imágenes e iconos
└── src/
    ├── config/               # app, auth, conexión, políticas, mailer
    ├── controllers/          # Lógica de negocio y consultas SQL
    ├── models/               # DTOs con getters y setters
    ├── routes/               # Endpoints de formularios (?accion=...)
    ├── services/             # EmailService
    ├── data/                 # Aeropuertos
    ├── lib/PHPMailer/        # PHPMailer vendorizado
    └── views/                # Vistas, layouts y componentes
```

El router `index.php` aplica las guardas de `src/config/auth.php` (`requireAuth`, `requireRol`, `soloInvitados`). Las acciones de formulario son endpoints independientes en `src/routes/`, no pasan por el router y responden con redirecciones o JSON.

## Puesta en marcha local con Docker

### Requisitos
- Docker y Docker Compose.
- Puertos `8000` (web) y `3307` (MySQL) libres.

### Pasos

```bash
git clone https://github.com/maurojjzz/Aerolineas-EG-TP.git
cd Aerolineas-EG-TP
cp .env.example .env
# Editar .env con los datos de la base y del correo
docker compose up --build
```

Abrir <http://localhost:8000>.

- MySQL se publica en el host en el puerto **3307**.
- El nombre de la base debe ser **`aerolinea`**: el dump `sql/aerolinea.sql` crea esa base.
- El dump se importa solo la primera vez que se crea el volumen. Si se modifica el SQL:

```bash
docker compose down -v
docker compose up --build
```

### Verificación rápida

```bash
# Estado de los contenedores
docker compose ps

# Sintaxis de un archivo PHP
docker compose exec web php -l src/views/home.php
```

### Usuarios de prueba

El dump académico incluye estas cuentas. La contraseña de las tres es `12345678`.

| Rol | Correo |
| --- | --- |
| Administrador | `admin@admin.admin` |
| CEO | `ceo@ceo.ceo` |
| Cliente | `test@test.test` |

> Son cuentas de demostración para la defensa del TP. En producción hay que cambiarles la contraseña o desactivarlas.

## Variables de entorno

El proyecto lee `.env` con `parse_ini_file()` desde `src/config/{conexion,cloudinary,mailer}.php`. No se usa un paquete de dotenv.

| Variable | Obligatoria | Descripción |
| --- | --- | --- |
| `DB_HOST` | Sí | Host de MySQL. En Compose se fuerza `db`. |
| `DB_USER` | Sí | Usuario de la base. |
| `DB_PASSWORD` | Sí | Contraseña de la base. |
| `DB_NAME` | Sí | Nombre de la base. Debe ser `aerolinea`. |
| `DB_ROOT_PASSWORD` | Solo Docker | Contraseña root de MySQL en Compose. |
| `CLOUDINARY_CLOUD_NAME` | Sí para subir imágenes | Cloud Name de Cloudinary. |
| `CLOUDINARY_API_KEY` | Sí para subir imágenes | API Key. |
| `CLOUDINARY_API_SECRET` | Sí para subir imágenes | API Secret. |
| `MAIL_HOST` | Sí para enviar correo | Servidor SMTP. |
| `MAIL_USER` | Sí para enviar correo | Usuario SMTP. |
| `MAIL_PASS` | Sí para enviar correo | Contraseña o app password. |
| `MAIL_PORT` | Sí para enviar correo | Puerto SMTP (587, 465, etc.). |
| `MAIL_FROM` | Sí para enviar correo | Remitente de los correos. |
| `MAIL_FROM_NAME` | No | Nombre visible del remitente. |
| `MAIL_ENCRYPTION` | No | `starttls` (por defecto), `ssl` o `none`. |
| `MAIL_TIMEOUT` | No | Timeout SMTP en segundos (10 por defecto). |
| `MAIL_CONTACT_TO` | No | Buzón que recibe el formulario de contacto. Si falta, usa `MAIL_FROM`. Admite varias direcciones separadas por coma. |
| `MAIL_REPLY_TO` | No | Reply-To de los correos. Si falta, usa `MAIL_FROM`. |
| `MAIL_HOSTNAME` | No | Nombre para el saludo HELO/EHLO. Si falta, se usa el hostname del servidor. |
| `MAIL_DKIM_DOMAIN` | No | Dominio DKIM. |
| `MAIL_DKIM_SELECTOR` | No | Selector DKIM. |
| `MAIL_DKIM_PRIVATE_KEY` | No | Ruta a la clave privada DKIM. |
| `MAIL_DKIM_PASSPHRASE` | No | Passphrase de la clave DKIM, si tiene. |

Si faltan las claves de correo, la aplicación sigue funcionando: registra el error y no envía el mensaje. La configuración de correo prioriza las variables de entorno reales sobre el archivo `.env` (útil para pruebas locales).

### Correo y entregabilidad

- El formulario de contacto valida los datos en el servidor, exige token CSRF y envía el mensaje por `EmailService`.
- `MAIL_CONTACT_TO` permite separar el buzón de soporte del remitente técnico.
- El `Reply-To` del contacto apunta al visitante para poder responderle directamente.
- DKIM es opcional: si `MAIL_DKIM_DOMAIN`, `MAIL_DKIM_SELECTOR` y `MAIL_DKIM_PRIVATE_KEY` están configurados y la clave es legible, PHPMailer firma el mensaje; si no, el envío continúa sin firma.
- SPF, DKIM y DMARC se configuran en el proveedor del dominio, no en el código. Sin esos registros es probable que los correos caigan en spam.
- Algunos hostings gratuitos bloquean el SMTP saliente. En ese caso hay que usar un proveedor transaccional o el correo del propio hosting.

## Base de datos

El dump `sql/aerolinea.sql` incluye estructura y datos de ejemplo. Las tablas usan nombres en singular (excepto `novedades`) y columnas en español camelCase (`idUsuario`, `fechaHoraSalidaVuelo`).

Estados que se comparan de forma exacta y sensible a mayúsculas:

| Tabla | Valores |
| --- | --- |
| `reserva.estadoReserva` | `pendiente de pago`, `confirmada`, `cancelada` |
| `promocion.estado` | `Pendiente`, `Aprobada`, `Denegada` |
| `novedades.estado` | `borrador`, `publicada`, `archivada` |


Los usuarios nuevos se crean con `emailVerificado=0` y `activo=0`. Los clientes se activan con el enlace de verificación enviado por correo; los CEOs además requieren que un administrador los apruebe. Sin SMTP configurado, para probar localmente hay que actualizar esos campos directamente en la base.

## Flujos principales

1. **Registro y verificación:** el usuario se registra, recibe un correo con un enlace y activa la cuenta. Los CEOs quedan pendientes de aprobación del administrador.
2. **Búsqueda y reserva:** el cliente busca vuelos, elige ida y/o vuelta, selecciona promociones y genera una reserva en estado `pendiente de pago`.
3. **Pago:** el cliente paga desde `Mi panel`; la reserva pasa a `confirmada` y se notifica por correo al cliente y al CEO de la aerolínea.
4. **Vencimiento:** las reservas impagas se cancelan automáticamente después de 3 días y los asientos se liberan.
5. **Promociones:** el CEO crea una promoción, el administrador la aprueba o deniega y se notifica al CEO.
6. **Novedades:** el administrador redacta novedades en borrador, las publica y los clientes las ven en el listado público.


## Validación realizada

- HTML5 de las páginas renderizadas con el validador Nu (vnu.jar): sin errores en los estados relevados.
- CSS de `public/css` con W3C CSS Validator (Jigsaw): sin errores, salvo `pointer-events` y prefijos de proveedor, que el validador no reconoce y se mantienen a propósito.
- Regresión funcional manual: login de los tres roles, navegación por paneles, búsqueda, reserva, pago simulado y validación del formulario de contacto (sin envíos reales de correo).
