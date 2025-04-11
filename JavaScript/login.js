document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        console.log('Attempting login for:', email);
        const response = await fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        console.log('Server response:', data);
        
        if (data.success) {
            console.log('Login successful!');
            console.log('User details - Username:', data.username, 'isAdmin:', data.isAdmin);
            
            // Explicitly check the isAdmin property type and value
            console.log('isAdmin type:', typeof data.isAdmin);
            console.log('isAdmin value:', data.isAdmin);
            
            // Redirect Admin users to profile.php (admin dashboard) and regular users to home.html
            if (data.isAdmin === true) {
                console.log('Redirecting to profile.php (Admin dashboard)');
                window.location.href = 'profile.php';
            } else {
                console.log('Redirecting to home.html (Regular user)');
                window.location.href = 'home.html';
            }
        } else {
            console.log('Login failed:', data.message);
            showError(data.message);
        }
    } catch (error) {
        console.error('Error during login process:', error);
        showError('An error occurred. Please try again.');
    }
});

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    const form = document.getElementById('login-form');
    const existingError = form.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    form.insertBefore(errorDiv, form.firstChild);
} 