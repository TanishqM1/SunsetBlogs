// Get the elements
const getStartedButton = document.querySelector('.cta-button');
const authOptions = document.getElementById('auth-options');

// Function to show the auth options
function showAuthOptions() {
    // Hide the Get Started button
    getStartedButton.style.display = 'none';
    // Show the auth options
    authOptions.style.display = 'flex';
}

// Close auth options when clicking outside
document.addEventListener('click', function(event) {
    const authButtons = document.getElementById('auth-buttons');
    if (!authButtons.contains(event.target)) {
        authOptions.style.display = 'none';
        getStartedButton.style.display = 'inline-block';
    }
});

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        authOptions.style.display = 'none';
        getStartedButton.style.display = 'inline-block';
    }
}); 