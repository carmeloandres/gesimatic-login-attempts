# Gesimatic — Descripción del proyecto

## 1. Propósito

Gesimatic es un ecosistema de plugins y herramientas para WordPress orientado a ofrecer funcionalidades profesionales, mantenibles y reutilizables.

Los componentes de Gesimatic pueden incluir:

- plugins gratuitos;
- plugins profesionales;
- servicios conectados con el servidor de Gesimatic;
- mecanismos de actualización y distribución;
- herramientas de administración;
- integración con WordPress Multisite;
- servicios de seguridad, correo, formularios, autenticación y mantenimiento.

Cada plugin debe poder evolucionar de forma independiente, pero debe conservar coherencia con el resto del ecosistema.

---

## 2. Objetivos principales

Las prioridades del proyecto son, en este orden:

1. Seguridad.
2. Estabilidad.
3. Mantenibilidad.
4. Claridad.
5. Compatibilidad con WordPress.
6. Rendimiento.
7. Extensibilidad.
8. Facilidad de uso.

No se debe sacrificar claridad o seguridad para reducir unas pocas líneas de código.

No se debe introducir complejidad anticipada sin una necesidad real.

---

## 3. Principios del producto

### 3.1 Código profesional

El código debe ser adecuado para producción y debe evitar soluciones provisionales que se conviertan en dependencias permanentes.

### 3.2 Compatibilidad hacia atrás

No deben romperse APIs públicas, hooks, filtros, nombres de opciones, estructuras de datos o contratos existentes salvo autorización expresa.

Cuando una ruptura sea inevitable:

1. Debe explicarse.
2. Debe justificarse.
3. Debe proponerse una estrategia de migración.
4. Debe documentarse el impacto.

### 3.3 Seguridad por defecto

Toda entrada externa debe tratarse como no confiable.

Los cambios relacionados con autenticación, permisos, nonces, sesiones, tokens, rate limiting, bloqueos, REST API o comunicación remota deben considerarse sensibles.

### 3.4 Coherencia entre plugins

Los plugins de Gesimatic deben compartir, cuando sea razonable:

- convenciones de nombres;
- estructura de namespaces;
- formato de respuestas;
- tratamiento de errores;
- patrones de validación;
- criterios de seguridad;
- estilo de código;
- estrategia de internacionalización;
- estrategia de compatibilidad.

### 3.5 Simplicidad

Debe preferirse la solución más sencilla que cumpla correctamente los requisitos.

No deben crearse nuevas capas, interfaces, servicios, traits, fábricas o abstracciones si no aportan un beneficio concreto.

---

## 4. Tecnologías generales

El ecosistema puede utilizar:

- PHP;
- Composer;
- PSR-4;
- PSR-12;
- WordPress;
- WordPress Multisite;
- WordPress REST API;
- Action Scheduler;
- JavaScript moderno;
- React para interfaces de administración cuando corresponda;
- herramientas de compilación de assets cuando el plugin lo requiera.

La versión mínima de PHP, WordPress y demás dependencias debe obtenerse de los archivos reales del repositorio, especialmente:

- `composer.json`;
- cabecera principal del plugin;
- `package.json`;
- configuración de compilación;
- documentación del propio plugin.

No asumir versiones que contradigan dichos archivos.

---

## 5. Idioma y nomenclatura

- La comunicación con el desarrollador debe realizarse en español.
- Los identificadores del código deben mantenerse en inglés.
- No deben traducirse nombres de clases, métodos, funciones, namespaces, hooks, filtros, endpoints o claves existentes.
- Los textos visibles para el usuario deben internacionalizarse mediante las APIs de WordPress.
- Los comentarios y PHPDoc deben respetar el idioma predominante del repositorio.

---

## 6. Forma de trabajar

Antes de modificar código:

1. Comprender el objetivo de la tarea.
2. Inspeccionar los archivos relacionados.
3. Buscar implementaciones similares dentro del mismo repositorio.
4. Identificar contratos públicos afectados.
5. Comprobar si existen pruebas o comandos de validación.
6. Limitar los cambios al alcance solicitado.

Durante la implementación:

- mantener coherencia con el código existente;
- evitar cambios cosméticos no relacionados;
- no añadir dependencias sin autorización;
- no reescribir archivos completos cuando baste un cambio localizado;
- no eliminar código sin comprender su propósito;
- no ocultar errores relevantes.

Después de la implementación:

- ejecutar las comprobaciones disponibles;
- revisar seguridad y compatibilidad;
- informar de las pruebas ejecutadas;
- indicar cualquier comprobación que no haya podido realizarse.

---

## 7. Decisiones y alternativas

Cuando existan varias soluciones razonables:

1. Recomendar una opción.
2. Explicar brevemente por qué.
3. Señalar ventajas e inconvenientes relevantes.
4. Evitar enumerar alternativas que no aporten valor.
5. Priorizar la compatibilidad con la arquitectura actual.

No debe presentarse una preferencia personal como una obligación del proyecto.

---

## 8. Reglas obligatorias

- Respetar la arquitectura y convenciones existentes.
- Mantener compatibilidad hacia atrás salvo autorización expresa.
- No modificar APIs públicas sin advertirlo.
- No eliminar hooks o filtros públicos sin autorización.
- No añadir dependencias de producción sin autorización.
- No introducir secretos, credenciales o tokens en el repositorio.
- No confiar en entradas procedentes del navegador, REST API, formularios, cabeceras o servicios remotos.
- No crear código duplicado cuando exista una abstracción común adecuada.
- No reutilizar una clase de instalación o configuración mediante herencia si la nueva clase no es conceptualmente un subtipo de ella.
- No modificar archivos ajenos a la tarea salvo necesidad justificada.

---

## 9. Recomendaciones

Siempre que encaje con el código existente:

- preferir composición frente a herencia;
- aplicar responsabilidad única;
- usar early return;
- mantener métodos pequeños;
- reducir anidaciones;
- separar validación, autorización y ejecución;
- encapsular dependencias;
- usar servicios para lógica reutilizable;
- favorecer objetos inmutables cuando resulte útil;
- evitar estado global;
- evitar singletons nuevos;
- evitar helpers globales nuevos;
- preferir APIs oficiales de WordPress.

---

## 10. Criterios de finalización

Una tarea no debe considerarse terminada hasta revisar:

- seguridad;
- compatibilidad con WordPress;
- compatibilidad con PHP;
- comportamiento en Multisite cuando aplique;
- rendimiento razonable;
- manejo de errores;
- casos límite;
- internacionalización;
- duplicación;
- coherencia con el resto del plugin;
- posibles efectos sobre APIs públicas.

---

## 11. Áreas previstas del ecosistema

Entre los componentes conocidos o previstos de Gesimatic se encuentran:

- Gesimatic Core;
- Gesimatic Server;
- Gesimatic Login Attempts;
- Gesimatic SMTP;
- Gesimatic Static Forms;
- Gesimatic Flags;
- módulos de API;
- módulos de administración;
- módulos de assets;
- módulos de cola;
- clientes de comunicación remota;
- controladores REST;
- manejadores de acciones;
- servicios compartidos;
- procesos mediante Action Scheduler.

Esta lista es orientativa. Antes de asumir que una clase o módulo existe, debe comprobarse en el repositorio actual.