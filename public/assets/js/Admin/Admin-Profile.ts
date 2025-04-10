document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // Modal elements
    const editProfileModal = document.getElementById('editProfileModal') as HTMLElement | null;
    const changePasswordModal = document.getElementById('changePasswordModal') as HTMLElement | null;
    
    // Button elements
    const editProfileBtn = document.getElementById('editProfileBtn') as HTMLButtonElement | null;
    const closeEditModal = document.getElementById('closeEditModal') as HTMLButtonElement | null;
    const cancelEdit = document.getElementById('cancelEdit') as HTMLButtonElement | null;
    const changePasswordBtn = document.getElementById('changePasswordBtn') as HTMLButtonElement | null;
    const closePasswordModal = document.getElementById('closePasswordModal') as HTMLButtonElement | null;
    const cancelPassword = document.getElementById('cancelPassword') as HTMLButtonElement | null;
    
    // Toggle buttons
    const toggle2faBtn = document.getElementById('toggle2faBtn') as HTMLButtonElement | null;
    const toggleAlertsBtn = document.getElementById('toggleAlertsBtn') as HTMLButtonElement | null;
    const toggleEmailBtn = document.getElementById('toggleEmailBtn') as HTMLButtonElement | null;
    const toggleSystemBtn = document.getElementById('toggleSystemBtn') as HTMLButtonElement | null;
    const togglePromoBtn = document.getElementById('togglePromoBtn') as HTMLButtonElement | null;
    
    // Form elements
    const profileForm = document.getElementById('profileForm') as HTMLFormElement | null;
    const passwordForm = document.getElementById('passwordForm') as HTMLFormElement | null;
    const newPasswordInput = document.getElementById('newPassword') as HTMLInputElement | null;
    const strengthBars = document.querySelectorAll('.strength-bar') as NodeListOf<HTMLElement>;
    const strengthText = document.querySelector('.strength-text') as HTMLElement | null;

    // Open edit profile modal
    if (editProfileBtn && editProfileModal) {
        editProfileBtn.addEventListener('click', function() {
            editProfileModal.classList.add('active');
        });
    }

    // Close edit profile modal
    function closeEditProfileModal() {
        if (editProfileModal) editProfileModal.classList.remove('active');
    }

    if (closeEditModal) closeEditModal.addEventListener('click', closeEditProfileModal);
    if (cancelEdit) cancelEdit.addEventListener('click', closeEditProfileModal);

    // Open change password modal
    if (changePasswordBtn && changePasswordModal) {
        changePasswordBtn.addEventListener('click', function() {
            changePasswordModal.classList.add('active');
        });
    }

    // Close change password modal
    function closeChangePasswordModal() {
        if (changePasswordModal) changePasswordModal.classList.remove('active');
        if (passwordForm) passwordForm.reset();
    }

    if (closePasswordModal) closePasswordModal.addEventListener('click', closeChangePasswordModal);
    if (cancelPassword) cancelPassword.addEventListener('click', closeChangePasswordModal);

    // Toggle button functionality
    function setupToggleButton(button: HTMLButtonElement | null) {
        if (!button) return;
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('active');
            this.textContent = this.classList.contains('active') ? 'On' : 'Off';
            
            // In a real app, you would make an API call here to save the preference
            const settingName = this.id.replace('toggle', '').replace('Btn', '');
            console.log(`${settingName} is now ${this.classList.contains('active') ? 'on' : 'off'}`);
        });
    }

    setupToggleButton(toggle2faBtn);
    setupToggleButton(toggleAlertsBtn);
    setupToggleButton(toggleEmailBtn);
    setupToggleButton(toggleSystemBtn);
    setupToggleButton(togglePromoBtn);

    // Profile form submission
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values with type assertions
            const fullName = (document.getElementById('fullName') as HTMLInputElement).value;
            const email = (document.getElementById('email') as HTMLInputElement).value;
            const phone = (document.getElementById('phone') as HTMLInputElement).value;
            const address = (document.getElementById('address') as HTMLTextAreaElement).value;
            
            // In a real app, you would validate and send to server here
            console.log('Profile updated:', { fullName, email, phone, address });
            
            // Update the displayed profile information
            const nameElement = document.querySelector('.avatar-info h3');
            const emailElement = document.querySelectorAll('.detail-item')[0]?.querySelector('span');
            const phoneElement = document.querySelectorAll('.detail-item')[1]?.querySelector('span');
            const addressElement = document.querySelectorAll('.detail-item')[2]?.querySelector('span');
            
            if (nameElement) nameElement.textContent = fullName;
            if (emailElement) emailElement.textContent = email;
            if (phoneElement) phoneElement.textContent = phone;
            if (addressElement) addressElement.textContent = address;
            
            // Show success message
            alert('Profile updated successfully!');
            
            // Close modal
            closeEditProfileModal();
        });
    }

    // Password strength checker
    if (newPasswordInput && strengthBars && strengthText) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Contains numbers
            if (/\d/.test(password)) strength += 1;
            
            // Contains special characters
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            
            // Contains uppercase and lowercase
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            
            // Update strength bars
            strengthBars.forEach((bar, index) => {
                bar.style.backgroundColor = index < strength ? 
                    (strength < 2 ? '#ff5630' : strength < 4 ? '#ffab00' : '#36b37e') : '#eee';
            });
            
            // Update strength text
            const strengthMessages = ['Very Weak', 'Weak', 'Moderate', 'Strong', 'Very Strong'];
            strengthText.textContent = strengthMessages[strength];
            strengthText.style.color = strength < 2 ? '#ff5630' : strength < 4 ? '#ffab00' : '#36b37e';
        });
    }

    // Password form submission
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = (document.getElementById('currentPassword') as HTMLInputElement).value;
            const newPassword = (document.getElementById('newPassword') as HTMLInputElement).value;
            const confirmPassword = (document.getElementById('confirmPassword') as HTMLInputElement).value;
            
            // Simple validation
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return;
            }
            
            if (newPassword.length < 8) {
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            // In a real app, you would send this to the server
            console.log('Password changed (simulated)');
            
            // Show success message
            alert('Password changed successfully!');
            
            // Close modal and reset form
            closeChangePasswordModal();
        });
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (editProfileModal && e.target === editProfileModal) {
            closeEditProfileModal();
        }
        if (changePasswordModal && e.target === changePasswordModal) {
            closeChangePasswordModal();
        }
    });
});