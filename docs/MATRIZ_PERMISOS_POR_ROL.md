# Matriz de Permisos por Rol

## Convenciones

- Todos los endpoints HTTP se protegen con `permission:` en rutas.
- Controllers sensibles agregan segunda capa con `$this->authorize(...)`.
- Para recursos con propietario (ventas y citas), se aplica Policy por recurso.
- Roles con acceso global: `administrador`, `admin`, `superadmin`.

## Matriz actual (seeders)

| Rol             | Estado       | Alcance principal                             |
| --------------- | ------------ | --------------------------------------------- |
| `administrador` | Implementado | Todos los permisos (`Permission::pluck(...)`) |
| `cajero`        | Implementado | Tarjetas de regalo y fidelidad                |

### Permisos actuales de `cajero`

- `ver-tarjeta-regalo`
- `crear-tarjeta-regalo`
- `reporte-tarjeta-regalo`
- `exportar-tarjeta-regalo`
- `ver-fidelidad`
- `gestionar-fidelidad`
- `reporte-fidelidad`
- `exportar-fidelidad`

## Matriz recomendada (operativa)

| Módulo                                | administrador | cajero               | vendedor                |
| ------------------------------------- | ------------- | -------------------- | ----------------------- |
| Ventas (`ver/crear/mostrar/eliminar`) | SI (global)   | Opcional por negocio | SI (propias por Policy) |
| Reportes de ventas                    | SI (global)   | Opcional por negocio | SI (propios por Policy) |
| Citas (`ver/crear/editar/eliminar`)   | SI (global)   | Opcional por negocio | SI (propias por Policy) |
| Confirmar cita                        | SI (global)   | Opcional por negocio | SI (propias por Policy) |
| Tarjetas de regalo                    | SI            | SI                   | Opcional                |
| Fidelidad                             | SI            | SI                   | Opcional                |
| Usuarios y roles                      | SI            | NO                   | NO                      |

## Regla de propiedad activa

- `VentaPolicy`: usuarios no privilegiados solo pueden ver/eliminar ventas propias.
- `CitaPolicy`: usuarios no privilegiados solo pueden editar/confirmar/eliminar citas propias.
- Compatibilidad legacy en citas: si `user_id` es `NULL`, la Policy permite acceso por permiso para no bloquear datos históricos.

## Auditoría de seguridad

Se registra en logs (`authorization.audit.*`):

- Asignación y sincronización de roles por usuario.
- Sincronización de permisos por rol.
- Eliminación de usuarios con sus roles previos.
- Eliminación de roles con sus permisos previos.
