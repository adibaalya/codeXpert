/**
 * public/js/weeklyChart.js
 */

document.addEventListener('DOMContentLoaded', function() {
    const chartCanvas = document.getElementById('weeklyChart');
    
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        
        // Read config or use defaults
        const config = window.dashboardConfig || { labels: [], data: [] };
        
        // 1. Define Colors (Use config if available, otherwise default to Learner Orange)
        const colorStart = config.colorStart || 'rgb(255, 87, 34)'; 
        const colorEnd = config.colorEnd || 'rgb(255, 167, 38)';

        const weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: config.labels,
                datasets: [{
                    label: 'Activity', // Generic label
                    data: config.data,
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        
                        // 2. Use the dynamic variables here
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, colorStart);
                        gradient.addColorStop(1, colorEnd);
                        return gradient;
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 0 },
                hover: { mode: null },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        borderRadius: 8,
                        titleColor: '#F9FAFB',
                        bodyColor: '#F9FAFB',
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Count: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#6B7280', font: { size: 12, weight: 600 } }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#6B7280', font: { size: 12, weight: 600 } }
                    }
                }
            }
        });
    }
});