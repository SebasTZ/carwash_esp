# Ejemplos de Refactorización para Otros Controladores

## 📝 CONTROLADOR DE PRODUCTOS

### ❌ ANTES (Código Actual)

```php
public function create()
{
    $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
        ->select('marcas.id as id', 'c.nombre as nombre')
        ->where('c.estado', 1)
        ->get();

    $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
        ->select('presentaciones.id as id', 'c.nombre as nombre')
        ->where('c.estado', 1)
        ->get();

    $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
        ->select('categorias.id as id', 'c.nombre as nombre')
        ->where('c.estado', 1)
        ->get();

    return view('producto.create', compact('marcas', 'presentaciones', 'categorias'));
}
```

### ✅ DESPUÉS (Refactorizado)

```php
// app/Repositories/CaracteristicaRepository.php
class CaracteristicaRepository
{
    public function obtenerMarcasActivas()
    {
        return Cache::remember('marcas:activas', 3600, function() {
            return Marca::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($marca) => [
                    'id' => $marca->id,
                    'nombre' => $marca->caracteristica->nombre
                ]);
        });
    }

    public function obtenerPresentacionesActivas()
    {
        return Cache::remember('presentaciones:activas', 3600, function() {
            return Presentacione::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($pres) => [
                    'id' => $pres->id,
                    'nombre' => $pres->caracteristica->nombre
                ]);
        });
    }

    public function obtenerCategoriasActivas()
    {
        return Cache::remember('categorias:activas', 3600, function() {
            return Categoria::with('caracteristica')
                ->whereHas('caracteristica', fn($q) => $q->where('estado', 1))
                ->get()
                ->map(fn($cat) => [
                    'id' => $cat->id,
                    'nombre' => $cat->caracteristica->nombre
                ]);
        });
    }
}

// En ProductoController refactorizado
public function __construct(
    private CaracteristicaRepository $caracteristicaRepo
) {
    // middleware...
}

public function create()
{
    return view('producto.create', [
        'marcas' => $this->caracteristicaRepo->obtenerMarcasActivas(),
        'presentaciones' => $this->caracteristicaRepo->obtenerPresentacionesActivas(),
        'categorias' => $this->caracteristicaRepo->obtenerCategoriasActivas(),
    ]);
}
```

---

## 📝 CONTROLADOR DE CITAS

### ❌ ANTES

```php
public function store(Request $request)
{
    $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'fecha' => 'required|date|date_format:Y-m-d|after_or_equal:today',
        'hora' => 'required',
        'notas' => 'nullable|string|max:500',
    ]);

    $posicionCola = Cita::getNextQueuePosition($request->fecha);

    Cita::create([
        'cliente_id' => $request->cliente_id,
        'fecha' => $request->fecha,
        'hora' => $request->hora,
        'posicion_cola' => $posicionCola,
        'estado' => 'pendiente',
        'notas' => $request->notas,
    ]);

    return redirect()->route('citas.index')
        ->with('success', 'Cita registrada exitosamente');
}
```

### ✅ DESPUÉS

```php
// app/Http/Requests/StoreCitaRequest.php
class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-cita');
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation()
    {
        // Normalizar datos si es necesario
        if ($this->hora && strlen($this->hora) === 5) {
            $this->merge(['hora' => $this->hora]);
        }
    }
}

// app/Services/CitaService.php
class CitaService
{
    public function crear(array $data): Cita
    {
        return DB::transaction(function() use ($data) {
            $posicionCola = Cita::getNextQueuePosition($data['fecha']);

            $cita = Cita::create([
                'cliente_id' => $data['cliente_id'],
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'posicion_cola' => $posicionCola,
                'estado' => 'pendiente',
                'notas' => $data['notas'] ?? null,
            ]);

            // Notificar al cliente (opcional)
            // event(new CitaCreada($cita));

            return $cita;
        });
    }

    public function actualizar(Cita $cita, array $data): Cita
    {
        return DB::transaction(function() use ($cita, $data) {
            $actualizarPosicion = $data['fecha'] != $cita->fecha->format('Y-m-d');

            $cita->update([
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'posicion_cola' => $actualizarPosicion
                    ? Cita::getNextQueuePosition($data['fecha'])
                    : $cita->posicion_cola,
                'notas' => $data['notas'] ?? $cita->notas,
            ]);

            return $cita->fresh();
        });
    }
}

// En CitaController
public function __construct(private CitaService $citaService)
{
    // middleware...
}

public function store(StoreCitaRequest $request)
{
    try {
        $cita = $this->citaService->crear($request->validated());

        return redirect()
            ->route('citas.index')
            ->with('success', 'Cita registrada exitosamente');

    } catch (\Exception $e) {
        return back()
            ->with('error', 'Error al registrar la cita')
            ->withInput();
    }
}
```

