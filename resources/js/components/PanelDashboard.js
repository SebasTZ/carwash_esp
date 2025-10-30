// PanelDashboard.js
// Componente JS para renderizar el dashboard del panel de control

export const PanelDashboard = {
    init({ el, data, userPermissions }) {
            const root = document.querySelector(el);
            if (!root || !data) return;
            root.innerHTML = '';
            // Verificar que data.cards sea un array
            const cards = (data && Array.isArray(data.cards)) ? data.cards : [];
            // Crear el contenedor de filas responsivo
            const rowDiv = document.createElement('div');
            rowDiv.className = 'row g-4';
            cards.forEach(card => {
                if (card.permission && !userPermissions.includes(card.permission)) return;
                const cardDiv = document.createElement('div');
                cardDiv.className = `col-xl-3 col-md-6`;
                cardDiv.innerHTML = `
                    <div class="dashboard-card ${card.bg} text-white h-100" style="cursor:pointer" onclick="window.location.href='${card.url}'">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="stat-label mb-1">${card.label}</p>
                                    <p class="stat-value">${card.value}</p>
                                </div>
                                <i class="${card.icon} stat-icon"></i>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a class="text-white" href="${card.url}">
                                <span>${card.footer}</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                `;
                rowDiv.appendChild(cardDiv);
            });
            root.appendChild(rowDiv);
    }
};

window.PanelDashboard = PanelDashboard;
