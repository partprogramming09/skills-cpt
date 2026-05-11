# Skills CPT

Plugin de WordPress para registrar y gestionar el Custom Post Type de habilidades, con soporte para:

- Meta box en admin para nivel de dominio y color
- Soporte para Imagen Destacada (Logo de la habilidad)
- Campos en REST API para uso headless
- Campos en WPGraphQL (si el plugin esta activo)
- Compatibilidad con Polylang para contenido traducible

## Requisitos

- WordPress 6.x o superior
- PHP 7.4+
- Opcional: WPGraphQL, Polylang

## Instalacion

1. Copia la carpeta `skills-cpt` dentro de `wp-content/plugins/`.
2. Activa **Skills CPT** en el panel de administracion.

## Que registra

- CPT: `skill`
- Slug: `habilidades`
- Soporta: `title`, `thumbnail` (logo), `excerpt`

## Campos de metadatos (internos)

- `_skill_level`: Nivel del 0 al 100.
- `_skill_color`: Color hexadecimal de la marca/habilidad.
- `_skill_year`: Años de experiencia (0-30).

## REST API

Sobre el tipo `skill`:
- `level`
- `color`
- `year`

## WPGraphQL

Sobre el tipo `Skill`:
- `level`
- `color`
- `year`
