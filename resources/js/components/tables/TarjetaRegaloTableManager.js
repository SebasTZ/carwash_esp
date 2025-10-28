// TarjetaRegaloTableManager.js
// Componente para gestionar la tabla de reporte de tarjetas de regalo

export default class TarjetaRegaloTableManager {
    constructor(tableSelector) {
        this.table = document.querySelector(tableSelector);
        if (!this.table) return;
        this.initSorting();
    }

    initSorting() {
        // Implementar ordenamiento simple por columnas si se requiere
        // Ejemplo: click en encabezado para ordenar
        const headers = this.table.querySelectorAll('th');
        headers.forEach((header, idx) => {
            header.addEventListener('click', () => {
                this.sortByColumn(idx);
            });
        });
    }

    sortByColumn(colIdx) {
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));
        rows.sort((a, b) => {
            const aText = a.children[colIdx].textContent.trim();
            const bText = b.children[colIdx].textContent.trim();
            return aText.localeCompare(bText, undefined, {numeric: true});
        });
        const tbody = this.table.querySelector('tbody');
        rows.forEach(row => tbody.appendChild(row));
    }
}