---

## 📝 MEJORAS EN MODELOS

### Agregar Scopes Útiles

```php
// app/Models/Cliente.php
class Cliente extends Model
{
    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereHas('persona', fn($q) => $q->where('estado', 1));
    }

    public function scopeConFidelidad($query)
    {
        return $query->where('lavados_acumulados', '>=', 1);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->whereHas('persona', function($q) use ($termino) {
            $q->where('razon_social', 'LIKE', "%{$termino}%")
              ->orWhere('direccion', 'LIKE', "%{$termino}%");
        });
    }

    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return $this->persona->razon_social ?? 'Sin nombre';
    }

    public function getProgreseFidelidadAttribute(): int
    {
        return min(100, ($this->lavados_acumulados / 10) * 100);
    }
}

// Uso en controladores
$clientes = Cliente::activos()
    ->conFidelidad()
    ->buscar($request->buscar)
    ->paginate(15);
```

### Agregar Mutators

```php
// app/Models/Producto.php
class Producto extends Model
{
    // Mutators
    protected function codigo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtoupper($value),
        );
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucwords(strtolower($value)),
            set: fn ($value) => ucwords(strtolower($value)),
        );
    }

    // Accessors
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) return 'agotado';
        if ($this->stock <= ($this->stock_minimo ?? 10)) return 'bajo';
        return 'disponible';
    }

    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'agotado' => 'danger',
            'bajo' => 'warning',
            'disponible' => 'success',
        };
    }
}

// Uso en vistas Blade
<span class="badge badge-{{ $producto->stock_status_color }}">
    {{ $producto->stock_status }}
</span>
```

---

## 📝 OBSERVERS PARA EVENTOS

### Crear Observers

```php
// app/Observers/ProductoObserver.php
class ProductoObserver
{
    public function created(Producto $producto): void
    {
        Log::info("Producto creado: {$producto->nombre}", ['id' => $producto->id]);
        Cache::tags(['productos'])->flush();
    }

    public function updated(Producto $producto): void
    {
        if ($producto->wasChanged('stock')) {
            Log::info("Stock actualizado: {$producto->nombre}", [
                'anterior' => $producto->getOriginal('stock'),
                'nuevo' => $producto->stock,
            ]);
        }

        Cache::tags(['productos'])->flush();
        Cache::forget("producto_{$producto->id}_stock");
    }

    public function deleted(Producto $producto): void
    {
        // Soft delete en lugar de eliminar
        if (!$producto->isForceDeleting()) {
            $producto->update(['estado' => 0]);
        }

        Cache::tags(['productos'])->flush();
    }
}

// Registrar en AppServiceProvider
use App\Observers\ProductoObserver;
use App\Models\Producto;

public function boot(): void
{
    Producto::observe(ProductoObserver::class);
    Venta::observe(VentaObserver::class);
    Cliente::observe(ClienteObserver::class);
}
```

---

## 📝 QUERY SCOPES AVANZADOS

### Filtros Dinámicos

```php
// app/Models/Venta.php
class Venta extends Model
{
    public function scopeFiltrar($query, array $filtros)
    {
        return $query
            ->when($filtros['fecha_desde'] ?? null,
                fn($q, $fecha) => $q->where('fecha_hora', '>=', $fecha))
            ->when($filtros['fecha_hasta'] ?? null,
                fn($q, $fecha) => $q->where('fecha_hora', '<=', $fecha . ' 23:59:59'))
            ->when($filtros['cliente_id'] ?? null,
                fn($q, $id) => $q->where('cliente_id', $id))
            ->when($filtros['medio_pago'] ?? null,
                fn($q, $medio) => $q->where('medio_pago', $medio))
            ->when($filtros['min_total'] ?? null,
                fn($q, $min) => $q->where('total', '>=', $min))
            ->when($filtros['max_total'] ?? null,
                fn($q, $max) => $q->where('total', '<=', $max));
    }
}

// Uso en controlador
$ventas = Venta::filtrar($request->all())
    ->conRelaciones()
    ->paginate(15);
```

