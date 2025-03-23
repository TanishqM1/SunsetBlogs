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

    // Form validation and submission
    const blogForm = document.querySelector('.blog-form');
    
    blogForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Basic form validation
        const title = document.getElementById('blogTitle').value;
        const date = document.getElementById('blogDate').value;
        const author = document.getElementById('blogAuthor').value;
        const category = document.getElementById('blogCategories').value;
        
        if (!title || !date || !author || !category) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Validate subtitles and content
        const subtitles = document.querySelectorAll('input[name="subtitles[]"]');
        const contents = document.querySelectorAll('textarea[name="subtitles-content[]"]');
        
        let isValid = true;
        let contentArray = [];
        
        subtitles.forEach((subtitle, index) => {
            if (!subtitle.value || !contents[index].value) {
                isValid = false;
            } else {
                contentArray.push({
                    subtitle: subtitle.value,
                    content: contents[index].value
                });
            }
        });
        
        if (!isValid) {
            alert('Please fill in all subtitle and content fields');
            return;
        }

        // Create FormData object for file uploads
        const formData = new FormData();
        formData.append('title', title);
        formData.append('date', date);
        formData.append('author', author);
        formData.append('category', category);
        formData.append('content', JSON.stringify(contentArray));
        formData.append('additionalAuthors', document.getElementById('additionalAuthors').value || '');
        formData.append('mediaLinks', document.getElementById('mediaLinks').value || '');
        formData.append('tags', document.getElementById('blogTags').value || '');
        
        // Add files if they exist
        const blogImage = document.getElementById('blogImage').files[0];
        const thumbnailImage = document.getElementById('thumbnailImage').files[0];
        
        if (blogImage) {
            formData.append('blogImage', blogImage);
        }
        if (thumbnailImage) {
            formData.append('thumbnailImage', thumbnailImage);
        }

        try {
            console.log('Sending form data...');
            const response = await fetch('create_post.php', {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);
            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Failed to parse response as JSON:', parseError);
                console.error('Raw response was:', responseText);
                throw new Error('Server returned invalid JSON response');
            }

            console.log('Parsed response data:', result);
            
            if (result.success) {
                alert('Blog post created successfully!');
                window.location.href = 'your-work.php'; // Redirect to user's posts
            } else {
                console.error('Server error:', result.debug_message); // Log the debug message
                alert(result.message || 'An error occurred while creating the post');
            }
        } catch (error) {
            console.error('Error details:', error);
            alert('An error occurred while creating the post. Please try again.');
        }
    });
}); 