# Guía Corta de Estilo Blade UI

## Objetivo

Definir reglas simples y consistentes para nuevas vistas Blade y refactors UI del proyecto.

## 1) Estructura base de vistas

- Usar siempre `@extends('layouts.app')`.
- Definir `@section('title', '...')` con título claro del módulo.
- En `@section('content')` incluir `@include('layouts.partials.alert')` al inicio.
- Estructura recomendada:
    - `container-fluid px-4`
    - `cw-page-header` (título + acciones)
    - `breadcrumb`
    - `card` principal con contenido

## 2) Encabezados y acciones

- Encabezado estándar:
    - `cw-page-header`
    - `cw-page-title`
    - `cw-page-actions`
- Acciones principales al lado derecho, evitando duplicarlas dentro del card cuando no sea necesario.

## 3) Formularios

- Contenedor de formulario: clase `cw-form`.
- Pie de formulario: `cw-form-actions` para botones.
- Inputs y selects con clases Bootstrap 5 (`form-control`, `form-select`).
- Mostrar errores con `@error('campo')` y mensaje corto.
- Mantener nombres de campos alineados con validaciones de controlador.

## 4) Listados (index)

- Preferir tabla server-side paginada o `DynamicTable` según módulo existente.
- Encabezados de tabla claros y acciones agrupadas en columna final.
- Usar `<x-pagination-info ... />` para paginación uniforme.

## 5) Confirmaciones y modales

- Para acciones destructivas en botones/enlaces: usar atributos `data-confirm` (modal global).
- Para confirmaciones con submit y contenido contextual: usar `<x-confirm-action-modal ...>`.
- Evitar `onclick="return confirm(...)"` en nuevas implementaciones.

## 6) JavaScript en vistas

- Preferir helpers globales en `window.CarWash` antes que funciones inline repetidas.
- Para modales dinámicos de acción, usar `window.CarWash.openActionModal(...)`.
- Evitar duplicar lógica idéntica en múltiples vistas.

## 7) Idioma y copy

- Mantener copy en español en vistas de usuario interno.
- Textos de acción claros: `Eliminar`, `Restaurar`, `Finalizar`, `Cancelar`.

## 8) Accesibilidad mínima

- Usar labels asociados a inputs (`for`/`id`).
- Botones con `title` cuando solo tienen ícono.
- Mantener estructura semántica simple y consistente.

## Checklist rápido para PR

- [ ] Vista usa `layouts.app`
- [ ] Header y breadcrumbs consistentes
- [ ] Formulario/listado sigue patrones `cw-*`
- [ ] Confirmaciones usan `data-confirm` o `x-confirm-action-modal`
- [ ] Sin `onclick="return confirm(...)"`
- [ ] Sin duplicación de JS evitable