---

## 📝 COLLECTIONS CUSTOM

### Crear Collections Personalizadas

```php
// app/Collections/VentaCollection.php
use Illuminate\Database\Eloquent\Collection;

class VentaCollection extends Collection
{
    public function totalGeneral(): float
    {
        return $this->sum('total');
    }

    public function totalPorMedioPago(string $medio): float
    {
        return $this->where('medio_pago', $medio)->sum('total');
    }

    public function agruparPorFecha(): array
    {
        return $this->groupBy(function($venta) {
            return $venta->fecha_hora->format('Y-m-d');
        })->map(function($ventas, $fecha) {
            return [
                'fecha' => $fecha,
                'cantidad' => $ventas->count(),
                'total' => $ventas->sum('total'),
            ];
        })->values()->all();
    }
}

// En el modelo Venta
public function newCollection(array $models = []): VentaCollection
{
    return new VentaCollection($models);
}

// Uso
$ventas = Venta::delDia()->get();
$total = $ventas->totalGeneral();
$totalEfectivo = $ventas->totalPorMedioPago('efectivo');
$resumen = $ventas->agruparPorFecha();
```

---

## 📝 VALIDATION RULES PERSONALIZADAS

### Crear Reglas Reutilizables

```php
// app/Rules/StockSuficiente.php
class StockSuficiente implements Rule
{
    public function __construct(private int $cantidad) {}

    public function passes($attribute, $value): bool
    {
        $producto = Producto::find($value);

        if (!$producto || $producto->es_servicio_lavado) {
            return true;
        }

        return $producto->stock >= $this->cantidad;
    }

    public function message(): string
    {
        return 'Stock insuficiente para el producto seleccionado.';
    }
}

// Uso en Request
public function rules(): array
{
    return [
        'producto_id' => ['required', new StockSuficiente($this->cantidad)],
        'cantidad' => ['required', 'integer', 'min:1'],
    ];
}
```

---

## 📝 COMMAND PATTERN PARA TAREAS COMPLEJAS

### Crear Commands

```php
// app/Actions/ProcesarCompra.php
class ProcesarCompra
{
    public function __construct(
        private StockService $stockService,
        private ComprobanteService $comprobanteService
    ) {}

    public function execute(array $data): Compra
    {
        return DB::transaction(function() use ($data) {
            $compra = $this->crearCompra($data);
            $this->procesarProductos($compra, $data);

            return $compra;
        });
    }

    private function crearCompra(array $data): Compra
    {
        return Compra::create([
            'fecha_hora' => now(),
            'impuesto' => $data['impuesto'] ?? 0,
            'numero_comprobante' => $data['numero_comprobante'],
            'total' => $data['total'],
            'proveedore_id' => $data['proveedore_id'],
            'comprobante_id' => $data['comprobante_id'],
        ]);
    }

    private function procesarProductos(Compra $compra, array $data): void
    {
        // Lógica de productos...
    }
}

// Uso en controlador
public function store(StoreCompraRequest $request, ProcesarCompra $action)
{
    try {
        $compra = $action->execute($request->validated());
        return redirect()->route('compras.show', $compra);
    } catch (\Exception $e) {
        return back()->withError($e->getMessage());
    }
}
```

---

## 🎯 RESUMEN DE PATRONES A APLICAR

1. **Service Layer** - Lógica de negocio
2. **Repository Pattern** - Consultas complejas
3. **Form Requests** - Validación
4. **Observers** - Eventos de modelos
5. **Scopes** - Queries reutilizables
6. **Collections** - Manipulación de datos
7. **DTOs** - Transferencia de datos
8. **Actions/Commands** - Tareas específicas
9. **Custom Rules** - Validaciones complejas
10. **Caché** - Optimización de performance

---

¡Sigue estos patrones para mantener un código limpio y escalable! 🚀
