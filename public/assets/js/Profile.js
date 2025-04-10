document.addEventListener('DOMContentLoaded', function() {
    // Profile Edit Functionality
    const editProfileBtn = document.getElementById('editProfileBtn');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveCancelButtons = document.getElementById('saveCancelButtons');
    
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
                element.innerHTML = `
                    <select>
                        <option value="Male" ${element.textContent === 'Male' ? 'selected' : ''}>Male</option>
                        <option value="Female" ${element.textContent === 'Female' ? 'selected' : ''}>Female</option>
                        <option value="Other" ${element.textContent === 'Other' ? 'selected' : ''}>Other</option>
                        <option value="Prefer not to say" ${element.textContent === 'Prefer not to say' ? 'selected' : ''}>Prefer not to say</option>
                    </select>
                `;
            } else if (field === 'dob') {
                element.innerHTML = `<input type="date" value="${formatDateForInput(element.textContent)}">`;
            } else {
                element.innerHTML = `<input type="text" value="${element.textContent}">`;
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
    
    // Save changes
    function saveChanges() {
        const updatedValues = {};
        
        editableFields.forEach(field => {
            const element = document.getElementById(field);
            if (field === 'gender') {
                updatedValues[field] = element.querySelector('select').value;
            } else if (field === 'dob') {
                updatedValues[field] = formatDateForDisplay(element.querySelector('input').value);
            } else {
                updatedValues[field] = element.querySelector('input').value;
            }
        });
        
        // Update the display with new values
        editableFields.forEach(field => {
            document.getElementById(field).textContent = updatedValues[field];
        });
        
        // In a real app, you would send these to your backend here
        console.log('Updated profile:', updatedValues);
        
        exitEditMode();
        alert('Profile updated successfully!');
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
    
    function formatDateForDisplay(inputDate) {
        // Convert "1985-01-15" to "January 15, 1985"
        const date = new Date(inputDate);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }
    
    // Event listeners
    if (editProfileBtn) editProfileBtn.addEventListener('click', enterEditMode);
    if (saveProfileBtn) saveProfileBtn.addEventListener('click', saveChanges);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', exitEditMode);
    
    // Change password functionality
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            window.location.href = 'Change-Password-Page.html';
        });
    }
    
    // Add contact functionality
    const addContactBtn = document.getElementById('addContactBtn');
    if (addContactBtn) {
        addContactBtn.addEventListener('click', function() {
            alert('Form to add new emergency contact would appear here.');
        });
    }
    
    // Add insurance functionality
    const addInsuranceBtn = document.getElementById('addInsuranceBtn');
    if (addInsuranceBtn) {
        addInsuranceBtn.addEventListener('click', function() {
            alert('Form to add new insurance information would appear here.');
        });
    }
    
    // Edit/delete contact buttons
    document.querySelectorAll('.edit-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Edit contact form would appear here.');
        });
    });
    
    document.querySelectorAll('.delete-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this emergency contact?')) {
                this.closest('.contact-card').remove();
            }
        });
    });
    
    // Edit/delete insurance buttons
    document.querySelectorAll('.edit-insurance').forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Edit insurance form would appear here.');
        });
    });
    
    document.querySelectorAll('.delete-insurance').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this insurance information?')) {
                this.closest('.insurance-card').remove();
            }
        });
    });
});