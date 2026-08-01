# AGENTS.md

# Gesimatic - AI Development Guide

Este repositorio pertenece al proyecto **Gesimatic**.

Antes de realizar cualquier modificación debes comprender la arquitectura del proyecto y seguir las reglas descritas en este documento y en la documentación situada en `docs/ai/`.

---

# Objetivo

El objetivo principal es generar código que sea:

1. Seguro.
2. Mantenible.
3. Consistente.
4. Fácil de comprender.
5. Compatible con WordPress.

Nunca sacrificar la claridad del código por reducir unas pocas líneas.

---

# Documentación

Lee los siguientes documentos en este orden cuando la tarea afecte a esas áreas.

1. docs/ai/common/01-project.md
2. docs/ai/common/02-architecture.md
3. docs/ai/common/03-wordpress.md
4. docs/ai/common/04-security.md
5. dics/ai/plugin.md

---

# Tecnologías utilizadas

El proyecto utiliza:

- PHP 8.2 o superior.
- Composer.
- PSR-4.
- PSR-12.
- WordPress.
- WordPress Multisite.
- REST API.
- Action Scheduler.
- JavaScript ES6+.
- React para interfaces de administración cuando sea necesario.

No asumir tecnologías distintas.

---

# Autoload

El proyecto utiliza Composer siguiendo PSR-4.

No utilizar:

- require
- require_once
- include
- include_once

para cargar clases propias del proyecto.

Toda nueva clase debe ser compatible con el autoload de Composer.

---

# Reglas obligatorias

Estas reglas deben cumplirse siempre.

## Compatibilidad

- No romper compatibilidad hacia atrás.
- No modificar APIs públicas salvo indicación expresa.
- No eliminar filtros o acciones públicas.

## Arquitectura

- Respetar la arquitectura existente.
- No introducir nuevas capas innecesarias.
- Mantener coherencia con el resto del proyecto.

## Código

- Seguir PSR-12.
- Utilizar tipado estricto.
- Utilizar namespaces.
- Utilizar PHPDoc en elementos públicos.
- No duplicar código.

## WordPress

- Internacionalizar todos los textos.
- Escapar toda salida.
- Sanear toda entrada.
- Validar todos los datos externos.
- Utilizar las APIs oficiales de WordPress.

---

# Recomendaciones

Siempre que sea posible:

- Preferir composición frente a herencia.
- Aplicar SOLID.
- Aplicar KISS.
- Aplicar DRY.
- Utilizar Early Return.
- Mantener métodos pequeños.
- Mantener clases con una única responsabilidad.
- Evitar anidaciones profundas.
- Evitar efectos secundarios innecesarios.

---

# Cómo debe trabajar Codex

Antes de modificar código:

1. Comprender el problema.
2. Buscar implementaciones similares.
3. Mantener coherencia con el proyecto.
4. Evitar cambios no relacionados.

Cuando existan varias soluciones:

- Elegir la más sencilla.
- Explicar brevemente la elección.
- Indicar ventajas e inconvenientes.
- Evitar sobreingeniería.

No crear nuevas abstracciones sin una justificación clara.

---

# Antes de finalizar cualquier tarea

Comprobar siempre:

- Seguridad.
- Rendimiento.
- Compatibilidad WordPress.
- Compatibilidad PHP.
- Legibilidad.
- Código duplicado.
- Posibles casos límite.

---

# Cuando revises código

Analizar:

- Bugs.
- Seguridad.
- Rendimiento.
- Arquitectura.
- Nomenclatura.
- Mantenibilidad.
- Cumplimiento de SOLID.
- Cumplimiento de las convenciones de Gesimatic.

Priorizar siempre la estabilidad del proyecto frente a optimizaciones prematuras.