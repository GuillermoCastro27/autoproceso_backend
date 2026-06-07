# SIGEA v5 — Criterios de Evaluación del Proyecto

> **Sistema:** SIGEA v5 — Sistema Integral de Gestión Empresarial Académica  
> **Stack:** Laravel 13 · PHP 8.5 · PostgreSQL · Tabler v1.4.0 (Bootstrap 5)  
> **Última actualización:** 2026-05-23

---

## Resumen de Estado General

| # | Criterio | Estado | Logrado / Total |
|---|----------|--------|-----------------|
| 1 | Login / Seguridad / UX | ✅ Completo | 10 / 10 |
| 2 | Menú de Navegación | ✅ Completo | 9 / 10 (1 no aplica) |
| 3 | Base de Datos | ⏳ Pendiente | 0 / 10 |
| 4 | Vistas SQL | ⏳ Pendiente | 0 / 9 |
| 5 | Procedimientos Almacenados | ⏳ Pendiente | 0 / 10 |
| 6 | Triggers | ⏳ Pendiente | 0 / 10 |
| 7 | Ventanas / Módulos de Interfaz | ✅ Completo | 10 / 10 |
| 8 | Seguridad del Sistema | ⚠ Parcial | 5 logrados · 3 parciales · 2 pendientes |
| 9 | Auditoría de Transacciones | ⚠ Parcial | 2 logrados · 7 parciales · 1 pendiente |
| 10 | Manual de Usuario | ⏳ Pendiente | 0 / 10 |

---

## Criterios 1 — Login / Seguridad / UX

> **Estado general: ✅ Completo — 10/10 logrados**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Simplicidad** | ✅ Logrado | Interfaz con Tabler (Bootstrap 5), diseño minimalista, campos con labels claras en español, sin elementos innecesarios. Flujo de login, recuperación y restablecimiento de contraseña en vistas separadas y simples. |
| 2 | **Seguridad de Contraseña** | ✅ Logrado | Requisitos de complejidad con indicador visual en reset-password (longitud, mayúscula, número, símbolo). Bloqueo a 3 intentos fallidos (`Limit::perMinutes(15, 3)`). Registro de intentos en `LoginAttempt`. Alertas por correo a admins. Contraseñas con bcrypt. 2FA con Jetstream `TwoFactorAuthenticatable`. |
| 3 | **Validación en Tiempo Real** | ✅ Logrado | JS en `blur`/`input` en las 3 vistas de auth. Valida formato de email, contraseña requerida, indicador de requisitos en reset-password y coincidencia de confirmación. |
| 4 | **Tiempo de Carga y Respuesta** | ✅ Logrado | Assets auto-hospedados (sin CDN), Vite para compilación, sin bloqueos perceptibles. |
| 5 | **Recuperación de Contraseña** | ✅ Logrado | Flujo completo: forgot-password (envío de enlace) y reset-password. Errores específicos por caso. Tokens con expiración de 60 minutos. |
| 6 | **Manejo de Errores** | ✅ Logrado | Mensajes diferenciados: credenciales incorrectas vs. validación de campo, enlace expirado con link a nueva solicitud. Páginas de error personalizadas (403, 404, 500, 503). |
| 7 | **Accesibilidad** | ✅ Logrado | Skip link, landmark `<main>`, `aria-describedby` en inputs, `aria-live="polite"` en feedback, `aria-invalid="true"`, `aria-hidden="true"` en íconos decorativos. |
| 8 | **Compatibilidad con Múltiples Dispositivos y Navegadores** | ✅ Logrado | Bootstrap 5 responsive. Probado en Chrome, Firefox, Edge, Opera, Brave, Vivaldi. Cubre PC, tablet y móvil. |
| 9 | **Personalización del Login** | ✅ Logrado | Selector de idioma con bandera de Paraguay (`py.svg`) en login y header. Toggle dark/light mode persistido en `localStorage`. Checkbox "Recordarme". |
| 10 | **Integración con Servicios Externos** | ✅ Logrado *(solo producción)* | Google Sign-In con Laravel Socialite. Botón con logo SVG oficial. Usuarios nuevos reciben rol "Invitado" automáticamente. No disponible en desarrollo local (Google OAuth rechaza dominios `.test`). |

---

## Criterios 2 — Menú de Navegación

