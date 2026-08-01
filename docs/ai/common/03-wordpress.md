# Gesimatic — Reglas de desarrollo WordPress

## 1. Principio general

Los plugins Gesimatic deben utilizar las APIs oficiales de WordPress siempre que resulten adecuadas.

No debe replicarse funcionalidad que WordPress ya proporciona de forma estable y segura.

---

## 2. Ciclo de carga

Debe respetarse el ciclo de carga de WordPress.

### Reglas

- No ejecutar lógica significativa al incluir archivos.
- Registrar hooks en el momento adecuado.
- No asumir que todos los plugins están cargados antes de `plugins_loaded`.
- No registrar rutas REST antes de `rest_api_init`.
- No cargar assets de administración fuera de las pantallas necesarias.
- No realizar operaciones costosas en cada request sin necesidad.

---

## 3. Hooks

### Obligatorio

- Utilizar nombres únicos y coherentes.
- Mantener compatibilidad de hooks públicos.
- Documentar parámetros y valor de retorno en filtros.
- Validar el contexto antes de ejecutar lógica costosa.
- Evitar closures cuando dificulten eliminar hooks o probar el código.

### Recomendado

- Encapsular el registro de hooks.
- Separar el registro del comportamiento.
- Usar métodos pequeños como callbacks.
- Evitar hooks globales cuando exista uno más específico.

---

## 4. Internacionalización

Todo texto visible para el usuario debe internacionalizarse.

### Reglas

- Utilizar el text domain real del plugin.
- No concatenar fragmentos traducibles cuando altere el orden gramatical.
- Usar placeholders con `sprintf` cuando sea necesario.
- Escapar según el contexto, utilizando funciones combinadas como `esc_html__()` cuando corresponda.
- No traducir identificadores internos, claves, slugs o nombres de hooks.

---

## 5. Sanitización, validación y escape

Son operaciones diferentes.

### Sanitización

Normaliza una entrada.

Ejemplos:

- `sanitize_text_field()`;
- `sanitize_email()`;
- `sanitize_key()`;
- `absint()`;
- `sanitize_url()`;
- `wp_kses_post()`.

### Validación

Comprueba que el valor cumple los requisitos de negocio.

La sanitización no sustituye a la validación.

### Escape

Se realiza en el momento de salida y según el contexto.

Ejemplos:

- `esc_html()`;
- `esc_attr()`;
- `esc_url()`;
- `wp_kses_post()`;
- `wp_json_encode()` para datos JSON.

### Reglas

- Sanear y validar toda entrada externa.
- Escapar lo más tarde posible.
- No escapar antes de almacenar salvo que la API lo requiera.
- No usar una función de escape incorrecta para el contexto.
- No confiar en valores procedentes de opciones o base de datos únicamente porque fueron guardados previamente.

---

## 6. Superglobales

Evitar acceso directo a:

- `$_GET`;
- `$_POST`;
- `$_REQUEST`;
- `$_COOKIE`;
- `$_SERVER`;
- `$_FILES`.

Cuando sea necesario:

1. comprobar existencia;
2. aplicar `wp_unslash()` si WordPress pudo añadir slashes;
3. sanear;
4. validar;
5. limitar tamaño y formato.

No utilizar `$_REQUEST` salvo justificación excepcional.

---

## 7. Nonces

Los nonces protegen frente a CSRF, pero no sustituyen la autorización.

### Reglas

- Verificar nonce en operaciones sensibles iniciadas por usuario.
- Comprobar capacidades además del nonce.
- No tratar un nonce como secreto.
- No utilizar nonces para autenticar comunicaciones servidor a servidor.
- En REST autenticado desde WordPress, usar el mecanismo adecuado, como `X-WP-Nonce`, cuando corresponda.

---

## 8. Capacidades y permisos

Toda operación sensible debe verificar capacidades.

### Reglas

- No confiar en el rol; comprobar capacidades.
- Usar la capacidad más específica posible.
- En Multisite, distinguir entre administrador del sitio y superadministrador.
- No asumir que `manage_options` equivale a control de red.
- En endpoints REST, implementar un `permission_callback` real.
- No utilizar `__return_true` en endpoints sensibles.

---

## 9. REST API

### Registro

- Registrar rutas en `rest_api_init`.
- Definir namespace y versión.
- Declarar métodos permitidos.
- Definir argumentos cuando sea útil.
- Proporcionar `permission_callback`.

### Entrada

- Usar `WP_REST_Request`.
- Validar parámetros.
- No asumir que el cuerpo POST siempre existe.
- Admitir cuerpos vacíos únicamente cuando el contrato del endpoint lo permita.
- Evitar leer directamente `php://input` salvo necesidad justificada.

### Respuesta

- Usar `WP_REST_Response`, `WP_Error` o la abstracción común del proyecto.
- Utilizar códigos HTTP apropiados.
- Mantener una estructura uniforme.
- No devolver detalles internos sensibles.

---

## 10. Opciones y configuración

Antes de usar una opción debe determinarse su ámbito.

### Sitio individual

- `get_option()`;
- `update_option()`;
- `delete_option()`.

### Red Multisite

