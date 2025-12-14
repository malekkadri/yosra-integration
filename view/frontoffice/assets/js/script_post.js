// URL parameter handling
function handleUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    const errorMsg = urlParams.get('error');
    const successMsg = urlParams.get('success');
    
    if (errorMsg) {
        alert('⚠️ Upload Warning: ' + errorMsg);
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    } else if (successMsg) {
        alert('✅ Post added successfully!');
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}

// Form validation
function setupFormValidation() {
    const form = document.getElementById('form');
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const author = document.getElementById('author').value.trim();
        const message = document.getElementById('message').value.trim();
        const imageInput = document.getElementById('image');
        
        let isValid = true;
        let errorMessage = '';
        
        if (author === '') {
            isValid = false;
            errorMessage += '• Title field is required\n';
        }
        
        if (message === '') {
            isValid = false;
            errorMessage += '• Message field is required\n';
        }
        
        if (imageInput.files.length === 0) {
            isValid = false;
            errorMessage += '• Please select an image\n';
        }
        
        if (isValid) {
            form.submit();
        } else {
            alert('Please complete the following fields:\n' + errorMessage);
        }
    });
}

// Time functionality
function setupTimeUpdates() {
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.value = timeString;
        }
    }
    
    updateTime();
    setInterval(updateTime, 1000);
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    handleUrlMessages();
    setupFormValidation();
    setupTimeUpdates();
});