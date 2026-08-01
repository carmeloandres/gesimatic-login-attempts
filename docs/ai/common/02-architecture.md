# Gesimatic — Arquitectura

## 1. Objetivo

Este documento define las reglas arquitectónicas generales de los plugins Gesimatic.

La implementación real de cada repositorio tiene prioridad. Antes de crear nuevas clases o modificar la estructura, deben inspeccionarse:

- `composer.json`;
- el archivo principal del plugin;
- la estructura de directorios;
- los namespaces existentes;
- las clases base;
- las interfaces;
- los módulos;
- los servicios registrados;
- las pruebas;
- los comandos disponibles.

---

## 2. Composer y PSR-4

El proyecto utiliza Composer y autoload PSR-4.

### Obligatorio

- Cada clase debe declarar un namespace coherente con su ruta.
- Las clases propias deben cargarse mediante Composer.
- No utilizar `require`, `require_once`, `include` o `include_once` para cargar clases propias.
- No crear mapas de carga manuales si PSR-4 puede resolverlos.
- Después de modificar namespaces o configuración de autoload, ejecutar la comprobación correspondiente, normalmente `composer dump-autoload`.

### Antes de crear una clase

Debe comprobarse la sección `autoload.psr-4` de `composer.json`.

No asumir que todos los plugins comparten el mismo namespace raíz o la misma carpeta base.

---

## 3. Estructura modular

Gesimatic puede organizar su funcionalidad mediante módulos, por ejemplo:

- `ApiModule`;
- `AdminModule`;
- `QueueModule`;
- `AssetsModule`.

Un módulo debe encargarse de registrar y coordinar una capacidad concreta.

### Un módulo puede

- registrar hooks;
- registrar servicios;
- registrar rutas;
- configurar assets;
- conectar componentes;
- inicializar integraciones.

### Un módulo no debe

- acumular toda la lógica de negocio;
- convertirse en contenedor de utilidades;
- realizar trabajo pesado en su constructor;
- mezclar responsabilidades no relacionadas.

La lógica reutilizable debe extraerse a servicios.

---

## 4. Core y arranque del plugin

La clase principal o `Core` debe coordinar el arranque del plugin.

Sus responsabilidades pueden incluir:

- cargar dependencias;
- registrar módulos;
- iniciar servicios;
- conectar hooks principales;
- preparar activación, desactivación o actualización.

No debe contener lógica de negocio extensa.

No deben introducirse nuevas instancias globales o singletons salvo que la arquitectura existente los requiera expresamente.

---

## 5. Servicios

Los servicios encapsulan lógica reutilizable o integraciones.

Ejemplos:

- clientes HTTP;
- validadores;
- rate limiters;
- repositorios;
- gestores de configuración;
- servicios de autenticación;
- servicios de correo;
- adaptadores de Action Scheduler;
- generadores de respuestas.

### Reglas

- Un servicio debe tener una responsabilidad clara.
- Sus dependencias deben ser explícitas.
- Debe evitarse obtener dependencias desde variables globales.
- La creación de servicios debe seguir el mecanismo existente del proyecto.
- No crear un contenedor de dependencias nuevo si el repositorio no lo utiliza.
- No convertir toda clase en servicio de forma automática.

---

## 6. Herencia y composición

Debe preferirse composición frente a herencia.

La herencia solo debe utilizarse cuando exista una relación real de subtipo.

### No permitido

Heredar de una clase `Setup`, `Installer` o similar únicamente para reutilizar:

- constantes;
- métodos auxiliares;
- acceso a opciones;
- utilidades compartidas.

En esos casos debe extraerse la funcionalidad a:

- un servicio;
- una clase base con responsabilidad coherente;
- una interfaz;
- un objeto de configuración;
- una clase de constantes, cuando esté justificado.

### Ejemplo conceptual

Un manejador de acciones no es un tipo de instalador.

Por tanto, no debe extender una clase de instalación solo para acceder a métodos o constantes.

---

## 7. Interfaces

Las interfaces deben representar contratos útiles.

### Reglas

- Los nombres deben terminar en `Interface`.
- No crear interfaces con una única implementación sin una razón concreta.
- Usar interfaces cuando exista más de una implementación, un límite arquitectónico o una necesidad de sustitución.
- Las clases que actúan como manejadores de acciones de controladores deben implementar el contrato común establecido, por ejemplo `ControllerActionInterface`, cuando exista en el repositorio.
- No cambiar firmas de interfaces públicas sin analizar el impacto.

---

## 8. Controladores

Los controladores coordinan la entrada, autorización, validación y delegación.

No deben contener lógica de negocio extensa.

Un controlador debería:

1. recibir la petición;
2. verificar permisos;
3. validar los datos;
4. delegar la ejecución;
5. devolver una respuesta uniforme.

