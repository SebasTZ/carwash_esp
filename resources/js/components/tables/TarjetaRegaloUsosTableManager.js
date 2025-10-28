// TarjetaRegaloUsosTableManager.js
// Componente para gestionar la tabla de historial de usos de tarjetas de regalo

export default class TarjetaRegaloUsosTableManager {
    constructor(tableSelector) {
        this.table = document.querySelector(tableSelector);
        if (!this.table) return;
        this.initSorting();
    }

    initSorting() {
        // Ordenamiento por columnas
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
