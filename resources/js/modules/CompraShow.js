/**
 * CompraShow.js
 * Módulo para mostrar los detalles de una compra en la vista show
 * Basado en la lógica de show-old.blade.php y utilidades de CompraManager.js
 */

import { formatCurrency } from '@utils/formatters';

class CompraShow {
	constructor({ productos, impuesto }) {
		this.productos = productos || [];
		this.impuesto = parseFloat(impuesto) || 0;
		this.suma = 0;
		this.igv = 0;
		this.total = 0;
		this.init();
	}

	// Redondea a dos decimales
	round(num, decimales = 2) {
		const signo = (num >= 0 ? 1 : -1);
		num = num * signo;
		if (decimales === 0)
			return signo * Math.round(num);
		num = num.toString().split('e');
		num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
		num = num.toString().split('e');
		return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
	}

	calcularValores() {
		this.suma = this.productos.reduce((acc, item) => {
			return acc + (parseFloat(item.cantidad) * parseFloat(item.precio_compra));
		}, 0);
		this.suma = this.round(this.suma);
		this.igv = this.round(this.impuesto);
		this.total = this.round(this.suma + this.igv);
	}

	renderTotales() {
		document.getElementById('th-suma').innerHTML = formatCurrency(this.suma);
		document.getElementById('th-igv').innerHTML = formatCurrency(this.igv);
		document.getElementById('th-total').innerHTML = formatCurrency(this.total);
	}

	renderTabla() {
		const tbody = document.getElementById('tbody-productos');
		if (!tbody) return;
		tbody.innerHTML = '';
		this.productos.forEach(item => {
			const subtotal = this.round(item.cantidad * item.precio_compra);
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td>${item.nombre}</td>
				<td>${item.cantidad}</td>
				<td>${formatCurrency(item.precio_compra)}</td>
				<td>${formatCurrency(item.precio_venta)}</td>
				<td class="td-subtotal">${formatCurrency(subtotal)}</td>
			`;
			tbody.appendChild(tr);
		});
	}

	init() {
		this.calcularValores();
		this.renderTabla();
		this.renderTotales();
	}
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
	// Obtener datos desde el DOM (Blade los debe renderizar en data-attributes o JSON)
	const productosData = window.compraProductos || [];
	const impuesto = document.getElementById('input-impuesto')?.value || 0;
	console.log('[CompraShow] Data recibida:', { productos: productosData, impuesto });
	window.compraShow = new CompraShow({ productos: productosData, impuesto });
});

export default CompraShow;