Cuando exista una clase base como `AbstractApiController`, los nuevos controladores compatibles deben utilizarla.

No asumir el nombre o ubicación exacta sin verificar el repositorio.

---

## 9. Manejadores de acciones

Los manejadores de acciones encapsulan operaciones concretas solicitadas por un controlador.

### Responsabilidades

- ejecutar una acción determinada;
- recibir datos ya encaminados por el controlador;
- validar los datos específicos de la acción cuando corresponda;
- utilizar servicios;
- devolver un resultado compatible con el contrato establecido.

### Reglas

- Deben implementar la interfaz común cuando exista.
- No deben heredar de clases de configuración o instalación para reutilizar código.
- No deben acceder a superglobales directamente.
- No deben mezclar varias acciones independientes.
- No deben generar respuestas HTTP directamente si el contrato espera datos de dominio o un resultado intermedio.

---

## 10. Configuración y Setup

Las clases `Setup`, `Installer`, `Activator` o similares deben limitarse a responsabilidades como:

- creación de opciones iniciales;
- migraciones;
- instalación;
- activación;
- actualización;
- configuración inicial.

La lectura cotidiana de configuración debe realizarse mediante un servicio o componente específico.

Las constantes compartidas no deben quedar acopladas a una clase de instalación si son necesarias en tiempo de ejecución.

---

## 11. Respuestas y errores

Cuando exista una abstracción común como `CommonResponse`, debe utilizarse para mantener consistencia.

Las respuestas deben distinguir entre:

- éxito;
- error de validación;
- falta de permisos;
- recurso no encontrado;
- conflicto;
- demasiadas solicitudes;
- error interno.

No deben filtrarse excepciones, rutas, credenciales, trazas o datos sensibles al cliente.

En modo de depuración puede registrarse información adicional, pero no debe exponerse indiscriminadamente en la respuesta.

---

## 12. Persistencia

Para acceso a datos de WordPress:

- utilizar las APIs oficiales;
- usar `$wpdb` con consultas preparadas cuando sea necesario;
- evitar SQL directo si existe una API adecuada;
- centralizar consultas complejas en repositorios o servicios;
- no mezclar persistencia con presentación;
- considerar Multisite al decidir entre opciones de sitio y opciones de red.

No modificar esquemas o estructuras persistentes sin contemplar migración y compatibilidad.

---

## 13. Colas y tareas diferidas

Cuando se utilice Action Scheduler:

- las acciones deben ser idempotentes cuando sea posible;
- los argumentos deben ser serializables;
- no deben incluir secretos innecesarios;
- los fallos deben registrarse de forma segura;
- deben evitarse duplicados no intencionados;
- debe evaluarse el comportamiento al reintentar;
- las tareas no deben depender de estado efímero del request original.

---

## 14. Dependencias entre plugins

No asumir que otro plugin Gesimatic está activo.

Toda integración entre plugins debe:

- comprobar disponibilidad;
- fallar de forma controlada;
- evitar errores fatales;
- usar contratos públicos;
- no acceder a clases internas de otro plugin sin un acuerdo arquitectónico explícito.

---

## 15. Hooks y eventos

Los hooks permiten desacoplar componentes, pero no deben utilizarse como sustituto de una arquitectura clara.

### Reglas

- Mantener nombres estables.
- Documentar parámetros.
- No cambiar el orden o significado de argumentos públicos.
- Usar prefijos o namespaces coherentes.
- Evitar hooks excesivamente genéricos.
- No eliminar hooks públicos sin estrategia de compatibilidad.

---

## 16. Flujo recomendado de una petición REST

1. WordPress recibe la petición.
2. El router identifica el endpoint.
3. `permission_callback` verifica acceso inicial.
4. El controlador identifica la acción.
5. Se validan y normalizan los datos.
6. Se selecciona el manejador correspondiente.
7. El manejador usa los servicios necesarios.
8. Se construye una respuesta uniforme.
9. Se registran errores relevantes sin exponer información sensible.

La validación de la cadena de acción no debe duplicarse si el controlador ya la ha validado de manera fiable para seleccionar el manejador.

---

## 17. Nuevas abstracciones

Antes de crear una abstracción nueva, comprobar:

- si ya existe algo similar;
- si será reutilizada;
- si reduce acoplamiento real;
- si mejora las pruebas;
- si simplifica el código;
- si encaja con la nomenclatura existente.

No crear abstracciones únicamente para anticipar necesidades hipotéticas.

---

## 18. Cambios arquitectónicos

Todo cambio estructural relevante debe incluir:

- motivación;
- archivos afectados;
- impacto en compatibilidad;
- estrategia de migración;
- pruebas necesarias;
- ventajas e inconvenientes.

No realizar refactorizaciones amplias como efecto secundario de una tarea pequeña.