> **Estado general: ✅ Completo — 9/10 logrados · 1 no aplica**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Organización y Estructura** | ✅ Logrado | Menú lateral con 5 módulos principales + Dashboard. Jerarquía de dos niveles con colapso/expansión. Íconos SVG únicos por módulo. |
| 2 | **Accesibilidad por Roles** | ✅ Logrado | Menú dinámico con `@can('modulo.x')`. Control por pestaña con `@can('tab.x.y')`. Spatie Permission con guard `sanctum`. Permisos granulares en `config/modulos.php`. |
| 3 | **Claridad y Descripción de las Opciones** | ✅ Logrado | Etiquetas descriptivas en español, íconos únicos, buscador en el header con índice de menú y pestañas. |
| 4 | **Facilidad de Navegación** | ✅ Logrado | Buscador en tiempo real. Máximo 2 clics para cualquier ventana. Estado activo marcado automáticamente. Secciones se expanden al navegar. |
| 5 | **Consistencia de Diseño** | ✅ Logrado | Tabler v1.4.0 uniforme en todo el sistema. Mismos colores, fuente Inter y patrones en todas las secciones. |
| 6 | **Tiempo de Respuesta** | ✅ Logrado | Assets auto-hospedados, navegación por rutas Laravel estándar, respuesta inmediata. |
| 7 | **Adaptabilidad a Diferentes Dispositivos** | ✅ Logrado | Sidebar colapsable con hamburguesa. Bootstrap 5 responsive. Mobile-first. |
| 8 | **Personalización** | — No aplica | En un ERP de uso interno el menú ya está personalizado por rol (cada usuario ve solo su módulo). Implementar reordenamiento generaría inconsistencias en soporte y capacitación. |
| 9 | **Manejo de Errores** | ✅ Logrado | Páginas personalizadas para 403, 404, 500 y 503. Rutas sin permiso no se muestran (prevención proactiva). Middleware `can:` protege a nivel de servidor. |
| 10 | **Actualización y Escalabilidad** | ✅ Logrado | `config/modulos.php` centraliza módulos, tabs y acciones. Seeder idempotente (`firstOrCreate`). Agregar módulo = editar config + re-ejecutar seeder. |

---

## Criterios 3 — Base de Datos

> **Estado general: ⏳ Pendiente — BD aún no completamente implementada**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Auditoría** | ⏳ Pendiente | Cada tabla debe tener campos de auditoría (usuario, fecha/hora de creación/modificación) o tablas de auditoría por módulo. |
| 2 | **Borrado lógico y físico** | ⏳ Pendiente | Campo `deleted_at` (soft delete) en tablas. Borrado físico en cascada respetando integridad referencial. |
| 3 | **Integridad referencial** | ⏳ Pendiente | Claves foráneas definidas en la BD. No eliminar constraints. Validar FK antes de borrar registros padre. |
| 4 | **Tipos de atributos** | ⏳ Pendiente | Definir correctamente `integer`, `numeric`, `varchar`, `text`, `boolean`, `date`, `timestamp` según el relevamiento de documentos. |
| 5 | **Creación de tablas / DER** | ⏳ Pendiente | Toda tabla nueva debe reflejarse en el Diagrama Entidad-Relación (DER) actualizado. |
| 6 | **Procedures, triggers y vistas** | ⏳ Pendiente | Implementar procedimientos almacenados, triggers y vistas en PostgreSQL. |
| 7 | **Duplicación de tablas** | ⏳ Pendiente | Normalizar: evitar tablas con nombres distintos que guardan la misma información. |
| 8 | **Campos vacíos** | ⏳ Pendiente | No crear atributos que nunca contendrán datos. Revisar cada campo antes de agregar. |
| 9 | **Copia de seguridad** | ⏳ Pendiente | Sistema de backup automático de PostgreSQL (pg_dump programado o herramienta equivalente). |
| 10 | **Acceso a la BD en red** | ⏳ Pendiente | Configurar PostgreSQL para conexiones desde la red. Ajustar `pg_hba.conf` y `postgresql.conf`. |

---

## Criterios 4 — Vistas SQL

