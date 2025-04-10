document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // DOM Elements
    const tabButtons = document.querySelectorAll('.tab-btn');
    const searchInput = document.querySelector('#inventorySearch');
    const addStockBtn = document.querySelector('#addStockBtn');
    const categoryFilter = document.querySelector('#categoryFilter');
    const statusFilter = document.querySelector('#statusFilter');
    const resetFiltersBtn = document.querySelector('.reset-filters-btn');
    const inventoryTable = document.querySelector('.inventory-table');
    const tableBody = inventoryTable.querySelector('tbody');
    const modal = document.querySelector('#inventoryModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    const inventoryForm = document.querySelector('#inventoryForm');
    const modalTitle = document.querySelector('#modalTitle');

    // Tab functionality
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterInventory();
        });
    });

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', filterInventory);
    }

    // Filter functionality
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterInventory);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterInventory);
    }

    // Reset filters
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            searchInput.value = '';
            categoryFilter.value = '';
            statusFilter.value = '';
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabButtons[0].classList.add('active');
            filterInventory();
        });
    }

    // Filter inventory based on all criteria
    function filterInventory() {
        const searchTerm = searchInput.value.toLowerCase();
        const category = categoryFilter.value.toLowerCase();
        const status = statusFilter.value.toLowerCase();
        const activeTab = document.querySelector('.tab-btn.active').dataset.tab;
        
        const rows = tableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowText = row.textContent.toLowerCase();
            const rowCategory = cells[2].textContent.toLowerCase();
            const rowStatus = cells[6].textContent.toLowerCase();
            
            // Check if row matches all filters
            const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
            const matchesCategory = category === '' || rowCategory.includes(category);
            const matchesStatus = status === '' || rowStatus.includes(status);
            
            // Tab-specific filtering
            let matchesTab = true;
            if (activeTab === 'new') {
                // Example: Items added in last 7 days
                matchesTab = false; // Implement your logic
            } else if (activeTab === 'old') {
                // Items expiring soon
                const expiryDate = cells[5].textContent;
                if (expiryDate !== 'N/A') {
                    const expiry = new Date(expiryDate);
                    const today = new Date();
                    const timeDiff = expiry.getTime() - today.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                    matchesTab = daysDiff <= 30;
                } else {
                    matchesTab = false;
                }
            } else if (activeTab === 'low') {
                // Low stock items
                const quantity = parseInt(cells[3].textContent);
                const threshold = parseInt(cells[4].textContent);
                matchesTab = quantity <= threshold;
            }
            
            row.style.display = (matchesSearch && matchesCategory && matchesStatus && matchesTab) ? '' : 'none';
        });
    }

    // Add stock button - open modal
    if (addStockBtn) {
        addStockBtn.addEventListener('click', function() {
            modalTitle.textContent = 'Add New Inventory Item';
            inventoryForm.reset();
            modal.classList.add('active');
        });
    }

    // Close modal
    function closeModal() {
        modal.classList.remove('active');
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    // Click outside modal to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Form submission
    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const itemName = document.querySelector('#itemName').value;
            const itemCategory = document.querySelector('#itemCategory').value;
            const itemId = document.querySelector('#itemId').value;
            const itemQuantity = document.querySelector('#itemQuantity').value;
            const itemThreshold = document.querySelector('#itemThreshold').value;
            const itemExpiry = document.querySelector('#itemExpiry').value;
            const itemNotes = document.querySelector('#itemNotes').value;
            
            // Determine status based on quantity and threshold
            let statusClass, statusText, statusIcon;
            const quantity = parseInt(itemQuantity);
            const threshold = parseInt(itemThreshold);
            
            if (quantity <= threshold * 0.3) {
                statusClass = 'status-critical';
                statusText = 'Critical';
                statusIcon = '<i class="fas fa-exclamation-circle"></i>';
            } else if (quantity <= threshold) {
                statusClass = 'status-low';
                statusText = 'Low';
                statusIcon = '<i class="fas fa-exclamation-triangle"></i>';
            } else {
                statusClass = 'status-adequate';
                statusText = 'Adequate';
                statusIcon = '<i class="fas fa-check-circle"></i>';
            }
            
            // Create new row (in a real app, this would be saved to a database first)
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${itemId}</td>
                <td>${itemName}</td>
                <td>${itemCategory}</td>
                <td><span class="quantity-value">${itemQuantity}</span></td>
                <td>${itemThreshold}</td>
                <td>${itemExpiry || 'N/A'}</td>
                <td><span class="status ${statusClass}">${statusIcon} ${statusText}</span></td>
                <td class="action-buttons">
                    <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                    <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                    <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
                </td>
            `;
            
            // Add to table
            tableBody.appendChild(newRow);
            
            // Close modal and reset form
            closeModal();
            inventoryForm.reset();
            
            // Add event listeners to new action buttons
            addActionEventListeners(newRow);
            
            // Show success message
            alert('Inventory item added successfully!');
        });
    }

    // Add event listeners to action buttons
    function addActionEventListeners(row) {
        const editBtn = row.querySelector('.edit-btn');
        const deleteBtn = row.querySelector('.delete-btn');
        const restockBtn = row.querySelector('.restock-btn');
        
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                // Get row data
                const cells = row.querySelectorAll('td');
                
                // Set modal title
                modalTitle.textContent = 'Edit Inventory Item';
                
                // Fill form with existing data
                document.querySelector('#itemName').value = cells[1].textContent;
                document.querySelector('#itemCategory').value = cells[2].textContent;
                document.querySelector('#itemId').value = cells[0].textContent;
                document.querySelector('#itemQuantity').value = cells[3].textContent.trim();
                document.querySelector('#itemThreshold').value = cells[4].textContent;
                
                const expiryDate = cells[5].textContent;
                document.querySelector('#itemExpiry').value = expiryDate === 'N/A' ? '' : expiryDate;
                
                // Open modal
                modal.classList.add('active');
                
                // Update form submission to handle edit
                const formSubmitHandler = function(e) {
                    e.preventDefault();
                    
                    // Get updated values
                    const itemName = document.querySelector('#itemName').value;
                    const itemCategory = document.querySelector('#itemCategory').value;
                    const itemQuantity = document.querySelector('#itemQuantity').value;
                    const itemThreshold = document.querySelector('#itemThreshold').value;
                    const itemExpiry = document.querySelector('#itemExpiry').value;
                    
                    // Update row data
                    cells[1].textContent = itemName;
                    cells[2].textContent = itemCategory;
                    cells[3].innerHTML = `<span class="quantity-value">${itemQuantity}</span>`;
                    cells[4].textContent = itemThreshold;
                    cells[5].textContent = itemExpiry || 'N/A';
                    
                    // Update status based on new quantity
                    const quantity = parseInt(itemQuantity);
                    const threshold = parseInt(itemThreshold);
                    let statusClass, statusText, statusIcon;
                    
                    if (quantity <= threshold * 0.3) {
                        statusClass = 'status-critical';
                        statusText = 'Critical';
                        statusIcon = '<i class="fas fa-exclamation-circle"></i>';
                    } else if (quantity <= threshold) {
                        statusClass = 'status-low';
                        statusText = 'Low';
                        statusIcon = '<i class="fas fa-exclamation-triangle"></i>';
                    } else {
                        statusClass = 'status-adequate';
                        statusText = 'Adequate';
                        statusIcon = '<i class="fas fa-check-circle"></i>';
                    }
                    
                    cells[6].innerHTML = `<span class="status ${statusClass}">${statusIcon} ${statusText}</span>`;
                    
                    // Close modal
                    closeModal();
                    
                    // Show success message
                    alert('Inventory item updated successfully!');
                    
                    // Remove this event listener to prevent multiple handlers
                    inventoryForm.removeEventListener('submit', formSubmitHandler);
                    
                    // Restore original form handler
                    inventoryForm.addEventListener('submit', arguments.callee);
                };
                
                // Replace form submit handler for edit
                inventoryForm.removeEventListener('submit', arguments.callee);
                inventoryForm.addEventListener('submit', formSubmitHandler);
            });
        }
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this inventory item?')) {
                    row.remove();
                    alert('Inventory item deleted successfully!');
                }
            });
        }
        
        if (restockBtn) {
            restockBtn.addEventListener('click', function() {
                const itemName = row.querySelector('td:nth-child(2)').textContent;
                alert(`Restock request sent for: ${itemName}`);
            });
        }
    }

    // Initialize action buttons for existing rows
    document.querySelectorAll('.inventory-table tbody tr').forEach(row => {
        addActionEventListeners(row);
    });

    // Table sorting functionality
    document.querySelectorAll('.inventory-table th[data-sort]').forEach(header => {
        header.addEventListener('click', function() {
            const sortKey = this.dataset.sort;
            const isAscending = !this.classList.contains('asc');
            
            // Reset all headers
            document.querySelectorAll('.inventory-table th[data-sort]').forEach(h => {
                h.classList.remove('asc', 'desc');
            });
            
            // Set current header state
            this.classList.toggle('asc', isAscending);
            this.classList.toggle('desc', !isAscending);
            
            // Sort table
            sortTable(sortKey, isAscending);
        });
    });

    function sortTable(sortKey, isAscending) {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const indexMap = {
            'id': 0,
            'name': 1,
            'category': 2,
            'quantity': 3,
            'expiry': 5,
            'status': 6
        };
        
        const colIndex = indexMap[sortKey];
        
        rows.sort((a, b) => {
            const aCell = a.querySelectorAll('td')[colIndex];
            const bCell = b.querySelectorAll('td')[colIndex];
            
            let aValue = aCell.textContent.trim();
            let bValue = bCell.textContent.trim();
            
            // Special handling for quantity (numeric)
            if (sortKey === 'quantity') {
                aValue = parseInt(aValue);
                bValue = parseInt(bValue);
                return isAscending ? aValue - bValue : bValue - aValue;
            }
            
            // Special handling for expiry date
            if (sortKey === 'expiry') {
                if (aValue === 'N/A') return isAscending ? 1 : -1;
                if (bValue === 'N/A') return isAscending ? -1 : 1;
                
                const aDate = new Date(aValue);
                const bDate = new Date(bValue);
                return isAscending ? aDate - bDate : bDate - aDate;
            }
            
            // Default string comparison
            return isAscending 
                ? aValue.localeCompare(bValue) 
                : bValue.localeCompare(aValue);
        });
        
        // Rebuild table with sorted rows
        rows.forEach(row => tableBody.appendChild(row));
    }
});