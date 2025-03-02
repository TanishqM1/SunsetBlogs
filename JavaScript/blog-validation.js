document.addEventListener('DOMContentLoaded', function() {
    let subtitleCount = 1;

    window.addSubtitle = function() {
        subtitleCount++;
        
        const subtitleContainer = document.getElementById('subtitle-container');
        const newSubtitleGroup = document.createElement('div');
        newSubtitleGroup.className = 'subtitle-group';
        
        // Create delete button for the subtitle group
        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn-delete-subtitle';
        deleteButton.innerHTML = '✕';
        deleteButton.onclick = function() {
            newSubtitleGroup.remove();
        };
        
        newSubtitleGroup.innerHTML = `
            <div class="subtitle-header">
                <label for="subtitle${subtitleCount}" class="required">Subtitle</label>
                <input type="text" id="subtitle${subtitleCount}" name="subtitles[]" placeholder="Enter your subtitle" required>
            </div>
            <div class="subtitle-content">
                <label for="content${subtitleCount}" class="required">Content</label>
                <textarea id="content${subtitleCount}" name="subtitles-content[]" rows="4" placeholder="Write content for this subtitle" required></textarea>
            </div>
        `;
        
        // Add the delete button to the subtitle group
        newSubtitleGroup.insertBefore(deleteButton, newSubtitleGroup.firstChild);
        
        subtitleContainer.appendChild(newSubtitleGroup);
    };

    // Form validation
    const blogForm = document.querySelector('.blog-form');
    
    blogForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic form validation
        const title = document.getElementById('blogTitle').value;
        const date = document.getElementById('blogDate').value;
        const author = document.getElementById('blogAuthor').value;
        
        if (!title || !date || !author) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Validate subtitles and content
        const subtitles = document.querySelectorAll('input[name="subtitles[]"]');
        const contents = document.querySelectorAll('textarea[name="subtitles-content[]"]');
        
        let isValid = true;
        
        subtitles.forEach((subtitle, index) => {
            if (!subtitle.value || !contents[index].value) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            alert('Please fill in all subtitle and content fields');
            return;
        }
        
        // If all validation passes, you can submit the form
        // For now, we'll just show a success message
        alert('Blog post created successfully!');
    });
}); 