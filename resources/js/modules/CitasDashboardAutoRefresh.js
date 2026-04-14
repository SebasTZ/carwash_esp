/**
 * CitasDashboardAutoRefresh
 * Gestiona el contador de auto-recarga del dashboard de citas.
 */

class CitasDashboardAutoRefresh {
    constructor(options = {}) {
        this.countdown = options.countdown ?? 60;
        this.countdownElement = document.getElementById(options.elementId || 'countdown');

        if (!this.countdownElement) {
            return;
        }

        this.start();
    }

    start() {
        this.update();
    }

    update() {
        this.countdownElement.textContent = this.countdown;

        if (this.countdown <= 0) {
            window.location.reload();
            return;
        }

        this.countdown -= 1;
        window.setTimeout(() => this.update(), 1000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.citasDashboardAutoRefresh = new CitasDashboardAutoRefresh();
});

export default CitasDashboardAutoRefresh;