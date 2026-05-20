import Chart from 'chart.js/auto';

const chartText = '#B0B0B0';
const chartGrid = 'rgba(106, 150, 255, 0.12)';

function initCharts() {
    const payload = window.__ADMIN_CHART_DATA;
    if (!payload) {
        return;
    }

    Chart.defaults.color = chartText;
    Chart.defaults.borderColor = chartGrid;

    const activityEl = document.getElementById('activityChart');
    if (activityEl) {
        new Chart(activityEl, {
            type: 'line',
            data: {
                labels: payload.months ?? [],
                datasets: [
                    {
                        label: 'reports',
                        data: payload.reportsData ?? [],
                        borderColor: '#6A96FF',
                        backgroundColor: 'rgba(106, 150, 255, 0.12)',
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'maintenance',
                        data: payload.maintenanceData ?? [],
                        borderColor: '#FFA500',
                        backgroundColor: 'rgba(255, 165, 0, 0.12)',
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'resolved',
                        data: payload.resolvedData ?? [],
                        borderColor: '#56FF8B',
                        backgroundColor: 'rgba(86, 255, 139, 0.12)',
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 16 },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: chartGrid },
                    },
                    x: {
                        grid: { color: chartGrid },
                    },
                },
            },
        });
    }

    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        const counts = payload.statusPieCounts ?? [0, 0, 0];
        const sum = counts.reduce((a, b) => a + Number(b), 0);

        if (sum === 0) {
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: ['No data yet'],
                    datasets: [
                        {
                            data: [1],
                            backgroundColor: ['#3d4459'],
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                        tooltip: { enabled: false },
                    },
                },
            });
        } else {
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: ['Resolved', 'In Progress', 'Pending'],
                    datasets: [
                        {
                            data: counts,
                            backgroundColor: ['#56FF8B', '#FFA500', '#FF5B5B'],
                            borderWidth: 0,
                            hoverOffset: 6,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                    },
                },
            });
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
