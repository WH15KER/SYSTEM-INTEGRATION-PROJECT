document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = '../Login-Page.html';
        return;
    }

    // Sample audit log data
    const auditLogs = [
        {
            id: 'AUD-2023-001',
            timestamp: '2023-11-15 09:23:45',
            user: 'admin@medicalchecks.com',
            action: 'Login',
            entity: 'User Session',
            details: 'Successful login from IP 192.168.1.100',
            status: 'success',
            ipAddress: '192.168.1.100'
        },
        {
            id: 'AUD-2023-002',
            timestamp: '2023-11-15 10:45:12',
            user: 'doctor@medicalchecks.com',
            action: 'Update',
            entity: 'Patient Record',
            details: 'Updated patient demographics for ID #PT-10045',
            status: 'success',
            ipAddress: '192.168.1.105'
        },
        {
            id: 'AUD-2023-003',
            timestamp: '2023-11-15 11:12:33',
            user: 'nurse@medicalchecks.com',
            action: 'Create',
            entity: 'Lab Test Order',
            details: 'Ordered CBC test for patient ID #PT-10078',
            status: 'success',
            ipAddress: '192.168.1.110'
        },
        {
            id: 'AUD-2023-004',
            timestamp: '2023-11-15 13:27:19',
            user: 'admin@medicalchecks.com',
            action: 'Delete',
            entity: 'User Account',
            details: 'Deleted user account for technician@medicalchecks.com',
            status: 'success',
            ipAddress: '192.168.1.100'
        },
        {
            id: 'AUD-2023-005',
            timestamp: '2023-11-15 14:05:42',
            user: 'labtech@medicalchecks.com',
            action: 'Update',
            entity: 'Lab Result',
            details: 'Updated test results for order #LAB-2023-0456',
            status: 'success',
            ipAddress: '192.168.1.115'
        },
        {
            id: 'AUD-2023-006',
            timestamp: '2023-11-15 15:30:18',
            user: 'unknown',
            action: 'Login',
            entity: 'User Session',
            details: 'Failed login attempt for user doctor@medicalchecks.com - Invalid credentials',
            status: 'error',
            ipAddress: '203.0.113.42'
        },
        {
            id: 'AUD-2023-007',
            timestamp: '2023-11-15 16:45:55',
            user: 'system',
            action: 'Maintenance',
            entity: 'Database',
            details: 'Performed nightly database backup',
            status: 'info',
            ipAddress: '127.0.0.1'
        },
        {
            id: 'AUD-2023-008',
            timestamp: '2023-11-15 17:12:30',
            user: 'admin@medicalchecks.com',
            action: 'Configuration',
            entity: 'System Settings',
            details: 'Changed password policy requirements',
            status: 'warning',
            ipAddress: '192.168.1.100'
        },
        {
            id: 'AUD-2023-009',
            timestamp: '2023-11-15 18:20:15',
            user: 'reception@medicalchecks.com',
            action: 'Create',
            entity: 'Appointment',
            details: 'Scheduled follow-up appointment for patient ID #PT-10102',
            status: 'success',
            ipAddress: '192.168.1.120'
        },
        {
            id: 'AUD-2023-010',
            timestamp: '2023-11-15 19:05:33',
            user: 'system',
            action: 'Update',
            entity: 'System',
            details: 'Applied security patches for critical vulnerabilities',
            status: 'info',
            ipAddress: '127.0.0.1'
        }
    ];

    // DOM elements
    const auditLogsBody = document.getElementById('auditLogsBody');
    const logSearch = document.getElementById('logSearch');
    const actionTypeFilter = document.getElementById('actionType');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const applyFiltersBtn = document.querySelector('.apply-filters-btn');
    const resetFiltersBtn = document.querySelector('.reset-filters-btn');
    const exportBtn = document.querySelector('.export-btn');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const pageSizeSelect = document.getElementById('pageSize');
    const showingFromSpan = document.getElementById('showingFrom');
    const showingToSpan = document.getElementById('showingTo');
    const totalLogsSpan = document.getElementById('totalLogs');
    const totalLogsCount = document.getElementById('totalLogsCount');
    const todayLogs = document.getElementById('todayLogs');
    const criticalLogs = document.getElementById('criticalLogs');
    const userActions = document.getElementById('userActions');
    const systemEvents = document.getElementById('systemEvents');
    const logDetailsModal = document.getElementById('logDetailsModal');
    const logDetailsContent = document.getElementById('logDetailsContent');
    const closeModalButtons = document.querySelectorAll('.close-modal');

    // Pagination variables
    let currentPage = 1;
    let pageSize = 10;
    let filteredLogs = [...auditLogs];
    let sortColumn = 'timestamp';
    let sortDirection = 'desc';

    // Initialize date pickers
    $(startDateInput).datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
    
    $(endDateInput).datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    // Initialize the page
    function init() {
        updateStats();
        renderLogs();
        setupEventListeners();
    }

    // Update statistics cards
    function updateStats() {
        const today = new Date().toISOString().split('T')[0];
        const todayCount = auditLogs.filter(log => log.timestamp.startsWith(today)).length;
        const criticalCount = auditLogs.filter(log => log.status === 'error').length;
        const userActionsCount = auditLogs.filter(log => log.user !== 'system').length;
        const systemEventsCount = auditLogs.filter(log => log.user === 'system').length;
        
        todayLogs.textContent = todayCount;
        criticalLogs.textContent = criticalCount;
        userActions.textContent = userActionsCount;
        systemEvents.textContent = systemEventsCount;
        totalLogsCount.textContent = auditLogs.length;
        totalLogsSpan.textContent = auditLogs.length;
    }

    // Render logs based on current filters and pagination
    function renderLogs() {
        // Clear existing logs
        auditLogsBody.innerHTML = '';
        
        // Calculate pagination
        const totalLogs = filteredLogs.length;
        const totalPages = Math.ceil(totalLogs / pageSize);
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalLogs);
        const paginatedLogs = filteredLogs.slice(startIndex, endIndex);
        
        // Update pagination info
        showingFromSpan.textContent = startIndex + 1;
        showingToSpan.textContent = endIndex;
        totalLogsSpan.textContent = totalLogs;
        
        // Render each log
        paginatedLogs.forEach(log => {
            const row = document.createElement('tr');
            
            // Determine status class
            let statusClass = '';
            switch(log.status) {
                case 'success': statusClass = 'status-success'; break;
                case 'error': statusClass = 'status-error'; break;
                case 'warning': statusClass = 'status-warning'; break;
                case 'info': statusClass = 'status-info'; break;
                default: statusClass = 'status-info';
            }
            
            row.innerHTML = `
                <td>${log.timestamp}</td>
                <td>${log.user}</td>
                <td>${log.action}</td>
                <td>${log.entity}</td>
                <td class="log-details" data-log-id="${log.id}">${log.details}</td>
                <td><span class="status-badge ${statusClass}">${log.status}</span></td>
                <td>${log.ipAddress}</td>
            `;
            
            auditLogsBody.appendChild(row);
        });
        
        // Update pagination buttons
        updatePaginationButtons(totalPages);
    }

    // Update pagination buttons
    function updatePaginationButtons(totalPages) {
        // Clear existing page numbers
        pageNumbersContainer.innerHTML = '';
        
        // Previous button state
        prevPageBtn.disabled = currentPage === 1;
        
        // Next button state
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        // Generate page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        // Adjust if we're at the end
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // Always show first page
        if (startPage > 1) {
            const firstPageBtn = createPageButton(1);
            pageNumbersContainer.appendChild(firstPageBtn);
            
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 10px';
                pageNumbersContainer.appendChild(ellipsis);
            }
        }
        
        // Show range of pages
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = createPageButton(i);
            pageNumbersContainer.appendChild(pageBtn);
        }
        
        // Always show last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.padding = '0 10px';
                pageNumbersContainer.appendChild(ellipsis);
            }
            
            const lastPageBtn = createPageButton(totalPages);
            pageNumbersContainer.appendChild(lastPageBtn);
        }
    }

    // Create a page button
    function createPageButton(pageNumber) {
        const button = document.createElement('button');
        button.className = 'pagination-btn';
        button.textContent = pageNumber;
        
        if (pageNumber === currentPage) {
            button.classList.add('active');
        }
        
        button.addEventListener('click', () => {
            currentPage = pageNumber;
            renderLogs();
        });
        
        return button;
    }

    // Apply filters
    function applyFilters() {
        const searchTerm = logSearch.value.toLowerCase();
        const actionType = actionTypeFilter.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        filteredLogs = auditLogs.filter(log => {
            // Search term filter
            const matchesSearch = 
                log.user.toLowerCase().includes(searchTerm) ||
                log.action.toLowerCase().includes(searchTerm) ||
                log.entity.toLowerCase().includes(searchTerm) ||
                log.details.toLowerCase().includes(searchTerm) ||
                log.ipAddress.toLowerCase().includes(searchTerm);
            
            // Action type filter
            const matchesActionType = 
                actionType === 'all' || 
                (actionType === 'login' && log.action.toLowerCase().includes('login')) ||
                (actionType === 'create' && log.action.toLowerCase().includes('create')) ||
                (actionType === 'update' && log.action.toLowerCase().includes('update')) ||
                (actionType === 'delete' && log.action.toLowerCase().includes('delete')) ||
                (actionType === 'system' && log.user.toLowerCase() === 'system');
            
            // Date range filter
            const logDate = log.timestamp.split(' ')[0];
            const matchesDateRange = 
                (!startDate || logDate >= startDate) && 
                (!endDate || logDate <= endDate);
            
            return matchesSearch && matchesActionType && matchesDateRange;
        });
        
        // Reset to first page when filters change
        currentPage = 1;
        renderLogs();
    }

    // Reset all filters
    function resetFilters() {
        logSearch.value = '';
        actionTypeFilter.value = 'all';
        startDateInput.value = '';
        endDateInput.value = '';
        pageSizeSelect.value = '10';
        pageSize = 10;
        currentPage = 1;
        filteredLogs = [...auditLogs];
        renderLogs();
    }

    // Sort logs
    function sortLogs(column) {
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        
        filteredLogs.sort((a, b) => {
            let valueA = a[column];
            let valueB = b[column];
            
            // Special case for timestamp
            if (column === 'timestamp') {
                valueA = new Date(valueA);
                valueB = new Date(valueB);
            }
            
            if (valueA < valueB) {
                return sortDirection === 'asc' ? -1 : 1;
            }
            if (valueA > valueB) {
                return sortDirection === 'asc' ? 1 : -1;
            }
            return 0;
        });
        
        renderLogs();
    }

    // Export logs to CSV
    function exportToCSV() {
        const headers = ['Timestamp', 'User', 'Action', 'Entity', 'Details', 'Status', 'IP Address'];
        const rows = filteredLogs.map(log => [
            log.timestamp,
            log.user,
            log.action,
            log.entity,
            log.details,
            log.status,
            log.ipAddress
        ]);
        
        let csvContent = headers.join(',') + '\n';
        rows.forEach(row => {
            csvContent += row.map(field => `"${field.replace(/"/g, '""')}"`).join(',') + '\n';
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `audit_logs_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Show log details modal
    function showLogDetails(logId) {
        const log = auditLogs.find(l => l.id === logId);
        if (!log) return;
        
        logDetailsContent.innerHTML = `
            <div class="log-detail-row">
                <div class="log-detail-label">ID:</div>
                <div class="log-detail-value">${log.id}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">Timestamp:</div>
                <div class="log-detail-value">${log.timestamp}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">User:</div>
                <div class="log-detail-value">${log.user}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">Action:</div>
                <div class="log-detail-value">${log.action}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">Entity:</div>
                <div class="log-detail-value">${log.entity}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">IP Address:</div>
                <div class="log-detail-value">${log.ipAddress}</div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">Status:</div>
                <div class="log-detail-value">
                    <span class="status-badge ${getStatusClass(log.status)}">${log.status}</span>
                </div>
            </div>
            <div class="log-detail-row">
                <div class="log-detail-label">Details:</div>
                <div class="log-detail-value">
                    <pre>${log.details}</pre>
                </div>
            </div>
        `;
        
        logDetailsModal.classList.add('active');
    }

    // Get status class for modal
    function getStatusClass(status) {
        switch(status) {
            case 'success': return 'status-success';
            case 'error': return 'status-error';
            case 'warning': return 'status-warning';
            case 'info': return 'status-info';
            default: return 'status-info';
        }
    }

    // Close modal
    function closeModal() {
        logDetailsModal.classList.remove('active');
    }

    // Setup event listeners
    function setupEventListeners() {
        // Filter events
        applyFiltersBtn.addEventListener('click', applyFilters);
        resetFiltersBtn.addEventListener('click', resetFilters);
        logSearch.addEventListener('input', applyFilters);
        actionTypeFilter.addEventListener('change', applyFilters);
        startDateInput.addEventListener('change', applyFilters);
        endDateInput.addEventListener('change', applyFilters);
        
        // Pagination events
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderLogs();
            }
        });
        
        nextPageBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredLogs.length / pageSize);
            if (currentPage < totalPages) {
                currentPage++;
                renderLogs();
            }
        });
        
        pageSizeSelect.addEventListener('change', () => {
            pageSize = parseInt(pageSizeSelect.value);
            currentPage = 1;
            renderLogs();
        });
        
        // Export event
        exportBtn.addEventListener('click', exportToCSV);
        
        // Sort events
        document.querySelectorAll('.audit-table th[data-sort]').forEach(th => {
            th.addEventListener('click', () => {
                const column = th.getAttribute('data-sort');
                sortLogs(column);
            });
        });
        
        // Log details events
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('log-details')) {
                const logId = e.target.getAttribute('data-log-id');
                showLogDetails(logId);
            }
        });
        
        // Modal events
        closeModalButtons.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });
        
        // Close modal when clicking outside
        logDetailsModal.addEventListener('click', (e) => {
            if (e.target === logDetailsModal) {
                closeModal();
            }
        });
    }

    // Initialize the page
    init();
});