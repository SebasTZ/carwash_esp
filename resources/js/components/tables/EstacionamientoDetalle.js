// EstacionamientoDetalle.js
// Componente JS para mostrar el detalle de estacionamiento

export const EstacionamientoDetalle = {
    init({ el, estacionamiento }) {
        const container = document.querySelector(el);
        if (!container || !estacionamiento) return;
        container.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h5>Detalle de Estacionamiento</h5>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> ${estacionamiento.id}</p>
                    <p><strong>Estado:</strong> ${estacionamiento.estado}</p>
                    <p><strong>Vehículo:</strong> ${estacionamiento.vehiculo}</p>
                    <p><strong>Fecha de ingreso:</strong> ${estacionamiento.fecha_ingreso}</p>
                    <p><strong>Fecha de salida:</strong> ${estacionamiento.fecha_salida || '-'}</p>
                </div>
            </div>
        `;
    }
};

window.EstacionamientoDetalle = EstacionamientoDetalle;
