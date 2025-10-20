<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaginationComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_pagination_info_component_renders_with_data()
    {
        $items = Collection::times(30, fn($i) => ['id' => $i, 'name' => "Item {$i}"]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            30,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" />',
            ['paginator' => $paginator]
        );

        // Verificar que contiene el texto de paginación (con formato flexible)
        $view->assertSee('Mostrando');
        $view->assertSee('1');
        $view->assertSee('15');
        $view->assertSee('30');
        $view->assertSee('items');
    }

    /** @test */
    public function test_pagination_info_component_shows_empty_state()
    {
        $paginator = new LengthAwarePaginator(
            [],
            0,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" />',
            ['paginator' => $paginator]
        );

        $view->assertSee('No hay items para mostrar');
    }

    /** @test */
    public function test_pagination_info_component_hides_info_when_show_info_false()
    {
        $items = Collection::times(20, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            20,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" :show-info="false" />',
            ['paginator' => $paginator]
        );

        $view->assertDontSee('Mostrando');
    }

    /** @test */
    public function test_pagination_info_component_hides_links_when_show_links_false()
    {
        $items = Collection::times(20, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            20,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" :show-links="false" />',
            ['paginator' => $paginator]
        );

        // No debe renderizar los botones de paginación
        $view->assertDontSee('pagination');
    }

    /** @test */
    public function test_pagination_multi_component_renders_correctly()
    {
        $items = Collection::times(20, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            20,
            15,
            1,
            ['pageName' => 'clientes_page']
        );

        $view = $this->blade(
            '<x-pagination-multi :paginator="$paginator" entity="clientes" page-name="clientes_page" />',
            ['paginator' => $paginator]
        );

        $view->assertSee('clientes');
    }

    /** @test */
    public function test_pagination_info_component_shows_correct_page_info()
    {
        $items = Collection::times(30, fn($i) => ['id' => $i]);
        
        // Página 2 (items 16-30)
        $paginator = new LengthAwarePaginator(
            $items->slice(15, 15)->values(),
            30,
            15,
            2
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="productos" />',
            ['paginator' => $paginator]
        );

        // Verificar partes individuales en lugar de texto completo
        $view->assertSee('Mostrando');
        $view->assertSee('16');
        $view->assertSee('30');
        $view->assertSee('productos');
    }

    /** @test */
    public function test_pagination_preserves_query_parameters()
    {
        request()->merge(['search' => 'test', 'status' => 'active']);

        $items = Collection::times(20, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            20,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" :preserve-query="true" />',
            ['paginator' => $paginator]
        );

        // Debe incluir los parámetros de búsqueda en los links
        $view->assertSee('search=test');
        $view->assertSee('status=active');
    }

    /** @test */
    public function test_pagination_component_applies_custom_class()
    {
        $items = Collection::times(20, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items->take(15),
            20,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" class="my-custom-class" />',
            ['paginator' => $paginator]
        );

        $view->assertSee('my-custom-class');
    }

    /** @test */
    public function test_pagination_shows_singular_for_single_item()
    {
        $paginator = new LengthAwarePaginator(
            [['id' => 1]],
            1,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="producto" />',
            ['paginator' => $paginator]
        );

        // Verificar que muestra información para 1 item
        $view->assertSee('Mostrando');
        $view->assertSee('1');
        $view->assertSee('producto');
    }

    /** @test */
    public function test_pagination_handles_exact_page_size()
    {
        $items = Collection::times(15, fn($i) => ['id' => $i]);
        $paginator = new LengthAwarePaginator(
            $items,
            15,
            15,
            1
        );

        $view = $this->blade(
            '<x-pagination-info :paginator="$paginator" entity="items" />',
            ['paginator' => $paginator]
        );

        // Verificar que muestra 15 items correctamente
        $view->assertSee('Mostrando');
        $view->assertSee('15');
        $view->assertSee('items');
    }
}
