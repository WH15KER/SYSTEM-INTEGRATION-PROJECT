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
    
    // Initialize calendar
    renderCalendar(currentDate);
    
    // Next button functionality
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nextStep = parseInt(this.getAttribute('data-next'));
            
            if (validateStep(currentStep)) {
                updateStep(currentStep, nextStep);
                currentStep = nextStep;
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
            
            setTimeout(() => {
                alert('Appointment booked successfully! A confirmation has been sent to your email.');
                submitButton.disabled = false;
                buttonText.textContent = "Confirm Appointment";
                spinner.classList.add('hidden');
            }, 2000);
        });
    }
    
    // Validate step before proceeding
    function validateStep(step) {
        let isValid = true;
        
        if (step === 1) {
            const selectedService = document.querySelector('input[name="service"]:checked');
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
            const requiredFields = appointmentForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields');
            } else {
                updateConfirmationDetails();
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
        
        for (let hour = startHour; hour <= endHour; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                const displayTime = `${hour > 12 ? hour - 12 : hour}:${minute.toString().padStart(2, '0')} ${hour >= 12 ? 'PM' : 'AM'}`;
                
                const isAvailable = Math.random() > 0.3;
                
                const timeSlot = document.createElement('div');
                timeSlot.className = `time-slot ${isAvailable ? '' : 'unavailable'}`;
                timeSlot.textContent = displayTime;
                
                if (isAvailable) {
                    timeSlot.addEventListener('click', function() {
                        document.querySelectorAll('.time-slot').forEach(slot => {
                            slot.classList.remove('selected');
                        });
                        
                        this.classList.add('selected');
                        selectedTime = timeString;
                    });
                }
                
                timeSlotsContainer.appendChild(timeSlot);
            }
        }
    }
    
    // Update confirmation details
    function updateConfirmationDetails() {
        const service = document.querySelector('input[name="service"]:checked');
        if (service) {
            document.getElementById('confirm-service').textContent = service.value;
            document.getElementById('confirm-price').textContent = 
                service.id === 'general-checkup' ? 'Php 500' :
                service.id === 'blood-test' ? 'Php 300' :
                service.id === 'vision-test' ? 'Php 400' : 'Php 600';
        }
        
        if (selectedDate) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('confirm-date').textContent = selectedDate.toLocaleDateString('en-US', options);
            
            if (selectedTime) {
                const [hours, minutes] = selectedTime.split(':');
                const time = new Date();
                time.setHours(parseInt(hours));
                time.setMinutes(parseInt(minutes));
                
                document.getElementById('confirm-time').textContent = 
                    time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                
                document.getElementById('confirm-duration').textContent = '30 minutes';
            }
        }
        
        document.getElementById('confirm-name').textContent = document.getElementById('fullName').value;
        document.getElementById('confirm-email').textContent = document.getElementById('email').value;
        document.getElementById('confirm-phone').textContent = document.getElementById('phone').value;
    }
});