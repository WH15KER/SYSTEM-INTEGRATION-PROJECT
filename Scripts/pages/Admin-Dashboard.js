document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // Initialize charts
    initUserActivityChart();
    initSystemHealthChart();
    initUserDistributionChart();

    // Date range selector
    const dashboardRange = document.getElementById('dashboardRange');
    if (dashboardRange) {
        dashboardRange.addEventListener('change', function() {
            refreshDashboardData(this.value);
        });
    }

    // Refresh button
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('refreshing');
            setTimeout(() => {
                this.classList.remove('refreshing');
                refreshDashboardData(dashboardRange.value);
            }, 1000);
        });
    }

    // Chart type buttons
    document.querySelectorAll('.chart-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const chartContainer = this.closest('.chart-container');
            const chartType = this.getAttribute('data-chart');
            const dataType = this.getAttribute('data-type');
            
            // Update active button
            chartContainer.querySelectorAll('.chart-action-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update chart based on selection
            updateChart(chartType, dataType);
        });
    });

    // System backup button
    const systemBackupBtn = document.getElementById('systemBackupBtn');
    if (systemBackupBtn) {
        systemBackupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to initiate a system backup?')) {
                alert('System backup has been initiated. You will receive a notification when complete.');
                // In a real application, this would trigger an API call
            }
        });
    }

    function refreshDashboardData(range) {
        console.log(`Refreshing dashboard data for range: ${range}`);
        // In a real application, this would fetch new data from the server
        // For now, we'll just simulate a refresh
        document.querySelectorAll('.card-value').forEach(el => {
            const currentValue = parseInt(el.textContent.replace(/,/g, ''));
            const newValue = currentValue + Math.floor(Math.random() * 10) - 3;
            el.textContent = newValue.toLocaleString();
        });
    }

    function updateChart(chartType, dataType) {
        console.log(`Updating ${chartType} chart to show ${dataType} data`);
        // In a real application, this would update the chart with new data
    }

    function initUserActivityChart() {
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Active Users',
                    data: [120, 190, 170, 210, 240, 180, 150],
                    backgroundColor: 'rgba(0, 198, 152, 0.1)',
                    borderColor: 'rgba(0, 198, 152, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Tests Conducted',
                    data: [80, 120, 140, 160, 180, 130, 100],
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function initSystemHealthChart() {
        const ctx = document.getElementById('systemHealthChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Uptime', 'Performance', 'Security', 'Database', 'Network'],
                datasets: [{
                    label: 'Health Score',
                    data: [95, 88, 92, 85, 90],
                    backgroundColor: [
                        'rgba(0, 198, 152, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 198, 152, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function initUserDistributionChart() {
        const ctx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Patients', 'Doctors', 'Nurses', 'Admins', 'Others'],
                datasets: [{
                    data: [62, 18, 12, 5, 3],
                    backgroundColor: [
                        'rgba(0, 198, 152, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 198, 152, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
});