# Sistema de Registro de Eventos - Cámara de Comercio Qro

Repositorio: Sistema de registro y gestión de eventos (PHP puro, MVC, MySQL 5.7, Bootstrap tonos verdes).

## Descripción
Aplicación web para crear eventos, publicar landing pública de registro, gestionar asistentes, generar códigos numéricos y QR únicos, validar asistencia y consultar historial de asistentes.

## Características principales
- Dashboard para SuperAdmin y Gestor de Eventos.
- Creación y configuración de eventos (cupos, campos del formulario, página pública).
- Registro público con búsqueda por RFC (empresa) o Teléfono (invitado) y precarga de datos.
- Generación de código único y QR por registro.
- Validación de QR y marcación de asistencia.
- Exportación CSV/PDF, envío de correos y recordatorios.

## Requisitos
- PHP 7.4+ (recomendado PHP 8.x)
- MySQL 5.7
- Composer (opcional, recomendado)
- Servidor web (Apache/Nginx) con HTTPS

## Estructura del proyecto (MVC sugerida)
- /app
  - /Controllers
  - /Models
  - /Views
  - /Helpers
- /public
  - index.php
  - assets/ (css, js, images)
- /config
  - database.php
  - app.php
- /storage
  - logs/
  - uploads/
- /vendor (si se usa composer)

