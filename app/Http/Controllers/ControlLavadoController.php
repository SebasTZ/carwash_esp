<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ControlLavadoService;
use App\Repositories\ControlLavadoRepository;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Exports\ControlLavadoExport;
use App\Exceptions\LavadoYaIniciadoException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ControlLavadoController extends Controller
{
    protected $controlLavadoService;
    protected $controlLavadoRepository;

    public function __construct(
        ControlLavadoService $controlLavadoService,
        ControlLavadoRepository $controlLavadoRepository
    ) {
        $this->controlLavadoService = $controlLavadoService;
        $this->controlLavadoRepository = $controlLavadoRepository;

        $this->middleware('permission:ver-control-lavado|crear-control-lavado|editar-control-lavado|eliminar-control-lavado', ['only' => ['index']]);
        $this->middleware('permission:crear-control-lavado', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-control-lavado', ['only' => ['edit', 'update', 'asignarLavador', 'inicioLavado', 'finLavado', 'inicioInterior', 'finInterior']]);
        $this->middleware('permission:eliminar-control-lavado', ['only' => ['destroy']]);
        $this->middleware('permission:reporte-diario-lavado', ['only' => ['reporteDiario']]);
        $this->middleware('permission:reporte-semanal-lavado', ['only' => ['reporteSemanal']]);
        $this->middleware('permission:reporte-mensual-lavado', ['only' => ['reporteMensual']]);
        $this->middleware('permission:reporte-personalizado-lavado', ['only' => ['reportePersonalizado']]);
        $this->middleware('permission:exportar-reporte-lavado', ['only' => ['exportDiario', 'exportSemanal', 'exportMensual', 'exportPersonalizado']]);
    }

    public function index(Request $request)
    {
        $filtros = $request->only(['lavador_id', 'tipo_vehiculo_id', 'estado', 'fecha']);
        $lavados = $this->controlLavadoService->obtenerLavadosConFiltros($filtros, 15);

        $lavadores = Lavador::where('estado', 'activo')->get();
        $tiposVehiculo = TipoVehiculo::where('estado', 'activo')->get();

        return view('control.lavados', compact('lavados', 'lavadores', 'tiposVehiculo'));
    }

    public function show($id)
    {
        try {
            $lavado = $this->controlLavadoService->obtenerLavadoConRelaciones($id, [
                'venta', 
                'cliente', 
                'auditoriaLavadores.lavadorAnterior', 
                'auditoriaLavadores.lavadorNuevo'
            ]);

            return view('control.show', compact('lavado'));
        } catch (\Exception $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', 'Lavado no encontrado.');
        }
    }

    public function destroy($lavado)
    {
        try {
            $this->controlLavadoService->eliminarLavado($lavado, Auth::id());

            return redirect()
                ->route('control.lavados')
                ->with('success', 'Registro de lavado eliminado correctamente.');
        } catch (\Exception $e) {
            Log::channel('lavados')->error('Error al eliminar lavado', [
                'lavado_id' => $lavado,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('control.lavados')
                ->with('error', 'Error al eliminar el lavado.');
        }
    }

    public function asignarLavador(Request $request, $lavado)
    {
        $request->validate([
            'lavador_id' => 'required|exists:lavadores,id',
            'tipo_vehiculo_id' => 'required|exists:tipos_vehiculo,id',
        ]);

        try {
            $this->controlLavadoService->asignarLavador(
                lavadoId: $lavado,
                lavadorId: $request->lavador_id,
                tipoVehiculoId: $request->tipo_vehiculo_id,
                motivo: $request->motivo,
                usuarioId: Auth::id()
            );

            return redirect()
                ->route('control.lavados')
                ->with('success', 'Lavador y tipo de vehículo asignados correctamente.');

        } catch (LavadoYaIniciadoException $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', $e->getMessage());

        } catch (\Exception $e) {
            Log::channel('lavados')->error('Error al asignar lavador', [
                'lavado_id' => $lavado,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('control.lavados')
                ->with('error', 'Ocurrió un error al asignar el lavador.');
        }
    }

    public function inicioLavado(Request $request, $id)
    {
        if ($request->confirmar != 'si') {
            return redirect()
                ->route('control.lavados')
                ->with('confirmar_inicio', $id);
        }

        try {
            $this->controlLavadoService->iniciarLavado($id, Auth::id());

            return redirect()
                ->route('control.lavados')
                ->with('success', 'Lavado iniciado correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', $e->getMessage());
        }
    }

    public function finLavado($id)
    {
        try {
            $this->controlLavadoService->finalizarLavado($id);

            return redirect()->route('control.lavados');

        } catch (\Exception $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', $e->getMessage());
        }
    }

    public function inicioInterior($id)
    {
        try {
            $this->controlLavadoService->iniciarInterior($id);

            return redirect()->route('control.lavados');

        } catch (\Exception $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', $e->getMessage());
        }
    }

    public function finInterior($id)
    {
        try {
            $this->controlLavadoService->finalizarInterior($id);

            return redirect()->route('control.lavados');

        } catch (\Exception $e) {
            return redirect()
                ->route('control.lavados')
                ->with('error', $e->getMessage());
        }
    }

    public function exportDiario()
    {
        $lavados = $this->controlLavadoRepository->getToday();

        return Excel::download(
            new ControlLavadoExport($lavados), 
            'control_lavado_diario.xlsx'
        );
    }

    public function exportSemanal()
    {
        $lavados = $this->controlLavadoRepository->getThisWeek();

        return Excel::download(
            new ControlLavadoExport($lavados), 
            'control_lavado_semanal.xlsx'
        );
    }

    public function exportMensual()
    {
        $lavados = $this->controlLavadoRepository->getThisMonth();

        return Excel::download(
            new ControlLavadoExport($lavados), 
            'control_lavado_mensual.xlsx'
        );
    }

    public function exportPersonalizado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $lavados = $this->controlLavadoRepository->getByDateRange($fechaInicio, $fechaFin);

        return Excel::download(
            new ControlLavadoExport($lavados), 
            "control_lavado_{$fechaInicio}_a_{$fechaFin}.xlsx"
        );
    }
}