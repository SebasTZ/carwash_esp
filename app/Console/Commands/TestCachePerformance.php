<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\ProductoRepository;
use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestCachePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:test-performance {--productos=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el rendimiento del cache de productos en entorno real';

    protected ProductoRepository $repository;

    public function __construct(ProductoRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cantidadProductos = (int) $this->option('productos');
        
        $this->info("🧪 Probando rendimiento del cache con {$cantidadProductos} productos...");
        $this->newLine();

        // Limpiar cache
        Cache::flush();
        $this->info('🧹 Cache limpiado');

        // Medir sin cache (primera llamada)
        $this->info('📊 Midiendo sin cache...');
        $inicio = microtime(true);
        $queryCount = DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
        DB::enableQueryLog();
        
        $productos1 = $this->repository->obtenerParaVenta();
        
        $tiempoSinCache = (microtime(true) - $inicio) * 1000;
        $queries1 = count(DB::getQueryLog()) - $queryCount;

        // Medir con cache (segunda llamada)
        $this->info('⚡ Midiendo con cache...');
        $inicio = microtime(true);
        $queryCount = DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
        
        $productos2 = $this->repository->obtenerParaVenta();
        
        $tiempoConCache = (microtime(true) - $inicio) * 1000;
        $queries2 = count(DB::getQueryLog()) - $queryCount;

        DB::disableQueryLog();

        // Mostrar resultados
        $this->newLine();
        $this->line('================================================');
        $this->info('🚀 RESULTADOS DEL TEST DE CACHE');
        $this->line('================================================');
        $this->line("Entorno: " . config('app.env'));
        $this->line("Driver Cache: " . config('cache.default'));
        $this->line("Base de Datos: " . config('database.default'));
        $this->line("Productos encontrados: " . $productos1->count());
        $this->newLine();
        $this->line("📈 SIN CACHE:");
        $this->line("   Tiempo: " . round($tiempoSinCache, 2) . " ms");
        $this->line("   Queries: {$queries1}");
        $this->newLine();
        $this->line("⚡ CON CACHE:");
        $this->line("   Tiempo: " . round($tiempoConCache, 2) . " ms");
        $this->line("   Queries: {$queries2}");
        $this->newLine();
        
        $mejora = (($tiempoSinCache - $tiempoConCache) / $tiempoSinCache) * 100;
        $reduccionQueries = (($queries1 - $queries2) / max($queries1, 1)) * 100;
        
        if ($mejora > 0) {
            $this->info("✅ MEJORA: " . round($mejora, 1) . "% más rápido");
        } else {
            $this->warn("⚠️  OVERHEAD: " . round(abs($mejora), 1) . "% más lento");
        }
        
        $this->info("📊 REDUCCIÓN QUERIES: " . round($reduccionQueries, 1) . "%");
        $this->line('================================================');
        
        if (config('cache.default') === 'array') {
            $this->newLine();
            $this->comment('💡 RECOMENDACIÓN: Para mejores resultados, configura');
            $this->comment('   Redis o Memcached en producción.');
        }

        return Command::SUCCESS;
    }
}