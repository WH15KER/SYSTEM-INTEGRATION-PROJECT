// medical_record.js - Comprehensive Medical Record Management

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all medical record functionality
    initMedicalRecordSystem();
});

function initMedicalRecordSystem() {
    // Form submission handling
    const medicalRecordForm = document.getElementById('medical-record-form');
    if (medicalRecordForm) {
        medicalRecordForm.addEventListener('submit', handleFormSubmission);
    }

    // Search functionality
    const searchInput = document.getElementById('patient-search');
    if (searchInput) {
        searchInput.addEventListener('input', handlePatientSearch);
    }

    // Modal controls
    setupModalControls();

    // Data table initialization
    initDataTable();

    // Print functionality
    const printBtn = document.getElementById('print-record');
    if (printBtn) {
        printBtn.addEventListener('click', printMedicalRecord);
    }

    // Check for URL parameters (like success messages)
    checkURLParameters();
}

function handleFormSubmission(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    // Client-side validation
    if (!validateMedicalForm(formData)) {
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
        return;
    }

    // AJAX submission
    fetch('medical_record_page.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Record saved successfully!', 'success');
            if (form.dataset.redirect) {
                window.location.href = form.dataset.redirect;
            } else {
                // Reset form or update UI as needed
                form.reset();
                if (data.record_id) {
                    updateUIAfterSubmission(data.record_id);
                }
            }
        } else {
            showNotification(data.message || 'Error saving record', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving the record', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
}

function validateMedicalForm(formData) {
    // Required fields validation
    const requiredFields = ['patient_id', 'diagnosis', 'treatment'];
    let isValid = true;

    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            showNotification(`Please fill in the ${field.replace('_', ' ')} field`, 'error');
            isValid = false;
        }
    });

    // Date validation
    const visitDate = formData.get('visit_date');
    if (visitDate && new Date(visitDate) > new Date()) {
        showNotification('Visit date cannot be in the future', 'error');
        isValid = false;
    }

    return isValid;
}

function handlePatientSearch(e) {
    const searchTerm = e.target.value.trim();
    if (searchTerm.length < 2) return;

    // Debounce the search
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => {
        fetch(`medical_record_page.php?action=search&term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                updateSearchResults(data.patients || []);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }, 300);
}

function updateSearchResults(patients) {
    const resultsContainer = document.getElementById('patient-search-results');
    if (!resultsContainer) return;

    resultsContainer.innerHTML = '';

    if (patients.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No patients found</div>';
        return;
    }

    patients.forEach(patient => {
        const patientElement = document.createElement('div');
        patientElement.className = 'patient-result';
        patientElement.innerHTML = `
            <div class="patient-name">${patient.full_name}</div>
            <div class="patient-details">
                <span>ID: ${patient.patient_id}</span>
                <span>DOB: ${patient.dob}</span>
            </div>
        `;
        patientElement.addEventListener('click', () => {
            selectPatient(patient);
            resultsContainer.innerHTML = '';
        });
        resultsContainer.appendChild(patientElement);
    });
}

function selectPatient(patient) {
    // Update form fields with selected patient
    document.getElementById('patient-id').value = patient.patient_id;
    document.getElementById('patient-search').value = patient.full_name;
    
    // Update patient info display
    const infoContainer = document.getElementById('patient-info');
    if (infoContainer) {
        infoContainer.innerHTML = `
            <h4>${patient.full_name}</h4>
            <p>DOB: ${patient.dob} | Gender: ${patient.gender}</p>
            <p>Last Visit: ${patient.last_visit || 'N/A'}</p>
        `;
        infoContainer.style.display = 'block';
    }
}

function setupModalControls() {
    // Open modal buttons
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.classList.add('modal-open');
            }
        });
    });

    // Close modal buttons
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(element => {
        element.addEventListener('click', () => {
            const modal = element.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
            }
        });
    });

    // Prevent modal close when clicking inside
    document.querySelectorAll('.modal-content').forEach(content => {
        content.addEventListener('click', e => e.stopPropagation());
    });
}

function initDataTable() {
    const table = document.getElementById('medical-records-table');
    if (!table) return;

    // Initialize DataTables if the library is available
    if (typeof $.fn.DataTable === 'function') {
        $(table).DataTable({
            responsive: true,
            order: [[0, 'desc']], // Sort by most recent first
            columnDefs: [
                { targets: [0], type: 'date' }, // Assuming first column is date
                { targets: [4, 5], orderable: false } // Action columns
            ]
        });
    }
}

function printMedicalRecord() {
    const printSection = document.getElementById('printable-record');
    if (!printSection) return;

    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <html>
            <head>
                <title>Medical Record</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .print-header { text-align: center; margin-bottom: 20px; }
                    .patient-info { margin-bottom: 15px; }
                    .record-details { margin: 15px 0; }
                    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .footer { margin-top: 30px; font-size: 0.9em; }
                </style>
            </head>
            <body>
                ${printSection.innerHTML}
                <div class="footer">
                    <p>Printed on: ${new Date().toLocaleString()}</p>
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span class="notification-message">${message}</span>
        <button class="notification-close">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Manual close
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.remove();
    });
}

function checkURLParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        showNotification(urlParams.get('success'), 'success');
    }
    
    if (urlParams.has('error')) {
        showNotification(urlParams.get('error'), 'error');
    }
}

function updateUIAfterSubmission(recordId) {
    // Update the UI after successful submission
    // This could include:
    // - Adding the new record to a table
    // - Clearing certain fields
    // - Showing a success message
    
    // Example: Refresh the records table
    const recordsTable = $('#medical-records-table');
    if (recordsTable.DataTable) {
        recordsTable.DataTable().ajax.reload();
    }
    
    // Example: Scroll to the new record
    const newRecord = document.getElementById(`record-${recordId}`);
    if (newRecord) {
        newRecord.scrollIntoView({ behavior: 'smooth' });
    }
}
