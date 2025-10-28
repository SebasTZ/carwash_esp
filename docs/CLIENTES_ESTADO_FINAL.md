# 🧾 CLIENTES - Estado Final de Migración

## Resumen ejecutivo

Migración de la entidad `Clientes` a componentes modernos.

- Migración completada: index, create, edit.
- Backups creados: `index-old.blade.php`, `create-old.blade.php`, `edit-old.blade.php`.
- Tests: 91/91 pasando.
- Build: Exitoso.

## Cambios principales

1. index -> reemplazada tabla por `DynamicTable`:
   - Columnas: `persona.razon_social`, `persona.numero_documento`, `persona.telefono`, `persona.email`, `persona.estado`, acciones (Editar / Eliminar).
   - Búsqueda global por nombre o documento.

2. create -> reemplazado por `FormValidator`:
   - Campos: `tipo_persona`, `razon_social`, `direccion`, `telefono`, `documento_id`, `numero_documento`.
   - Validaciones básicas: required en campos críticos.
   - Lógica condicional: muestra label apropiado para `tipo_persona` (natural/juridica).
   - Reglas dinámicas para `numero_documento` según tipo de documento (DNI=8,RUC=11).
   - Eliminado jQuery y Bootstrap-select; reemplazado por Vanilla JS y `FormValidator`.

3. edit -> migrado a `FormValidator` con pre-poblado:
   - `old()` combinado con `$cliente->persona->*` para preservar valores en caso de errores.
   - Reglas dinámicas iguales que en create.

## Notas técnicas

- Controller (`clienteController`) ya realiza eager loading en `index` con `persona.documento`, por lo que `DynamicTable` puede renderizar nested data sin problemas.

- En `edit`, la variable `$cliente` se carga con `persona.documento` y se usan `old()` + modelo para pre-poblado y manejo de errores.

- Asegurarse de que los `documentos` en la BD incluyan los textos `DNI` y `RUC` para que la lógica de maxlength/minlength funcione correctamente.

## Pasos para probar manualmente

1. Ir a Clientes -> Index: comprobar búsqueda y badges de estado.
2. Crear Cliente: probar tipo natural y jurídico, cambiar tipo de documento a DNI/RUC y verificar límite de caracteres.
3. Editar Cliente: verificar pre-poblado y que los cambios se guardan.
4. Ejecutar `npm test` y `npm run build` — ambos ya probados en esta sesión.

## Archivos cambiados

- `resources/views/cliente/index.blade.php` (migrada)
- `resources/views/cliente/create.blade.php` (migrada)
- `resources/views/cliente/edit.blade.php` (migrada)
- `resources/views/cliente/index-old.blade.php` (backup)
- `resources/views/cliente/create-old.blade.php` (backup)
- `resources/views/cliente/edit-old.blade.php` (backup)

## Próximos pasos recomendados

- Migrar `Clientes` avanzados: añadir subform para `Vehículos` si el sistema debe gestionar vehículos por cliente.
- Implementar tests E2E para flujos Create/Edit/Delete de `Clientes`.

---

Migración realizada correctamente. Si quieres, empiezo la migración de `Clientes` con gestión de vehículos (sub-form dinámico) como siguiente paso.
