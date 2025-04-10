document.addEventListener('DOMContentLoaded', function() {
    // Load saved settings
    function loadSettings() {
        const savedSettings = JSON.parse(localStorage.getItem('userSettings')) || {};
        
        // Notification settings
        document.getElementById('emailNotifications').checked = savedSettings.emailNotifications !== false;
        document.getElementById('smsAlerts').checked = savedSettings.smsAlerts || false;
        document.getElementById('appNotifications').checked = savedSettings.appNotifications !== false;
        
        // Privacy settings
        document.getElementById('dataSharing').checked = savedSettings.dataSharing || false;
        document.getElementById('showInDirectory').checked = savedSettings.showInDirectory !== false;
        
        // Display settings
        document.getElementById('darkMode').checked = savedSettings.darkMode || false;
        if (savedSettings.fontSize) {
            document.getElementById('fontSize').value = savedSettings.fontSize;
        }
        
        // Account settings
        document.getElementById('twoFactorAuth').checked = savedSettings.twoFactorAuth || false;
        document.getElementById('activityLog').checked = savedSettings.activityLog !== false;
        
        // Apply dark mode if enabled
        if (savedSettings.darkMode) {
            document.body.classList.add('dark-mode');
        }
        
        // Apply font size
        if (savedSettings.fontSize) {
            document.body.style.fontSize = savedSettings.fontSize === 'small' ? '14px' : 
                                         savedSettings.fontSize === 'large' ? '18px' : '16px';
        }
    }
    
    // Save settings
    function saveSettings() {
        const settings = {
            // Notification settings
            emailNotifications: document.getElementById('emailNotifications').checked,
            smsAlerts: document.getElementById('smsAlerts').checked,
            appNotifications: document.getElementById('appNotifications').checked,
            
            // Privacy settings
            dataSharing: document.getElementById('dataSharing').checked,
            showInDirectory: document.getElementById('showInDirectory').checked,
            
            // Display settings
            darkMode: document.getElementById('darkMode').checked,
            fontSize: document.getElementById('fontSize').value,
            
            // Account settings
            twoFactorAuth: document.getElementById('twoFactorAuth').checked,
            activityLog: document.getElementById('activityLog').checked
        };
        
        localStorage.setItem('userSettings', JSON.stringify(settings));
        alert('Settings saved successfully!');
        
        // Apply changes immediately
        if (settings.darkMode) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        // Apply font size
        document.body.style.fontSize = settings.fontSize === 'small' ? '14px' : 
                                      settings.fontSize === 'large' ? '18px' : '16px';
    }
    
    // Initialize settings
    loadSettings();
    
    // Save button event
    document.getElementById('saveSettings').addEventListener('click', saveSettings);
    
    // Dark mode toggle
    document.getElementById('darkMode').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
    });
    
    // Font size change
    document.getElementById('fontSize').addEventListener('change', function() {
        document.body.style.fontSize = this.value === 'small' ? '14px' : 
                                      this.value === 'large' ? '18px' : '16px';
    });
    
    // Add dark mode styles if needed
    const style = document.createElement('style');
    style.textContent = `
        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f0f0f0;
            --text-light: #b0b0b0;
            --text-lighter: #808080;
            --white: #1e1e1e;
            --secondary-color: #00c698;
        }
        
        body.dark-mode .settings-section,
        body.dark-mode .select-setting {
            background-color: #2d2d2d;
            color: #f0f0f0;
        }
        
        body.dark-mode .select-setting {
            border-color: #444;
        }
    `;
    document.head.appendChild(style);
});