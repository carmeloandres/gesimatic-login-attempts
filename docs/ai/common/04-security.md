# Gesimatic — Seguridad

## 1. Principio general

Toda entrada externa debe considerarse no confiable.

La seguridad debe aplicarse en el servidor, aunque exista validación en el navegador.

Los cambios relacionados con autenticación, acceso, permisos, sesiones, tokens, REST API, rate limiting, bloqueo, subida de archivos o comunicación remota requieren revisión reforzada.

---

## 2. Modelo básico de amenazas

Deben contemplarse, como mínimo:

- usuarios no autenticados;
- usuarios autenticados sin permisos;
- administradores de sitio en Multisite;
- superadministradores;
- bots;
- fuerza bruta;
- abuso de endpoints;
- replay de solicitudes;
- manipulación de parámetros;
- XSS;
- CSRF;
- SQL injection;
- SSRF;
- path traversal;
- subida de archivos maliciosos;
- exposición de secretos;
- filtrado insuficiente de logs;
- abuso de procesos programados;
- dependencia comprometida.

---

## 3. Autenticación y autorización

Autenticación y autorización son controles distintos.

### Obligatorio

- Verificar la identidad mediante el mecanismo correspondiente.
- Comprobar capacidades para cada operación sensible.
- No confiar únicamente en un nonce.
- No confiar en parámetros que indiquen el rol o identidad.
- No exponer operaciones administrativas a usuarios sin capacidad.
- En Multisite, revisar el alcance del administrador de sitio frente al superadministrador.

---

## 4. Nonces y CSRF

Los nonces de WordPress ayudan a mitigar CSRF.

### Reglas

- Verificar nonces en acciones iniciadas desde el navegador.
- Asociar el nonce a una acción concreta.
- No reutilizar nonces para propósitos diferentes.
- No considerarlos secretos.
- No utilizarlos como único control de permisos.
- No usarlos como autenticación servidor a servidor.

---

## 5. Validación de entrada

Toda entrada debe:

1. limitarse a los campos esperados;
2. comprobar tipo;
3. comprobar formato;
4. comprobar longitud;
5. comprobar rango;
6. sanearse;
7. validarse según reglas de negocio.

### Reglas

- Usar listas permitidas para valores enumerados.
- Rechazar claves desconocidas cuando el contrato lo requiera.
- No confiar en datos previamente validados por el cliente.
- No interpretar cadenas arbitrarias como nombres de clase, método, archivo o callback.
- No usar `unserialize()` con datos no confiables.
- No ejecutar código dinámico.

---

## 6. Escape de salida y XSS

Todo dato dinámico debe escaparse según su contexto.

### Contextos comunes

- HTML: `esc_html()`;
- atributo: `esc_attr()`;
- URL: `esc_url()`;
- HTML permitido: `wp_kses()` o `wp_kses_post()`;
- JavaScript: preferir JSON seguro y APIs de datos;
- JSON: `wp_json_encode()`.

No asumir que un valor guardado previamente es seguro para cualquier contexto.

---

## 7. SQL injection

### Obligatorio

- Usar `$wpdb->prepare()` para valores dinámicos.
- No concatenar entradas en SQL.
- No permitir nombres de columnas, direcciones de orden o tablas sin lista permitida.
- Validar enteros y límites.
- Evitar consultas construidas a partir de parámetros arbitrarios.

Las placeholders no pueden utilizarse directamente para nombres de tabla o columna; estos deben seleccionarse desde una lista segura.

---

## 8. REST API

Todo endpoint debe definir una política de permisos.

### Reglas

- Implementar `permission_callback`.
- Validar argumentos.
- Limitar métodos HTTP.
- Aplicar rate limiting cuando exista riesgo de abuso.
- Evitar enumeración de usuarios o recursos sensibles.
- No revelar si una cuenta concreta existe salvo necesidad funcional.
- Usar mensajes de error que no ayuden a un atacante.
- No devolver trazas ni detalles internos.
- Mantener estructura de error uniforme.

---

## 9. Rate limiting y bloqueos

En plugins como Gesimatic Login Attempts, el rate limiting es una función crítica.

### Reglas

- No reducir protecciones sin autorización.
- Definir claramente la clave de limitación.
- Evitar confiar ciegamente en cabeceras de proxy.
- Tratar correctamente IPv4 e IPv6.
- Normalizar la dirección antes de almacenarla.
- Contemplar proxies de confianza configurados.
- Prevenir desbordamiento de almacenamiento.
- Limpiar registros caducados.
- Evitar bloqueos permanentes accidentales.
- No revelar información que permita ajustar ataques con precisión innecesaria.

### Respuestas HTTP

Cuando corresponda, utilizar `429 Too Many Requests`.

Puede incluirse información como el tiempo de espera, pero no deben exponerse detalles internos del mecanismo de seguridad.

---

## 10. Direcciones IP y proxies

Las cabeceras como `X-Forwarded-For` pueden ser falsificadas si no existe un proxy de confianza.

### Reglas

- Usar `REMOTE_ADDR` como base.
- Solo confiar en cabeceras de proxy cuando el proxy esté configurado y validado.
- Si una cabecera contiene varias IP, aplicar una estrategia documentada.
- Validar con `filter_var(..., FILTER_VALIDATE_IP)`.
- No almacenar más información de la necesaria.
- Considerar privacidad y retención.

---

## 11. Contraseñas y credenciales

- Nunca registrar contraseñas.
- No almacenar contraseñas en texto plano.
- Usar las APIs de hashing de WordPress.
- No enviar credenciales por URL.
- No incluir secretos en JavaScript.
- No hardcodear claves.
- No confirmar mediante errores si una contraseña concreta es correcta parcialmente.
- Limpiar datos sensibles de excepciones y logs.