> **Estado general: ⏳ Pendiente — vistas SQL aún no implementadas**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Legibilidad** | ⏳ Pendiente | Alias descriptivos, indentación coherente, comentarios en el código SQL. |
| 2 | **Optimización de Consultas** | ⏳ Pendiente | Uso efectivo de índices, evitar subconsultas innecesarias, joins optimizados. |
| 3 | **Exactitud y Confiabilidad** | ⏳ Pendiente | Validar que la vista devuelva datos precisos bajo diferentes condiciones. |
| 4 | **Manejo de Datos Grandes** | ⏳ Pendiente | Evaluar rendimiento con grandes volúmenes. Usar particionamiento si es necesario. |
| 5 | **Seguridad y Control de Acceso** | ⏳ Pendiente | Restricciones de acceso por roles a nivel de BD. Vistas de seguridad para limitar exposición de datos sensibles. |
| 6 | **Compatibilidad con otras Vistas** | ⏳ Pendiente | Convenciones de nombres consistentes, compatibilidad con subconsultas existentes. |
| 7 | **Impacto en Rendimiento General** | ⏳ Pendiente | Monitorear CPU/memoria antes y después de implementar la vista. |
| 8 | **Actualización y Mantenimiento** | ⏳ Pendiente | Diseño flexible que permita modificar la vista sin afectar otros elementos. |
| 9 | **Documentación** | ⏳ Pendiente | Descripción clara del propósito, lógica y dependencias de cada vista. |

---

## Criterios 5 — Procedimientos Almacenados

> **Estado general: ⏳ Pendiente — stored procedures aún no implementados**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Legibilidad** | ⏳ Pendiente | Buena indentación, nombres descriptivos, comentarios que aclaren la lógica. |
| 2 | **Eficiencia y Optimización** | ⏳ Pendiente | Uso adecuado de índices, evitar bucles innecesarios, minimizar accesos redundantes. |
| 3 | **Seguridad y Control de Acceso** | ⏳ Pendiente | Permisos adecuados, manejo seguro de datos sensibles, prevención de SQL injection. |
| 4 | **Manejo de Errores** | ⏳ Pendiente | Bloques `EXCEPTION`, registro de errores en logs, mensajes informativos. |
| 5 | **Uso Eficiente de Parámetros** | ⏳ Pendiente | Uso correcto de `IN`, `OUT`, `INOUT`; valores predeterminados adecuados. |
| 6 | **Modularidad y Reutilización** | ⏳ Pendiente | Dividir lógica en procedures más pequeños, evitar redundancia. |
| 7 | **Documentación** | ⏳ Pendiente | Comentarios sobre el propósito, parámetros y posibles excepciones de cada procedure. |
| 8 | **Escalabilidad** | ⏳ Pendiente | Pruebas de estrés y carga, capacidad para manejar grandes volúmenes. |
| 9 | **Compatibilidad con Transacciones** | ⏳ Pendiente | Uso correcto de `BEGIN`, `COMMIT` y `ROLLBACK` para garantizar consistencia. |
| 10 | **Pruebas de Funcionalidad** | ⏳ Pendiente | Pruebas con diferentes escenarios, validación de resultados, monitoreo de tiempos. |

---

## Criterios 6 — Triggers

> **Estado general: ⏳ Pendiente — triggers aún no implementados**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Legibilidad** | ⏳ Pendiente | Comentarios descriptivos, nombres claros de variables y funciones, código bien organizado. |
| 2 | **Eficiencia y Rendimiento** | ⏳ Pendiente | Tiempo de ejecución bajo, operaciones optimizadas, uso eficiente de recursos. |
| 3 | **Momento de Ejecución** | ⏳ Pendiente | Uso correcto de `BEFORE`, `AFTER` o `INSTEAD OF` según el escenario. |
| 4 | **Seguridad y Control de Acceso** | ⏳ Pendiente | Controles de acceso, validación de permisos, prevención de SQL injection dentro del trigger. |
| 5 | **Manejo de Errores y Excepciones** | ⏳ Pendiente | Bloques `EXCEPTION`, registro de errores en logs, mensajes de error claros. |
| 6 | **Consistencia de los Datos** | ⏳ Pendiente | Manejo correcto de claves foráneas y restricciones de integridad. |
| 7 | **Optimización de Recursos** | ⏳ Pendiente | Minimizar consultas redundantes, eliminar operaciones costosas o repetitivas. |
| 8 | **Escalabilidad** | ⏳ Pendiente | Pruebas de estrés bajo grandes volúmenes, manejo en escenarios de alto tráfico. |
| 9 | **Adecuación del Propósito** | ⏳ Pendiente | El trigger cumple su función (auditoría, integridad referencial, automatización). |
| 10 | **Documentación y Mantenibilidad** | ⏳ Pendiente | Documentación del propósito, condiciones de activación e impacto en los datos. |

