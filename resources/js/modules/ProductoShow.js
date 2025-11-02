/**
 * ProductoShow.js
 * Módulo para mostrar los detalles de un producto en la vista show
 * Basado en CompraShow.js
 */

import { formatCurrency } from '/js/utils/formatters.js';

class ProductoShow {
    constructor({ elementId = 'productoShowContainer', producto = {} }) {
        console.log('[ProductoShow] Data recibida:', { producto });

        this.elementId = elementId;
        this.producto = producto;
        this.init();
    }

    /**
     * Renderizar la vista del producto
     */
    render() {
        const producto = this.producto;

        // Determinar estado
        const estadoClass = producto.estado === 1 ? 'bg-success' : 'bg-danger';
        const estadoText = producto.estado === 1 ? 'Activo' : 'Eliminado';

        // Renderizar categorías
        const categoriasHTML = Array.isArray(producto.categorias)
            ? producto.categorias.map(cat => `<span class="badge bg-info me-1">${cat.nombre}</span>`).join('')
            : '<span class="text-muted">Sin categorías</span>';

        // Renderizar imagen
        const imagenHTML = producto.img_path
            ? `<img src="${producto.img_path}" alt="${producto.nombre}" class="img-fluid rounded" style="max-width: 300px;">`
            : '<div class="alert alert-light">Sin imagen</div>';

        return `
            <div class="container-fluid px-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="mb-0">${producto.nombre}</h2>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        ${imagenHTML}
                                    </div>
                                    <div class="col-md-8">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th width="40%">Código:</th>
                                                    <td>${producto.codigo || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <th>Marca:</th>
                                                    <td>${producto.marca?.nombre || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <th>Presentación:</th>
                                                    <td>${producto.presentacione?.nombre || '-'}</td>
                                                </tr>
                                                <tr>
                                                    <th>Stock:</th>
                                                    <td>
                                                        ${
                                                            parseInt(producto.stock) <= 0
                                                                ? `<span class="badge bg-danger">${producto.stock}</span>`
                                                                : parseInt(producto.stock) <= 10
                                                                ? `<span class="badge bg-warning">${producto.stock}</span>`
                                                                : `<span class="badge bg-success">${producto.stock}</span>`
                                                        }
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Precio de Venta:</th>
                                                    <td><strong>S/ ${parseFloat(producto.precio_venta).toFixed(2)}</strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Estado:</th>
                                                    <td><span class="badge ${estadoClass}">${estadoText}</span></td>
                                                </tr>
                                                ${
                                                    producto.es_servicio_lavado
                                                        ? `<tr>
                                                            <th>Tipo:</th>
                                                            <td><span class="badge bg-primary">Servicio de Lavado</span></td>
                                                        </tr>`
                                                        : ''
                                                }
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                ${
                                    producto.descripcion
                                        ? `
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>Descripción:</h5>
                                            <p class="text-muted">${producto.descripcion}</p>
                                        </div>
                                    </div>
                                    `
                                        : ''
                                }

                                ${
                                    producto.fecha_vencimiento
                                        ? `
                                    <div class="row">
                                        <div class="col-12">
                                            <h5>Fecha de Vencimiento:</h5>
                                            <p>${new Date(producto.fecha_vencimiento).toLocaleDateString('es-PE')}</p>
                                        </div>
                                    </div>
                                    `
                                        : ''
                                }

                                <div class="row">
                                    <div class="col-12">
                                        <h5>Categorías:</h5>
                                        <div>${categoriasHTML}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Acciones</h5>
                            </div>
                            <div class="card-body">
                                <a href="/productos/${producto.id}/edit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="/productos" class="btn btn-secondary w-100 mb-2">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                                <button class="btn btn-danger w-100" onclick="if(confirm('¿Está seguro?')) { document.getElementById('deleteForm').submit(); }">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="deleteForm" action="/productos/${producto.id}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        `;
    }

    /**
     * Inicializar el componente
     */
    init() {
        const container = document.getElementById(this.elementId);
        if (!container) {
            console.warn('[ProductoShow] No se encontró el contenedor:', this.elementId);
            return;
        }

        container.innerHTML = this.render();
        console.log('[ProductoShow] Componente inicializado correctamente');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('[ProductoShow] DOMContentLoaded - verificando si se necesita inicializar');
});

export default ProductoShow;
