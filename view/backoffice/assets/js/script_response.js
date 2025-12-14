// Custom JavaScript for comment form
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for comments
    const form = document.getElementById('form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            // Prevent form submission
            event.preventDefault();
            
            // Get form values
            const author = document.getElementById('author').value.trim();
            const message = document.getElementById('message').value.trim();
            const currentTime = document.getElementById('currentTime').value.trim();
            
            // Validation checks
            let isValid = true;
            let errorMessage = '';
            
            // Check if author is empty
            if (author === '') {
                isValid = false;
                errorMessage += '• Author field is required\n';
            }
            
            // Check if message is empty
            if (message === '') {
                isValid = false;
                errorMessage += '• Message field is required\n';
            }
            
            // No image validation for comments
            
            // If form is valid, submit it
            if (isValid) {
                form.submit();
            } else {
                // Show error message
                alert('Please complete the following fields:\n' + errorMessage);
            }
        });
    }

    // Time functionality
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.value = timeString;
        }
    }
    
    // Update time immediately and set interval for continuous updates
    updateTime();
    setInterval(updateTime, 1000);
});