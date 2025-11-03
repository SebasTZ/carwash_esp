<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Capacidad Máxima del Estacionamiento
    |--------------------------------------------------------------------------
    |
    | Define la cantidad máxima de vehículos que pueden estar estacionados
    | simultáneamente. Este valor se puede ajustar según la capacidad real
    | del estacionamiento.
    |
    */
    'capacidad_maxima' => env('ESTACIONAMIENTO_CAPACIDAD_MAXIMA', 50), // Cambia el 20 al número que necesites

    /*
    |--------------------------------------------------------------------------
    | Tarifa por Hora por Defecto
    |--------------------------------------------------------------------------
    |
    | Tarifa predeterminada por hora de estacionamiento (en soles)
    |
    */
    'tarifa_hora_default' => env('ESTACIONAMIENTO_TARIFA_HORA', 5.00),

    /*
    |--------------------------------------------------------------------------
    | Tiempo Mínimo de Cobro (minutos)
    |--------------------------------------------------------------------------
    |
    | Tiempo mínimo que se cobra aunque el vehículo permanezca menos tiempo
    |
    */
    'tiempo_minimo_cobro' => env('ESTACIONAMIENTO_TIEMPO_MINIMO', 60),
];
