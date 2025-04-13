document.addEventListener('DOMContentLoaded', function() {
    // Appointment Form Functionality
    const appointmentForm = document.getElementById('appointmentForm');
    const formSteps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('.step');
    const nextButtons = document.querySelectorAll('.btn-next');
    const prevButtons = document.querySelectorAll('.btn-prev');
    
    let currentStep = 1;
    let currentDate = new Date();
    let selectedDate = null;
    let selectedTime = null;
    let selectedService = null;
    
    // Initialize calendar
    renderCalendar(currentDate);
    
    // Next button functionality
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nextStep = parseInt(this.getAttribute('data-next'));
            
            if (validateStep(currentStep)) {
                updateStep(currentStep, nextStep);
                currentStep = nextStep;
                
                // Update hidden fields before submission
                if (nextStep === 4) {
                    document.getElementById('hidden-service-id').value = selectedService;
                    document.getElementById('hidden-appointment-date').value = selectedDate ? formatDateForInput(selectedDate) : '';
                    document.getElementById('hidden-start-time').value = selectedTime || '';
                }
            }
        });
    });
    
    // Previous button functionality
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prevStep = parseInt(this.getAttribute('data-prev'));
            updateStep(currentStep, prevStep);
            currentStep = prevStep;
        });
    });
    
    // Month navigation
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });
    
    // Service selection
    document.querySelectorAll('input[name="service_id"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                selectedService = this.value;
                const service = services.find(s => s.service_id === this.value);
                if (service) {
                    document.getElementById('confirm-service').textContent = service.name;
                    document.getElementById('confirm-price').textContent = 'Php ' + parseFloat(service.price).toFixed(2);
                    document.getElementById('confirm-duration').textContent = service.duration_minutes + ' minutes';
                }
            }
        });
    });
    
    // Form submission
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = document.querySelector('.btn-submit');
            const spinner = submitButton.querySelector('.spinner');
            const buttonText = submitButton.querySelector('.button-text');
            
            submitButton.disabled = true;
            buttonText.textContent = "Processing...";
            spinner.classList.remove('hidden');
            
            // Submit form programmatically
            this.submit();
        });
    }
    
    // Validate step before proceeding
    function validateStep(step) {
        let isValid = true;
        
        if (step === 1) {
            const selectedService = document.querySelector('input[name="service_id"]:checked');
            if (!selectedService) {
                alert('Please select a service');
                isValid = false;
            }
        } else if (step === 2) {
            if (!selectedDate) {
                alert('Please select a date');
                isValid = false;
            } else if (!selectedTime) {
                alert('Please select a time slot');
                isValid = false;
            }
        } else if (step === 3) {
            const termsChecked = document.getElementById('appointment-terms').checked;
            if (!termsChecked) {
                alert('Please agree to the terms of service');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Update step visibility
    function updateStep(current, next) {
        document.querySelector(`.form-step[data-step="${current}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${current}"]`).classList.remove('active');
        
        document.querySelector(`.form-step[data-step="${next}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${next}"]`).classList.add('active');
    }
    
    // Render calendar
    function renderCalendar(date) {
        const calendarGrid = document.getElementById('calendarGrid');
        const monthYearDisplay = document.getElementById('currentMonth');
        
        calendarGrid.innerHTML = '';
        
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'];
        monthYearDisplay.textContent = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
        
        const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
        const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
        
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(day => {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day-header';
            dayElement.textContent = day;
            calendarGrid.appendChild(dayElement);
        });
        
        for (let i = 0; i < firstDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day disabled';
            calendarGrid.appendChild(emptyDay);
        }
        
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = i;
            
            const currentDay = new Date(date.getFullYear(), date.getMonth(), i);
            
            if (currentDay < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
                dayElement.classList.add('disabled');
            } else {
                dayElement.addEventListener('click', function() {
                    document.querySelectorAll('.calendar-day').forEach(day => {
                        day.classList.remove('selected');
                    });
                    
                    this.classList.add('selected');
                    selectedDate = currentDay;
                    generateTimeSlots();
                });
            }
            
            if (currentDay.toDateString() === today.toDateString()) {
                dayElement.classList.add('today');
            }
            
            calendarGrid.appendChild(dayElement);
        }
    }
    
    // Generate time slots
    function generateTimeSlots() {
        const timeSlotsContainer = document.getElementById('timeSlots');
        timeSlotsContainer.innerHTML = '';
        
        if (!selectedDate) return;
        
        selectedTime = null;
        
        const startHour = 8;
        const endHour = 17;
        const dateStr = formatDateForInput(selectedDate);
        
        for (let hour = startHour; hour <= endHour; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}:00`;
                const displayTime = `${hour > 12 ? hour - 12 : hour}:${minute.toString().padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
                
                // Check if this slot is available
                let isAvailable = true;
                
                // Check against booked slots
                const slotStart = new Date(`${dateStr}T${timeString}`);
                const slotEnd = new Date(slotStart.getTime() + 30 * 60000); // 30 minutes
                
                for (const slot of bookedSlots) {
                    const bookedStart = new Date(`${slot.appointment_date}T${slot.start_time}`);
                    const bookedEnd = new Date(`${slot.appointment_date}T${slot.end_time}`);
                    
                    if (slotStart < bookedEnd && slotEnd > bookedStart) {
                        isAvailable = false;
                        break;
                    }
                }
                
                const timeSlot = document.createElement('div');
                timeSlot.className = `time-slot ${isAvailable ? '' : 'unavailable'}`;
                timeSlot.textContent = displayTime;
                timeSlot.dataset.time = timeString;
                
                if (isAvailable) {
                    timeSlot.addEventListener('click', function() {
                        document.querySelectorAll('.time-slot').forEach(slot => {
                            slot.classList.remove('selected');
                        });
                        
                        this.classList.add('selected');
                        selectedTime = this.dataset.time;
                        
                        // Update confirmation details
                        document.getElementById('confirm-date').textContent = selectedDate.toLocaleDateString('en-US', { 
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
                        });
                        
                        const [hours, minutes] = selectedTime.split(':');
                        const time = new Date();
                        time.setHours(parseInt(hours));
                        time.setMinutes(parseInt(minutes));
                        
                        document.getElementById('confirm-time').textContent = 
                            time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                    });
                }
                
                timeSlotsContainer.appendChild(timeSlot);
            }
        }
    }
    
    // Format date for input field (YYYY-MM-DD)
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
});