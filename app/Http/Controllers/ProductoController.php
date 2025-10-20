<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Repositories\CaracteristicaRepository;
use App\Repositories\ProductoRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function __construct(
        private CaracteristicaRepository $caracteristicaRepo,
        private ProductoRepository $productoRepo
    ) {
        $this->middleware('permission:ver-producto|crear-producto|editar-producto|eliminar-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
    }
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
    public function store(\Illuminate\Http\Request $request)
    {
        try {
            DB::beginTransaction();
            //Tabla producto
            $producto = new Producto();
            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->file('img_path'));
            } else {
                $name = null;
            }

            $producto->fill([
                'codigo' => $request['codigo'],
                'nombre' => $request['nombre'],
                'descripcion' => $request['descripcion'],
                'fecha_vencimiento' => $request['fecha_vencimiento'],
                'img_path' => $name,
                'marca_id' => $request['marca_id'],
                'presentacione_id' => $request['presentacione_id'],
                'es_servicio_lavado' => isset($request['es_servicio_lavado']) ? true : false,
                'precio_venta' => isset($request['es_servicio_lavado']) ? $request['precio_venta'] : null
            ]);

            $producto->save();

            //Tabla categoría producto
            $categorias = $request['categorias'];
            $producto->categorias()->attach($categorias);


            DB::commit();
            
            // Limpiar caché de productos
            $this->productoRepo->limpiarCache();
            
        } catch (Exception $e) {
            DB::rollBack();
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
    public function update(\Illuminate\Http\Request $request, Producto $producto)
    {
        try{
            DB::beginTransaction();

            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->file('img_path'));

                //Eliminar si existiese una imagen
                if(Storage::disk('public')->exists('productos/'.$producto->img_path)){
                    Storage::disk('public')->delete('productos/'.$producto->img_path);
                }

            } else {
                $name = $producto->img_path;
            }

            $producto->fill([
                'codigo' => $request['codigo'],
                'nombre' => $request['nombre'],
                'descripcion' => $request['descripcion'],
                'fecha_vencimiento' => $request['fecha_vencimiento'],
                'img_path' => $name,
                'marca_id' => $request['marca_id'],
                'presentacione_id' => $request['presentacione_id'],
                'es_servicio_lavado' => isset($request['es_servicio_lavado']) ? true : false,
                'precio_venta' => isset($request['es_servicio_lavado']) ? $request['precio_venta'] : null
            ]);

            $producto->save();

            //Tabla categoría producto
            $categorias = $request['categorias'];
            $producto->categorias()->sync($categorias);

            DB::commit();
            
            // Limpiar caché de productos
            $this->productoRepo->limpiarCache();
            
        }catch(Exception $e){
            DB::rollBack();
        }

        return redirect()->route('productos.index')->with('success','Producto editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $producto = Producto::find($id);
        if ($producto->estado == 1) {
            Producto::where('id', $producto->id)
                ->update([
                    'estado' => 0
                ]);
            $message = 'Producto eliminado';
        } else {
            Producto::where('id', $producto->id)
                ->update([
                    'estado' => 1
                ]);
            $message = 'Producto restaurado';
        }

        return redirect()->route('productos.index')->with('success', $message);
    }
}
