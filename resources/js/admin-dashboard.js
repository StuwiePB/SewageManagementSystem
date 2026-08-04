import Chart from 'chart.js/auto';

const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
const chartText = isDark ? '#8FA3A8' : '#6B7C82';
const chartGrid = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(16, 38, 46, 0.08)';
const chartColors = {
    flow: '#2E7D8F',
    accent: '#F0A202',
    ok: '#3E8E6E',
    alert: '#C2453D',
    empty: isDark ? '#22383E' : '#DDE3E1',
};

function initCharts() {
    const payload = window.__ADMIN_CHART_DATA;
    if (!payload) {
        return;
    }

    Chart.defaults.color = chartText;
    Chart.defaults.borderColor = chartGrid;
    Chart.defaults.font.family = "'Instrument Sans', system-ui, sans-serif";

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
                        borderColor: chartColors.flow,
                        backgroundColor: 'rgba(46, 125, 143, 0.12)',
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'maintenance',
                        data: payload.maintenanceData ?? [],
                        borderColor: chartColors.accent,
                        backgroundColor: 'rgba(240, 162, 2, 0.12)',
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'resolved',
                        data: payload.resolvedData ?? [],
                        borderColor: chartColors.ok,
                        backgroundColor: 'rgba(62, 142, 110, 0.12)',
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
                            backgroundColor: [chartColors.empty],
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
                            backgroundColor: [chartColors.ok, chartColors.accent, chartColors.alert],
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
