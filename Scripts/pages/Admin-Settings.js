document.addEventListener('DOMContentLoaded', function() {
    // Ensure admin is logged in
    const isAdmin = localStorage.getItem('userRole') === 'admin';
    if (!isAdmin) {
        window.location.href = 'Login-Page.html';
        return;
    }

    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding tab pane
            tabPanes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Save settings button
    const saveSettingsBtn = document.querySelector('.save-settings-btn');
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            // Collect all form data
            const generalData = collectFormData('generalSettingsForm');
            const securityData = collectFormData('securitySettingsForm');
            const notificationData = collectFormData('notificationSettingsForm');
            const appearanceData = collectFormData('appearanceSettingsForm');
            
            const allSettings = {
                ...generalData,
                ...securityData,
                ...notificationData,
                ...appearanceData
            };
            
            // In a real application, this would send to server
            console.log('Saving settings:', allSettings);
            
            // Show success message
            showToast('Settings saved successfully!', 'success');
            
            // Apply theme changes immediately if they were changed
            if (appearanceData.theme) {
                applyTheme(appearanceData.theme);
            }
        });
    }

    // Color picker value display
    const colorInputs = document.querySelectorAll('input[type="color"]');
    colorInputs.forEach(input => {
        const displaySpan = input.nextElementSibling;
        if (displaySpan) {
            // Update display when color changes
            input.addEventListener('input', function() {
                displaySpan.textContent = this.value;
            });
            
            // Initialize display
            displaySpan.textContent = input.value;
        }
    });

    // Theme selection preview
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                // In a real app, you might preview the theme change
                console.log('Theme selected:', this.value);
            }
        });
    });

    // Helper function to collect form data
    function collectFormData(formId) {
        const form = document.getElementById(formId);
        if (!form) return {};
        
        const formData = new FormData(form);
        const data = {};
        
        formData.forEach((value, key) => {
            // Handle checkboxes and radios differently
            if (form.elements[key].type === 'checkbox') {
                data[key] = form.elements[key].checked;
            } else {
                data[key] = value;
            }
        });
        
        return data;
    }

    // Apply theme to the page
    function applyTheme(theme) {
        document.body.className = ''; // Remove any existing theme classes
        if (theme !== 'light') {
            document.body.classList.add(`${theme}-theme`);
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Initialize settings from localStorage (simulated)
    function initializeSettings() {
        // In a real app, you would load these from server/localStorage
        const savedSettings = {
            systemName: 'MedicalChecks',
            timezone: 'UTC',
            dateFormat: 'DD/MM/YYYY',
            timeFormat: '12h',
            language: 'en',
            region: 'US',
            twoFactorAuth: false,
            passwordComplexity: true,
            sessionTimeout: '30',
            minPasswordLength: '8',
            passwordHistory: '3',
            passwordExpiry: '90',
            emailNotifications: true,
            pushNotifications: false,
            notificationEmail: 'admin@medicalchecks.example.com',
            newUserAlerts: true,
            systemAlerts: true,
            securityAlerts: true,
            theme: 'light',
            primaryColor: '#00c698',
            secondaryColor: '#095461',
            compactMode: false
        };
        
        // Apply settings to forms
        applySettingsToForm('generalSettingsForm', savedSettings);
        applySettingsToForm('securitySettingsForm', savedSettings);
        applySettingsToForm('notificationSettingsForm', savedSettings);
        applySettingsToForm('appearanceSettingsForm', savedSettings);
    }

    // Apply settings to form elements
    function applySettingsToForm(formId, settings) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        for (const element of form.elements) {
            if (element.name in settings) {
                const value = settings[element.name];
                
                if (element.type === 'checkbox') {
                    element.checked = value;
                } else if (element.type === 'radio') {
                    if (element.value === value) {
                        element.checked = true;
                    }
                } else {
                    element.value = value;
                }
            }
        }
    }

    // Initialize the page
    initializeSettings();
});