---

## 12. Tokens y comunicación remota

### Reglas

- Transmitir exclusivamente mediante HTTPS.
- Validar el host de destino.
- Definir timeout.
- Comprobar errores y códigos HTTP.
- No desactivar verificación SSL.
- Limitar el alcance y duración de tokens.
- Rotar o revocar cuando sea posible.
- No registrar tokens completos.
- Comparar firmas mediante funciones resistentes a timing attacks, como `hash_equals()` cuando corresponda.
- Incluir protección frente a replay si el riesgo lo exige.

---

## 13. Firmas de solicitudes

Cuando exista firma de mensajes:

- firmar una representación canónica;
- incluir timestamp;
- limitar la ventana temporal;
- incluir nonce único si es necesario;
- validar algoritmo;
- usar clave adecuada;
- comparar con `hash_equals()`;
- rechazar timestamps fuera de rango;
- evitar reutilización.

No inventar un protocolo criptográfico propio si existe un estándar adecuado.

---

## 14. SSRF

Toda URL controlable externamente debe tratarse con precaución.

### Reglas

- Usar listas permitidas de hosts cuando sea posible.
- Rechazar esquemas no autorizados.
- Evitar acceso a localhost, red privada y metadata services.
- Controlar redirecciones.
- Validar de nuevo tras redirección.
- Utilizar las funciones seguras de WordPress, como `wp_safe_remote_get()` cuando corresponda.

---

## 15. Archivos y rutas

### Reglas

- No construir rutas directamente con entradas.
- Usar nombres generados o listas permitidas.
- Verificar extensiones y MIME.
- No confiar únicamente en el nombre del archivo.
- Evitar ejecución en directorios de subida.
- No permitir `../`.
- Normalizar rutas.
- Limitar tamaño.
- Usar APIs de WordPress para uploads.
- No incluir archivos subidos como código.

---

## 16. Deserialización y ejecución dinámica

No utilizar con datos no confiables:

- `unserialize()`;
- `eval()`;
- callbacks arbitrarios;
- nombres de clase proporcionados por el cliente;
- inclusión dinámica de archivos;
- comandos del sistema.

Si debe leerse contenido serializado heredado, usar mecanismos seguros y restringidos, y documentar el riesgo.

---

## 17. Logs

Los logs deben ser útiles sin exponer información sensible.

### No registrar

- contraseñas;
- tokens completos;
- nonces;
- cookies de sesión;
- cabeceras de autorización;
- cuerpos completos sensibles;
- claves privadas;
- datos personales innecesarios.

### Recomendado

- usar identificadores de correlación;
- truncar valores;
- registrar categorías de error;
- separar mensaje público de detalle interno;
- aplicar retención limitada.

---

## 18. Errores

Los errores públicos deben:

- ser comprensibles;
- usar códigos estables;
- no revelar rutas;
- no revelar SQL;
- no revelar clases internas;
- no revelar stack traces;
- no revelar configuración;
- no confirmar datos sensibles.

Los detalles técnicos deben registrarse de forma segura cuando sea necesario.

---

## 19. Dependencias

Antes de añadir o actualizar una dependencia:

- justificar su necesidad;
- revisar mantenimiento;
- revisar licencia;
- revisar vulnerabilidades;
- comprobar compatibilidad;
- evitar paquetes abandonados;
- actualizar lockfiles de forma coherente.

No ejecutar scripts desconocidos de dependencias sin revisión.

---

## 20. Action Scheduler y tareas programadas

- Validar argumentos antes de ejecutar.
- No asumir que una acción programada fue creada por código legítimo.
- Evitar incluir secretos.
- Diseñar acciones idempotentes.
- Controlar reintentos.
- Limitar lotes.
- Prevenir escalada de privilegios.
- Verificar el contexto cuando la acción afecte a un sitio concreto en Multisite.

---

## 21. Multisite

Debe revisarse especialmente:

- separación entre datos de sitios;
- acceso de administradores de sitio;
- operaciones de red;
- cambio de contexto con `switch_to_blog()`;
- restauración de contexto;
- exposición cruzada de opciones;
- tareas que recorren toda la red;
- eliminación de datos.

Un administrador de sitio no debe acceder a datos o acciones reservadas a la red.

---

## 22. Privacidad

- Recopilar solo los datos necesarios.
- Definir retención.
- Permitir eliminación cuando corresponda.
- Reducir granularidad de IP cuando sea posible.
- No usar datos de seguridad para finalidades no relacionadas.
- Evitar mostrar datos personales en interfaces o logs.
- Documentar transferencias a servicios externos.

---

## 23. Checklist de revisión de seguridad

Antes de finalizar un cambio, comprobar:

- ¿La operación requiere autenticación?
- ¿Se comprueban capacidades?
- ¿Requiere nonce?
- ¿La entrada está limitada, saneada y validada?
- ¿La salida se escapa según contexto?
- ¿Existe riesgo de SQL injection?
- ¿Existe riesgo de XSS?
- ¿Existe riesgo de CSRF?
- ¿Existe riesgo de SSRF?
- ¿Existe riesgo de path traversal?
- ¿Puede abusarse mediante repetición?
- ¿Es necesario rate limiting?
- ¿Se filtra información sensible?
- ¿Los logs son seguros?
- ¿El comportamiento es correcto en Multisite?
- ¿La respuesta revela existencia de usuarios o recursos?
- ¿Los errores internos permanecen internos?

---

## 24. Regla de prudencia

Si una modificación afecta a autenticación, permisos, tokens, firmas, bloqueos, subida de archivos, SQL, comunicación remota o cifrado, no debe asumirse que el cambio es trivial.

Debe explicarse el riesgo, revisar el flujo completo y proponer pruebas específicas.