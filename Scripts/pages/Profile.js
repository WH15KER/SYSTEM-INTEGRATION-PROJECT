document.addEventListener('DOMContentLoaded', function() {
    // Profile Edit Functionality
    const editProfileBtn = document.getElementById('editProfileBtn');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveCancelButtons = document.getElementById('saveCancelButtons');
    const profileForm = document.getElementById('profileForm');
    
    // Fields that can be edited
    const editableFields = [
        'fullName', 'dob', 'gender', 'email', 'phone', 
        'bloodType', 'address', 'physician'
    ];
    
    // Original values backup
    let originalValues = {};
    
    // Enter edit mode
    function enterEditMode() {
        document.body.classList.add('edit-mode');
        saveCancelButtons.style.display = 'flex';
        editProfileBtn.style.display = 'none';
        
        // Backup original values
        editableFields.forEach(field => {
            originalValues[field] = document.getElementById(field).textContent;
            
            // Replace with input fields
            const element = document.getElementById(field);
            if (field === 'gender') {
                const currentGender = element.textContent.trim();
                element.innerHTML = `
                    <select name="gender">
                        <option value="male" ${currentGender === 'Male' ? 'selected' : ''}>Male</option>
                        <option value="female" ${currentGender === 'Female' ? 'selected' : ''}>Female</option>
                        <option value="other" ${currentGender === 'Other' ? 'selected' : ''}>Other</option>
                        <option value="prefer-not-to-say" ${currentGender === 'Prefer not to say' ? 'selected' : ''}>Prefer not to say</option>
                    </select>
                `;
            } else if (field === 'dob') {
                element.innerHTML = `<input type="date" name="dob" value="${formatDateForInput(element.textContent)}">`;
            } else {
                const inputName = field === 'fullName' ? 'fullName' : 
                                 field === 'bloodType' ? 'bloodType' : 
                                 field.toLowerCase();
                element.innerHTML = `<input type="text" name="${inputName}" value="${element.textContent}">`;
            }
        });
    }
    
    // Exit edit mode
    function exitEditMode() {
        document.body.classList.remove('edit-mode');
        saveCancelButtons.style.display = 'none';
        editProfileBtn.style.display = 'inline-flex';
        
        // Restore original values
        editableFields.forEach(field => {
            const element = document.getElementById(field);
            element.textContent = originalValues[field];
        });
    }
    
    // Date formatting helpers
    function formatDateForInput(displayDate) {
        // Convert "January 15, 1985" to "1985-01-15"
        const months = {
            January: '01', February: '02', March: '03', April: '04',
            May: '05', June: '06', July: '07', August: '08',
            September: '09', October: '10', November: '11', December: '12'
        };
        
        const parts = displayDate.split(' ');
        if (parts.length === 3) {
            const month = months[parts[0]];
            const day = parts[1].replace(',', '').padStart(2, '0');
            const year = parts[2];
            return `${year}-${month}-${day}`;
        }
        return '';
    }
    
    // Event listeners
    if (editProfileBtn) editProfileBtn.addEventListener('click', enterEditMode);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', exitEditMode);
    
    // Change password functionality
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            window.location.href = 'Change-Password-Page.php';
        });
    }
    
    // Add contact functionality
    const addContactBtn = document.getElementById('addContactBtn');
    if (addContactBtn) {
        addContactBtn.addEventListener('click', function() {
            window.location.href = 'Add-Contact-Page.php';
        });
    }
    
    // Add insurance functionality
    const addInsuranceBtn = document.getElementById('addInsuranceBtn');
    if (addInsuranceBtn) {
        addInsuranceBtn.addEventListener('click', function() {
            window.location.href = 'Add-Insurance-Page.php';
        });
    }
    
    // Edit/delete contact buttons
    document.querySelectorAll('.edit-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            const contactId = this.getAttribute('data-contact-id');
            window.location.href = `Edit-Contact-Page.php?id=${contactId}`;
        });
    });
    
    document.querySelectorAll('.delete-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            const contactId = this.getAttribute('data-contact-id');
            if (confirm('Are you sure you want to delete this emergency contact?')) {
                fetch(`delete_contact.php?id=${contactId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.contact-card').remove();
                    } else {
                        alert('Failed to delete contact: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the contact');
                });
            }
        });
    });
    
    // Edit/delete insurance buttons
    document.querySelectorAll('.edit-insurance').forEach(btn => {
        btn.addEventListener('click', function() {
            const insuranceId = this.getAttribute('data-insurance-id');
            window.location.href = `Edit-Insurance-Page.php?id=${insuranceId}`;
        });
    });
    
    document.querySelectorAll('.delete-insurance').forEach(btn => {
        btn.addEventListener('click', function() {
            const insuranceId = this.getAttribute('data-insurance-id');
            if (confirm('Are you sure you want to delete this insurance information?')) {
                fetch(`delete_insurance.php?id=${insuranceId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.insurance-card').remove();
                    } else {
                        alert('Failed to delete insurance: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the insurance');
                });
            }
        });
    });
});