---

## Criterios 7 — Ventanas / Módulos de Interfaz

> **Estado general: ✅ Completo — 10/10 logrados**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Legibilidad del Código** | ✅ Logrado | Blade templates con nombres descriptivos, separación por módulo en `resources/views/`, lógica en controllers separada de la vista. |
| 2 | **Eficiencia en Uso de Recursos** | ✅ Logrado | Assets auto-hospedados, sin CDN externos, Vite para compilación optimizada. |
| 3 | **Modularidad y Reutilización** | ✅ Logrado | Layout base `tabler.blade.php`, partials (sidebar, header, footer), componentes Blade reutilizables. |
| 4 | **Manejo de Eventos y Respuesta** | ✅ Logrado | JS vanilla en `blur`/`input` para validaciones, Bootstrap JS para tabs/collapse/dropdowns. Sin bloqueos perceptibles. |
| 5 | **Interfaz Amigable** | ✅ Logrado | Tabler (Bootstrap 5): diseño limpio, etiquetas en español, íconos SVG, dark/light mode, buscador integrado. |
| 6 | **Manejo de Errores y Validación** | ✅ Logrado | Validación server-side (Laravel) + client-side (JS), mensajes de error por campo, páginas de error personalizadas (403, 404, 500, 503). |
| 7 | **Seguridad del Código** | ✅ Logrado | CSRF en todos los formularios (`@csrf`), Eloquent ORM previene SQL injection, middleware `can:`, sin XSS por uso de `{{ }}` de Blade. |
| 8 | **Adaptabilidad y Responsividad** | ✅ Logrado | Bootstrap 5 responsive. Sidebar con hamburguesa en mobile. Stepper de compras con overflow-x. Cubre PC, tablet y teléfono. |
| 9 | **Consistencia con el Estilo General** | ✅ Logrado | Tabler v1.4.0 aplicado uniformemente. Mismos colores, fuente Inter, íconos SVG y patrones de navegación en todos los módulos. |
| 10 | **Documentación del Código** | ✅ Logrado | Docblocks humanizados en todos los controllers (`PermissionController`, `RoleController`, `UserController`, `LoginAttemptController`, `SocialiteController`, `NotificationController`, `ProfileController`, `Controller`). Comentarios en Blade donde la lógica no es obvia. |

---

## Criterios 8 — Seguridad del Sistema

> **Estado general: ⚠ Parcial — 5 logrados · 3 parciales · 2 pendientes**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Autenticación Segura** | ✅ Logrado | 2FA con Jetstream (`TwoFactorAuthenticatable`), contraseñas con bcrypt, indicador de complejidad en reset-password, bloqueo a 3 intentos fallidos (Fortify `RateLimiter`). |
| 2 | **Control de Acceso y RBAC** | ✅ Logrado | Spatie Permission con roles y permisos granulares por módulo/tab/acción. Middleware `can:` en rutas. Menú dinámico según permisos. |
| 3 | **Cifrado de Datos** | ⚠ Parcial | HTTPS en desarrollo (Herd). Contraseñas con bcrypt. No implementado cifrado AES/RSA para datos en reposo (datos sensibles en BD sin cifrado a nivel de campo). |
| 4 | **Vulnerabilidades y Parches** | ✅ Logrado | Laravel 13 + PHP 8.5 (versiones actuales). Composer/npm para gestión de dependencias actualizables. |
| 5 | **Protección contra Inyecciones** | ✅ Logrado | Eloquent ORM con queries preparadas. Blade escapa automáticamente con `{{ }}`. CSRF en todos los formularios. Validación de inputs en controllers. |
| 6 | **Seguridad en Sesiones** | ✅ Logrado | `SESSION_DRIVER=database`, tokens de sesión seguros, expiración automática, `auth:sanctum` middleware, HTTPS. |
| 7 | **Registro de Actividades** | ✅ Logrado | `LoginAttempt` registra todos los intentos (exitosos y fallidos) con email, IP, user agent, fecha/hora. Alertas por correo a admins. Vista de auditoría con filtros. |
| 8 | **Protección DoS/DDoS** | ⚠ Parcial | Rate limiting en login (3 intentos/15 min). No hay firewall ni mitigación DDoS activa — depende de infraestructura del servidor de producción. |
| 9 | **Backups seguros** | ⏳ Pendiente | No implementado. Requiere configurar pg_dump automático con almacenamiento seguro. |
| 10 | **Normativas y Estándares** | ⚠ Parcial | Sin auditoría formal (GDPR, ISO 27001). Las prácticas implementadas se alinean con buenas prácticas generales. Aceptable para alcance académico. |

