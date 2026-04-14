<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Repositories\CaracteristicaRepository;
use App\Repositories\ProductoRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function __construct(
        private CaracteristicaRepository $caracteristicaRepo,
        private ProductoRepository $productoRepo
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::with(['categorias.caracteristica','marca.caracteristica','presentacione.caracteristica'])->latest()->paginate(15);
    
        return view('producto.index',compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('producto.create', [
            'marcas' => $this->caracteristicaRepo->obtenerMarcasActivas(),
            'presentaciones' => $this->caracteristicaRepo->obtenerPresentacionesActivas(),
            'categorias' => $this->caracteristicaRepo->obtenerCategoriasActivas(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($request, $validated) {
                $producto = new Producto();

                if ($request->hasFile('img_path')) {
                    $name = $producto->handleUploadImage($request->file('img_path'));
                } else {
                    $name = null;
                }

                $esServicioLavado = $request->boolean('es_servicio_lavado');

                $producto->fill([
                    'codigo' => $validated['codigo'],
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                    'img_path' => $name,
                    'marca_id' => $validated['marca_id'],
                    'presentacione_id' => $validated['presentacione_id'],
                    'es_servicio_lavado' => $esServicioLavado,
                    'precio_venta' => $esServicioLavado ? ($validated['precio_venta'] ?? null) : null,
                ]);

                $producto->save();
                $producto->categorias()->attach($validated['categorias']);
            });
            
            // Limpiar caché de productos y características
            $this->productoRepo->limpiarCache();
            $this->caracteristicaRepo->limpiarCache();
            
        } catch (Exception $e) {
            Log::error('Error al registrar producto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo registrar el producto. Intente nuevamente.');
        }

        return redirect()->route('productos.index')->with('success', 'Producto registrado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        return view('producto.edit', [
            'producto' => $producto,
            'marcas' => $this->caracteristicaRepo->obtenerMarcasActivas(),
            'presentaciones' => $this->caracteristicaRepo->obtenerPresentacionesActivas(),
            'categorias' => $this->caracteristicaRepo->obtenerCategoriasActivas(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $validated = $request->validated();

        try{
            DB::transaction(function () use ($request, $validated, $producto) {
                if ($request->hasFile('img_path')) {
                    $name = $producto->handleUploadImage($request->file('img_path'));

                    //Eliminar si existiese una imagen
                    if(Storage::disk('public')->exists('productos/'.$producto->img_path)){
                        Storage::disk('public')->delete('productos/'.$producto->img_path);
                    }

                } else {
                    $name = $producto->img_path;
                }

                $esServicioLavado = $request->boolean('es_servicio_lavado');

                $producto->fill([
                    'codigo' => $validated['codigo'],
                    'nombre' => $validated['nombre'],
                    'descripcion' => $validated['descripcion'] ?? null,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                    'img_path' => $name,
                    'marca_id' => $validated['marca_id'],
                    'presentacione_id' => $validated['presentacione_id'],
                    'es_servicio_lavado' => $esServicioLavado,
                    'precio_venta' => $esServicioLavado ? ($validated['precio_venta'] ?? null) : null,
                ]);

                $producto->save();
                $producto->categorias()->sync($validated['categorias']);
            });
            
            // Limpiar caché de productos y características
            $this->productoRepo->limpiarCache();
            $this->caracteristicaRepo->limpiarCache();
            
        }catch(Exception $e){
            Log::error('Error al actualizar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el producto. Intente nuevamente.');
        }

        return redirect()->route('productos.index')->with('success','Producto editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        if ($producto->estado == 1) {
            $producto->update(['estado' => 0]);
            $message = 'Producto eliminado';
        } else {
            $producto->update(['estado' => 1]);
            $message = 'Producto restaurado';
        }

        return redirect()->route('productos.index')->with('success', $message);
    }
}
