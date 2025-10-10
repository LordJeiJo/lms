LMS Pulse v4 — Seguimiento de progreso + UI hiperligera
-------------------------------------------------------
- Seguimiento completo de alumnos por lección (tabla `lesson_progress`).
- Dashboard con progreso global, módulos con barras dinámicas y CTA para continuar.
- Panel de creación con formularios desplegables y estilo "neo-glass" inspirado en ThePowerMBA + EdApp.
- Vista de seguimiento con tarjetas por alumno y progreso por módulo; cambios de rol y borrado (solo admins).
- Vista de lección con botón de completar/pending, métricas del módulo y vídeo embebido.
- Base de datos SQLite con WAL en `data/lms.sqlite` (se crea automáticamente). Usuario inicial admin/admin.

**Instalación rápida**
1. `php -S localhost:8000` dentro de la carpeta.
2. Abre `http://localhost:8000/index.php`.
3. Credenciales iniciales: `admin@example.com` / `admin`.
