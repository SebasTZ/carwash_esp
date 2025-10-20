# ✅ Checklist de Deployment - CarWash ESP

## 📋 Pre-Deployment

### 1. Backup y Seguridad

-   [ ] Crear backup completo de la base de datos
    ```bash
    mysqldump -u root -p dbsistemaventas > backup_$(date +%Y%m%d_%H%M%S).sql
    ```
-   [ ] Guardar backup en ubicación segura (fuera del servidor)
-   [ ] Verificar integridad del backup (restaurar en BD de prueba)
-   [ ] Crear backup de archivos `.env` y configuraciones

### 2. Control de Versiones

-   [ ] Crear rama de desarrollo si no existe
    ```bash
    git checkout -b refactor/backend-improvements
    ```
-   [ ] Hacer commit de todos los cambios
    ```bash
    git add .
    git commit -m "feat: Implementación completa de arquitectura de servicios y testing"
    ```
-   [ ] Crear tag de versión
    ```bash
    git tag -a v2.0.0 -m "Versión 2.0 - Arquitectura refactorizada"
    ```

### 3. Testing

-   [ ] Ejecutar suite completa de tests
    ```bash
    vendor/bin/phpunit
    ```
-   [ ] Verificar que los 44 tests pasen (100%)
-   [ ] Revisar logs de errores
    ```bash
    tail -f storage/logs/laravel.log
    ```

### 4. Migraciones

-   [ ] Verificar estado de migraciones
    ```bash
    php artisan migrate:status
    ```
-   [ ] Ejecutar migraciones pendientes
    ```bash
    php artisan migrate
    ```
-   [ ] Verificar que las tablas se crearon correctamente:
    -   ✅ `stock_movimientos`
    -   ✅ `secuencia_comprobantes`

### 5. Configuración

-   [ ] Revisar archivo `.env` para producción
    ```env
    APP_ENV=production
    APP_DEBUG=false
    LOG_LEVEL=warning
    CACHE_DRIVER=redis  # o file
    QUEUE_CONNECTION=database  # o redis
    ```
-   [ ] Generar nueva APP_KEY si es ambiente nuevo
    ```bash
    php artisan key:generate
    ```
-   [ ] Verificar configuraciones de base de datos
-   [ ] Configurar permisos de storage y bootstrap/cache
    ```bash
    chmod -R 775 storage bootstrap/cache
    ```

### 6. Optimización

-   [ ] Limpiar cachés existentes
    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```
-   [ ] Optimizar para producción
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    ```
-   [ ] Instalar solo dependencias de producción
    ```bash
    composer install --no-dev --optimize-autoloader
    ```

### 7. Verificación Funcional

-   [ ] Probar flujo completo de venta con efectivo
-   [ ] Probar flujo completo de venta con tarjeta
-   [ ] Probar venta con tarjeta de regalo
-   [ ] Probar venta con lavado gratis
-   [ ] Verificar actualización de stock
-   [ ] Verificar generación de comprobantes
-   [ ] Verificar programa de fidelización
-   [ ] Verificar reportes (diario, semanal, mensual)
-   [ ] Verificar impresión de tickets
-   [ ] Probar gestión de estacionamiento

### 8. Logs y Monitoreo

-   [ ] Verificar que los logs se estén generando
    ```bash
    ls -lh storage/logs/
    ```
-   [ ] Probar canal de logs de ventas
    ```bash
    tail -f storage/logs/ventas.log
    ```
-   [ ] Probar canal de logs de stock
    ```bash
    tail -f storage/logs/stock.log
    ```
-   [ ] Configurar rotación de logs (logrotate en Linux)

---

## 🚀 Deployment

### 1. Subir Código

-   [ ] Merge a rama `main` si todo está correcto
    ```bash
    git checkout main
    git merge refactor/backend-improvements
    git push origin main
    ```
-   [ ] Push del tag de versión
    ```bash
    git push origin v2.0.0
    ```

### 2. En Servidor de Producción

-   [ ] Pull del código más reciente
    ```bash
    git pull origin main
    ```
-   [ ] Instalar/actualizar dependencias
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
-   [ ] Ejecutar migraciones
    ```bash
    php artisan migrate --force
    ```
-   [ ] Optimizar
    ```bash
    php artisan optimize
    ```
-   [ ] Reiniciar servicios

    ```bash
    # Si usas supervisor para queues
    sudo supervisorctl restart all

    # Si usas Apache
    sudo service apache2 restart

    # Si usas Nginx + PHP-FPM
    sudo service php8.1-fpm restart
    sudo service nginx restart
    ```

### 3. Verificación Post-Deployment

-   [ ] Verificar que la aplicación carga correctamente
-   [ ] Hacer una venta de prueba
-   [ ] Verificar logs de errores
-   [ ] Verificar que los reportes funcionen
-   [ ] Monitorear uso de CPU/memoria
-   [ ] Verificar tiempos de respuesta

---

## 🔄 Plan de Rollback

### Si algo sale mal:

1. **Restaurar Base de Datos**

    ```bash
    mysql -u root -p dbsistemaventas < backup_YYYYMMDD_HHMMSS.sql
    ```

2. **Revertir Código**

    ```bash
    git revert HEAD
    # O volver a tag anterior
    git checkout v1.x.x
    ```

3. **Limpiar Cachés**

    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan optimize:clear
    ```

4. **Reiniciar Servicios**
    ```bash
    sudo service apache2 restart
    # o el servidor web que uses
    ```

---

## 📊 Métricas a Monitorear

### Primeras 24 horas

-   [ ] Errores en logs (`storage/logs/laravel.log`)
-   [ ] Ventas procesadas correctamente
-   [ ] Tiempo de respuesta de la aplicación
-   [ ] Uso de memoria
-   [ ] Queries lentas (si tienes slow query log activado)

### Primera semana

-   [ ] Stock sincronizado correctamente
-   [ ] Reportes generándose sin errores
-   [ ] Caché funcionando (consultas más rápidas)
-   [ ] Logs de ventas y stock generándose
-   [ ] Sin conflictos de numeración de comprobantes

---

## 👥 Comunicación

### Antes del Deployment

-   [ ] Notificar al equipo sobre el deployment
-   [ ] Programar horario de menor uso (madrugada/fin de semana)
-   [ ] Preparar mensaje para usuarios si habrá downtime

### Durante el Deployment

-   [ ] Mantener comunicación con el equipo
-   [ ] Documentar cualquier issue encontrado

### Después del Deployment

-   [ ] Confirmar éxito del deployment
-   [ ] Capacitar al equipo sobre nuevas funcionalidades
-   [ ] Documentar lecciones aprendidas

---

## 📞 Contactos de Emergencia

-   **Desarrollador Backend**: ******\_******
-   **DBA**: ******\_******
-   **DevOps**: ******\_******
-   **Soporte Nivel 1**: ******\_******

---

## 📝 Notas Adicionales

-   **Duración estimada**: 1-2 horas
-   **Horario recomendado**: Madrugada o fin de semana
-   **Requiere downtime**: Mínimo (5-10 minutos)
-   **Reversible**: Sí, mediante backup y rollback

---

**Versión**: 2.0.0  
**Fecha**: ******\_******  
**Responsable**: ******\_******  
**Estado**: [ ] Pendiente [ ] En Progreso [ ] Completado [ ] Rollback
