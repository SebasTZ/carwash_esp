export default class DynamicTable {
    constructor({ el, columns = [], data = [] }) {
        this.container = typeof el === 'string' ? document.querySelector(el) : el;
        this.columns = columns;
        this.data = data;
        this.render();
    }

    render() {
        if (!this.container) return;
        if (!this.data.length) {
            this.container.innerHTML = '<div class="alert alert-info">No hay datos para mostrar.</div>';
            return;
        }
        const table = document.createElement('table');
        table.className = 'table table-bordered table-striped';
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');
        // Encabezados
        const trHead = document.createElement('tr');
        this.columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.label || col;
            trHead.appendChild(th);
        });
        thead.appendChild(trHead);
        // Filas
        this.data.forEach(row => {
            const tr = document.createElement('tr');
            this.columns.forEach(col => {
                const td = document.createElement('td');
                td.textContent = row[col.key || col] || '';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        table.appendChild(thead);
        table.appendChild(tbody);
        this.container.innerHTML = '';
        this.container.appendChild(table);
    }
}