- `get_site_option()`;
- `update_site_option()`;
- `delete_site_option()`.

### Reglas

- No usar opciones de red para datos que pertenecen a un sitio.
- No usar opciones de sitio para configuración global de red.
- Registrar valores por defecto.
- Validar antes de guardar.
- Evitar autoload para opciones grandes o poco utilizadas.
- No almacenar secretos en texto plano cuando pueda evitarse.

---

## 11. Multisite

Gesimatic debe contemplar WordPress Multisite cuando el plugin lo declare compatible.

Comprobar:

- activación por sitio o por red;
- ámbito de opciones;
- tablas globales o por sitio;
- capacidades;
- cambio de blog con `switch_to_blog()`;
- restauración con `restore_current_blog()`;
- procesos programados;
- desinstalación;
- altas de nuevos sitios.

No ejecutar cambios en todos los sitios de una red sin analizar el coste y la recuperación ante fallos.

---

## 12. Base de datos

### Reglas

- Preferir APIs de WordPress.
- Usar `$wpdb->prepare()` para valores dinámicos.
- No interpolar entradas en SQL.
- Validar nombres dinámicos de tablas o columnas mediante listas permitidas.
- Usar `dbDelta()` únicamente cuando corresponda y conociendo sus limitaciones.
- Versionar el esquema.
- Diseñar migraciones repetibles o seguras ante reejecución.

---

## 13. Cron y Action Scheduler

WordPress Cron no es tiempo real.

Cuando se utilice WP-Cron o Action Scheduler:

- contemplar retrasos;
- evitar tareas demasiado pesadas;
- procesar en lotes;
- controlar reintentos;
- prevenir duplicados;
- registrar fallos sin datos sensibles;
- limpiar tareas obsoletas cuando corresponda.

---

## 14. HTTP API

Para solicitudes remotas usar:

- `wp_remote_get()`;
- `wp_remote_post()`;
- `wp_remote_request()`.

### Reglas

- establecer timeout razonable;
- validar URL;
- controlar errores con `is_wp_error()`;
- comprobar código HTTP;
- limitar redirecciones cuando sea necesario;
- validar y decodificar la respuesta;
- no desactivar SSL verification;
- no enviar secretos a dominios no autorizados.

---

## 15. Assets

### Reglas

- Registrar y encolar scripts y estilos mediante WordPress.
- Cargar solo donde sean necesarios.
- Declarar dependencias.
- Utilizar versiones coherentes para caché.
- No imprimir scripts inline arbitrarios.
- Para datos de configuración usar el mecanismo adecuado.

`wp_localize_script()` puede utilizarse para traducciones o datos sencillos, pero no debe emplearse como contenedor ilimitado de configuración.

Los valores deben prepararse correctamente antes de pasarlos. WordPress serializa el array a JavaScript, pero esto no sustituye la validación, el escape contextual previo ni el diseño seguro de los datos.

---

## 16. JavaScript y React

- Usar JavaScript moderno cuando el proceso de compilación lo permita.
- No introducir jQuery si el plugin no lo utiliza.
- Mantener separados datos, lógica y presentación.
- No incluir secretos en el bundle.
- Validar también en servidor.
- No confiar en controles del cliente para seguridad.
- Internacionalizar textos mediante las herramientas de WordPress.

---

## 17. AJAX heredado

Preferir REST API cuando encaje con la arquitectura.

Si se utiliza `admin-ajax.php`:

- registrar acciones autenticadas y no autenticadas conscientemente;
- verificar nonce;
- comprobar capacidades;
- sanear y validar;
- devolver con `wp_send_json_success()` o `wp_send_json_error()`;
- no mezclar salida HTML accidental con JSON.

---

## 18. Activación, desactivación y desinstalación

### Activación

- Crear estructuras necesarias.
- Registrar versión instalada.
- Evitar procesos largos.
- No depender de hooks que todavía no se han registrado.

### Desactivación

- Detener tareas cuando corresponda.
- No borrar datos del usuario por defecto.

### Desinstalación

- Borrar datos solo mediante decisión explícita.
- Considerar Multisite.
- Proteger el archivo de desinstalación.
- Eliminar opciones, tablas y tareas de forma controlada.

---

## 19. Privacidad

Cuando el plugin trate datos personales:

- minimizar la recopilación;
- documentar el propósito;
- controlar retención;
- evitar logs excesivos;
- integrar exportación o borrado de WordPress cuando corresponda;
- no exponer correos, IP, tokens o identificadores sin necesidad.

---

## 20. Errores y depuración

- No mostrar warnings o excepciones al usuario final.
- Usar `WP_DEBUG` y logging de forma adecuada.
- Evitar `var_dump()`, `print_r()` o `die()` en producción.
- No registrar contraseñas, tokens, nonces o cuerpos sensibles.
- Proporcionar mensajes públicos claros y mensajes internos útiles.

---

## 21. Compatibilidad

Antes de utilizar una API:

- comprobar versión mínima de WordPress;
- comprobar versión mínima de PHP;
- evitar funciones obsoletas;
- no asumir extensiones opcionales sin verificarlas;
- proporcionar degradación controlada cuando sea razonable.