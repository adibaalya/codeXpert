// Preview uploaded photo
function previewPhoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('photoPreview');
    const fileName = document.getElementById('fileName');
    
    if (file) {
        // Check file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            event.target.value = '';
            return;
        }

        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile Photo">';
            fileName.textContent = 'Selected: ' + file.name;
        }
        reader.readAsDataURL(file);

        // Reset remove photo flag
        const removePhotoInput = document.getElementById('remove_photo');
        if (removePhotoInput) {
            removePhotoInput.value = '0';
        }
    }
}

// Remove photo
function removePhoto() {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        const preview = document.getElementById('photoPreview');
        const removePhotoInput = document.getElementById('remove_photo');
        const fileInput = document.getElementById('profile_photo');
        const fileName = document.getElementById('fileName');
        
        // Reset to default avatar
        preview.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        `;
        
        // Set flag to remove photo
        removePhotoInput.value = '1';
        fileInput.value = '';
        fileName.textContent = 'Photo will be removed when you save';
    }
}