---

## Criterios 9 — Auditoría de Transacciones

> **Estado general: ⚠ Parcial — 2 logrados · 7 parciales · 1 pendiente**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Registro completo de transacciones** | ⚠ Parcial | Login auditado completamente (email, IP, user agent, fecha/hora, resultado). Transacciones de negocio (compras, ventas, servicios) sin auditoría aún. |
| 2 | **Integridad y consistencia** | ⚠ Parcial | Logs de login íntegros. Pendiente para módulos de negocio. |
| 3 | **Seguridad y control de acceso a logs** | ✅ Logrado | Vista de auditoría protegida con `can:tab.mant.seguridad.auditoria`. Solo admins acceden. |
| 4 | **Trazabilidad completa** | ⚠ Parcial | Trazabilidad de accesos implementada. Trazabilidad de cambios en datos de negocio (quién modificó qué registro) pendiente. |
| 5 | **Cumplimiento de normativas** | ⚠ Parcial | Sin certificación formal. Prácticas alineadas con buenas prácticas. Aceptable para alcance académico. |
| 6 | **Monitoreo en tiempo real** | ⚠ Parcial | Alertas por correo ante intentos fallidos de login. Sin monitoreo en tiempo real de transacciones de negocio. |
| 7 | **Identificación de transacciones anómalas** | ⚠ Parcial | Detección de intentos fallidos consecutivos de login (envía alerta). Sin análisis de patrones en transacciones de negocio. |
| 8 | **Generación de informes** | ✅ Logrado | `LoginAttemptController` con filtros por nombre, IP, fecha desde/hasta. Vista `auditoria/acceso.blade.php`. Módulo de Elaborar Informes con 4 secciones (compras, ventas, servicios, cobranza). |
| 9 | **Respaldo de registros** | ⏳ Pendiente | Depende del sistema de backup general de BD (Criterio 3, punto 9). |
| 10 | **Prevención de alteraciones** | ⚠ Parcial | Acceso restringido por rol. Sin logs inmutables ni mecanismo de detección de cambios en registros de auditoría. |

---

## Criterios 10 — Manual de Usuario

> **Estado general: ⏳ Pendiente — manual aún no redactado**

| # | Criterio | Estado | Detalle |
|---|----------|--------|---------|
| 1 | **Claridad y Comprensión del Lenguaje** | ⏳ Pendiente | Lenguaje no técnico, frases cortas y directas, sin ambigüedades. |
| 2 | **Estructura y Organización** | ⏳ Pendiente | Índice, secciones o capítulos claros, organización de lo general a lo específico. |
| 3 | **Instrucciones Paso a Paso** | ⏳ Pendiente | Listas numeradas o instrucciones secuenciales para tareas comunes del sistema. |
| 4 | **Uso de Imágenes y Ejemplos Visuales** | ⏳ Pendiente | Capturas de pantalla, diagramas de flujo, ejemplos visuales para cada función. |
| 5 | **Cobertura Completa de Funcionalidades** | ⏳ Pendiente | Explicar todas las funciones críticas: login, menú, compras, ventas, servicios, informes, seguridad, perfil. |
| 6 | **FAQ y Troubleshooting** | ⏳ Pendiente | Sección de preguntas frecuentes, pasos para resolver errores comunes. |
| 7 | **Accesibilidad del Manual** | ⏳ Pendiente | Formato PDF o similar, contraste adecuado, disponible en español. |
| 8 | **Actualización y Relevancia** | ⏳ Pendiente | Fecha de última actualización visible, coherencia con la interfaz actual del sistema. |
| 9 | **Facilidad de Navegación** | ⏳ Pendiente | Hipervínculos en el índice, referencias cruzadas entre secciones relacionadas. |
| 10 | **Adecuación al Público Objetivo** | ⏳ Pendiente | Versión para usuarios finales (operadores) y versión para administradores del sistema. |

---

*Generado automáticamente desde la memoria del proyecto — SIGEA v5 · 2026-05-23*
