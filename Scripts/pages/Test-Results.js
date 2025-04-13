document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchBtn');
    const resultsContainer = document.getElementById('resultsContainer');
    
    if (searchInput && searchButton) {
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            const resultCards = resultsContainer.querySelectorAll('.result-card');
            
            resultCards.forEach(card => {
                const testName = card.querySelector('h3').textContent.toLowerCase();
                const testDate = card.querySelector('p').textContent.toLowerCase();
                
                if (testName.includes(searchTerm) || testDate.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Also search when Enter is pressed
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchButton.click();
            }
        });
    }
    
    // Details button functionality
    document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const testId = this.getAttribute('data-test-id');
            const detailsDiv = document.getElementById(`details-${testId}`);
            
            if (detailsDiv.style.display === 'none' || !detailsDiv.style.display) {
                detailsDiv.style.display = 'block';
                this.textContent = 'Hide Details';
            } else {
                detailsDiv.style.display = 'none';
                this.textContent = 'Details';
            }
        });
    });
});