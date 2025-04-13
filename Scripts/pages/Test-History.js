document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const timeFilter = document.getElementById('time-filter');
    const testTypeFilter = document.getElementById('test-type');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const testItems = document.querySelectorAll('.test-item');
    
    function filterTests() {
        const timeValue = timeFilter.value;
        const typeValue = testTypeFilter.value;
        const searchValue = searchInput.value.toLowerCase();
        
        // Get current date for time filtering
        const now = new Date();
        let cutoffDate;
        
        switch (timeValue) {
            case 'year':
                cutoffDate = new Date();
                cutoffDate.setFullYear(now.getFullYear() - 1);
                break;
            case 'month':
                cutoffDate = new Date();
                cutoffDate.setMonth(now.getMonth() - 1);
                break;
            case 'week':
                cutoffDate = new Date();
                cutoffDate.setDate(now.getDate() - 7);
                break;
            default: // 'all'
                cutoffDate = new Date(0); // Very old date
        }
        
        testItems.forEach(item => {
            const testDate = new Date(item.querySelector('.test-date .day').textContent + ' ' + 
                                     item.querySelector('.test-date .month').textContent + ' ' + 
                                     item.closest('.timeline-year').querySelector('h2').textContent);
            const testCategory = item.dataset.category;
            const testName = item.querySelector('h4').textContent.toLowerCase();
            
            // Check if item matches all filters
            const matchesTime = testDate >= cutoffDate;
            const matchesType = typeValue === 'all' || testCategory === typeValue;
            const matchesSearch = searchValue === '' || testName.includes(searchValue);
            
            if (matchesTime && matchesType && matchesSearch) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Add event listeners for filters
    if (timeFilter && testTypeFilter) {
        timeFilter.addEventListener('change', filterTests);
        testTypeFilter.addEventListener('change', filterTests);
    }
    
    // Add event listeners for search
    if (searchInput && searchBtn) {
        searchBtn.addEventListener('click', filterTests);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                filterTests();
            }
        });
    }
    
    // View results button functionality
    document.querySelectorAll('.view-results').forEach(button => {
        button.addEventListener('click', function() {
            const testId = this.dataset.testId;
            // Redirect to test results page with the test ID
            window.location.href = `Test-Results-Page.php?test_id=${testId}`;
        });
    });
});