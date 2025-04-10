document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Add user button click handler
    const addUserBtn = document.querySelector('.add-user-btn');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            // In a real application, this would open a modal or redirect to an add user page
            alert('Add user functionality would open here');
            // Example: window.location.href = 'Admin-Add-User-Page.html';
        });
    }

    // Edit and delete button handlers
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const userId = row.cells[0].textContent;
            const userName = row.cells[1].textContent;
            alert(`Edit user ${userName} (ID: ${userId})`);
            // Example: window.location.href = `Admin-Edit-User-Page.html?id=${userId}`;
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const userId = row.cells[0].textContent;
            const userName = row.cells[1].textContent;
            
            if (confirm(`Are you sure you want to delete ${userName} (ID: ${userId})?`)) {
                // In a real application, this would make an API call to delete the user
                row.remove();
                alert(`${userName} has been deleted`);
            }
        });
    });

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

    // Initialize table sorting (basic implementation)
    const tableHeaders = document.querySelectorAll('.admin-table th');
    tableHeaders.forEach((header, index) => {
        if (index !== tableHeaders.length - 1) { // Skip actions column
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(index);
            });
        }
    });

    function sortTable(columnIndex) {
        const table = document.querySelector('.admin-table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAsc = table.getAttribute('data-sort-dir') === 'asc';
        
        rows.sort((a, b) => {
            const aText = a.cells[columnIndex].textContent;
            const bText = b.cells[columnIndex].textContent;
            
            // Special handling for status column
            if (columnIndex === 4) {
                return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            }
            
            // Numeric sorting for ID column
            if (columnIndex === 0) {
                return isAsc ? parseInt(aText) - parseInt(bText) : parseInt(bText) - parseInt(aText);
            }
            
            // Default text sorting
            return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });
        
        // Remove all rows
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        // Add sorted rows
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort direction
        table.setAttribute('data-sort-dir', isAsc ? 'desc' : 'asc');
    }
});