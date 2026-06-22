import Chart from 'chart.js/auto';

function createProgressChart(canvasId, value, max, label, color) {
    const percentage = max > 0 ? (value / max) * 100 : 0;
    const data = {
        datasets: [{
            data: [percentage, 100 - percentage],
            backgroundColor: [color, '#e9ecef'],
            borderWidth: 0,
            hoverBackgroundColor: [color, '#e9ecef'],
        }]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                enabled: false
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    };

    const ctx = document.getElementById(canvasId).getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: options
    });

    // Set the text inside the chart
    const chartText = document.getElementById(canvasId + '-text');
    if (chartText) {
        chartText.innerHTML = `<div class="chart-value">${value}</div><div class="chart-label">${label}</div>`;
    }
}

window.createProgressChart = createProgressChart;
