document.addEventListener('DOMContentLoaded', function() {
    // Search functionality is now handled by the form submission
    
    // Details button functionality with AJAX
    document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            
            // Create AJAX request to get item details
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_item_details.php?item_id=${itemId}`, true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    if (response.success) {
                        // Display the details in a modal or alert
                        const item = response.data;
                        let details = `
                            <h3>${item.name}</h3>
                            <p><strong>Category:</strong> ${item.category}</p>
                            <p><strong>Quantity:</strong> ${item.quantity} ${item.unit}</p>
                            <p><strong>Threshold:</strong> ${item.threshold}</p>
                        `;
                        
                        if (item.expiry_date) {
                            details += `<p><strong>Expiry Date:</strong> ${item.expiry_date}</p>`;
                        }
                        
                        if (item.description) {
                            details += `<p><strong>Description:</strong> ${item.description}</p>`;
                        }
                        
                        alert(details);
                    } else {
                        alert('Error fetching item details: ' + response.message);
                    }
                } else {
                    alert('Error fetching item details');
                }
            };
            
            xhr.send();
        });
    });
});