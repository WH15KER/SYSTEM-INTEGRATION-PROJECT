document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // Initialize date picker functionality
    const reportPeriod = document.getElementById('reportPeriod');
    const customRange = document.getElementById('customRange');
    
    reportPeriod.addEventListener('change', function() {
        if (this.value === 'custom') {
            customRange.style.display = 'flex';
            
            // Set default dates (today and 7 days ago)
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - 7);
            
            document.getElementById('endDate').valueAsDate = endDate;
            document.getElementById('startDate').valueAsDate = startDate;
        } else {
            customRange.style.display = 'none';
            loadReportData(this.value);
        }
    });
    
    document.getElementById('applyDateRange').addEventListener('click', function() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date cannot be after end date');
            return;
        }
        
        loadReportData('custom', startDate, endDate);
    });

    // Initialize summary cards with random data for demo
    updateSummaryCards();
    
    // Initialize charts
    const userActivityChart = initUserActivityChart();
    const roleDistributionChart = initRoleDistributionChart();
    const systemUsageChart = initSystemUsageChart();
    
    // Chart type toggle functionality
    document.querySelectorAll('.chart-action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const chartContainer = this.closest('.chart-container');
            const chartType = this.dataset.type;
            
            // Update active state
            chartContainer.querySelectorAll('.chart-action-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update the appropriate chart
            switch (this.dataset.chart) {
                case 'userActivity':
                    updateUserActivityChart(userActivityChart, chartType);
                    break;
                case 'roleDistribution':
                    updateRoleDistributionChart(roleDistributionChart, chartType);
                    break;
                case 'systemUsage':
                    updateSystemUsageChart(systemUsageChart, chartType);
                    break;
            }
        });
    });
    
    // Export button functionality
    document.querySelector('.export-btn').addEventListener('click', function() {
        const reportType = document.getElementById('reportType').value;
        const period = reportPeriod.value === 'custom' ? 
            `${document.getElementById('startDate').value} to ${document.getElementById('endDate').value}` : 
            reportPeriod.options[reportPeriod.selectedIndex].text;
        
        alert(`Exporting ${reportType} report for ${period}`);
        // In a real application, this would generate and download a report file
    });
    
    // Refresh button functionality
    document.querySelector('.refresh-btn').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing';
        
        // Simulate data refresh
        setTimeout(() => {
            updateSummaryCards();
            updateUserActivityChart(userActivityChart, 
                document.querySelector('[data-chart="userActivity"].active').dataset.type);
            updateRoleDistributionChart(roleDistributionChart, 
                document.querySelector('[data-chart="roleDistribution"].active').dataset.type);
            updateSystemUsageChart(systemUsageChart, 
                document.querySelector('[data-chart="systemUsage"].active').dataset.type);
            
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            showNotification('Report data refreshed successfully');
        }, 1500);
    });
    
    // Filter functionality
    document.getElementById('reportType').addEventListener('change', function() {
        loadReportData(reportPeriod.value);
    });
    
    document.getElementById('userRole').addEventListener('change', function() {
        loadReportData(reportPeriod.value);
    });
    
    // Table sorting functionality
    document.querySelectorAll('.activity-log-table th').forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const headerIndex = Array.prototype.indexOf.call(this.parentElement.children, this);
            const isAsc = this.classList.contains('asc');
            
            // Clear previous sort indicators
            table.querySelectorAll('th').forEach(th => {
                th.classList.remove('asc', 'desc');
                const icon = th.querySelector('i');
                if (icon) icon.className = 'fas fa-sort';
            });
            
            // Set new sort indicator
            this.classList.toggle('asc', !isAsc);
            this.classList.toggle('desc', isAsc);
            const icon = this.querySelector('i');
            if (icon) icon.className = isAsc ? 'fas fa-sort-up' : 'fas fa-sort-down';
            
            sortTable(table, headerIndex, isAsc);
        });
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.activity-log-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Pagination button handlers
    document.querySelectorAll('.pagination-btn:not(:disabled)').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('active')) return;
            
            document.querySelector('.pagination-btn.active').classList.remove('active');
            this.classList.add('active');
            
            // In a real application, this would load the appropriate page of results
            alert(`Loading page ${this.textContent}`);
        });
    });
    
    // Initial data load
    loadReportData(reportPeriod.value);
    
    // Functions
    function loadReportData(period, startDate, endDate) {
        // In a real application, this would fetch data from an API
        console.log(`Loading report data for ${period}`, startDate, endDate);
        
        // Simulate loading
        setTimeout(() => {
            updateSummaryCards();
            updateUserActivityChart(userActivityChart, 'weekly');
            updateRoleDistributionChart(roleDistributionChart, 'count');
            updateSystemUsageChart(systemUsageChart, 'logins');
        }, 500);
    }
    
    function updateSummaryCards() {
        // Generate random data for demo purposes
        document.getElementById('activeUsers').textContent = Math.floor(Math.random() * 500) + 1000;
        document.getElementById('testsConducted').textContent = Math.floor(Math.random() * 2000) + 2000;
        document.getElementById('avgSession').textContent = (Math.random() * 10 + 20).toFixed(1) + ' min';
        document.getElementById('systemAlerts').textContent = Math.floor(Math.random() * 10) + 5;
    }
    
    function initUserActivityChart() {
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Logins',
                        data: [],
                        borderColor: '#00c698',
                        backgroundColor: 'rgba(0, 198, 152, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Actions',
                        data: [],
                        borderColor: '#095461',
                        backgroundColor: 'rgba(9, 84, 97, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function updateUserActivityChart(chart, type) {
        // Generate random data based on type
        let labels = [];
        let loginData = [];
        let actionData = [];
        
        if (type === 'daily') {
            labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            loginData = labels.map(() => Math.floor(Math.random() * 300) + 100);
            actionData = labels.map(() => Math.floor(Math.random() * 800) + 200);
        } else if (type === 'weekly') {
            labels = Array.from({length: 4}, (_, i) => `Week ${i + 1}`);
            loginData = labels.map(() => Math.floor(Math.random() * 1500) + 500);
            actionData = labels.map(() => Math.floor(Math.random() * 4000) + 1000);
        } else { // monthly
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            loginData = labels.map(() => Math.floor(Math.random() * 5000) + 2000);
            actionData = labels.map(() => Math.floor(Math.random() * 15000) + 5000);
        }
        
        chart.data.labels = labels;
        chart.data.datasets[0].data = loginData;
        chart.data.datasets[1].data = actionData;
        chart.update();
    }
    
    function initRoleDistributionChart() {
        const ctx = document.getElementById('roleDistributionChart').getContext('2d');
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Admin', 'Doctor', 'Nurse', 'Patient', 'Other'],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#00c698',
                        '#095461',
                        '#00a76f',
                        '#ff8b00',
                        '#ff5630'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
    
    function updateRoleDistributionChart(chart, type) {
        // Generate random data
        const data = type === 'count' ? 
            [50, 120, 180, 800, 50].map(v => v + Math.floor(Math.random() * 50)) : 
            [500, 1200, 1800, 3000, 200].map(v => v + Math.floor(Math.random() * 500));
        
        chart.data.datasets[0].data = data;
        chart.update();
    }
    
    function initSystemUsageChart() {
        const ctx = document.getElementById('systemUsageChart').getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Count',
                        data: [],
                        backgroundColor: '#00c698',
                        borderColor: '#00c698',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function updateSystemUsageChart(chart, type) {
        // Generate data based on type
        let labels = [];
        let data = [];
        
        if (type === 'logins') {
            labels = ['00-04', '04-08', '08-12', '12-16', '16-20', '20-24'];
            data = labels.map(() => Math.floor(Math.random() * 300) + 50);
        } else if (type === 'features') {
            labels = ['Records', 'Tests', 'Appointments', 'Prescriptions', 'Billing'];
            data = labels.map(() => Math.floor(Math.random() * 800) + 200);
        } else { // errors
            labels = ['Auth', 'Database', 'Network', 'UI', 'API'];
            data = labels.map(() => Math.floor(Math.random() * 50) + 5);
        }
        
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.data.datasets[0].label = type === 'errors' ? 'Errors' : 'Usage';
        chart.data.datasets[0].backgroundColor = type === 'errors' ? '#ff5630' : '#00c698';
        chart.data.datasets[0].borderColor = type === 'errors' ? '#ff5630' : '#00c698';
        chart.update();
    }
    
    function sortTable(table, column, reverse) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aText = a.children[column].textContent.trim();
            const bText = b.children[column].textContent.trim();
            
            // Special handling for dates and status
            if (column === 0) { // Timestamp column
                return reverse ? 
                    new Date(bText) - new Date(aText) : 
                    new Date(aText) - new Date(bText);
            } else if (column === 5) { // Status column
                return reverse ? 
                    bText.localeCompare(aText) : 
                    aText.localeCompare(bText);
            } else {
                return reverse ? 
                    bText.localeCompare(aText) : 
                    aText.localeCompare(bText);
            }
        });
        
        // Remove all existing rows
        rows.forEach(row => tbody.removeChild(row));
        
        // Add the sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = '#00c698';
        notification.style.color = 'white';
        notification.style.padding = '15px 25px';
        notification.style.borderRadius = '8px';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.zIndex = '1000';
        notification.style.animation = 'fadeIn 0.3s';
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.3s';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});