document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const timeFilter = document.getElementById('time-filter');
    const testTypeFilter = document.getElementById('test-type');
    
    if (timeFilter && testTypeFilter) {
        timeFilter.addEventListener('change', filterTests);
        testTypeFilter.addEventListener('change', filterTests);
        
        function filterTests() {
            // Implement actual filtering logic here
            console.log('Filtering by:', timeFilter.value, testTypeFilter.value);
        }
    }
    
    // View results button functionality
    document.querySelectorAll('.view-results').forEach(button => {
        button.addEventListener('click', function() {
            // In a real app, this would open the test results
            alert('Opening test results...');
        });
    });
});