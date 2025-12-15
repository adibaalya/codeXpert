<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.profileCSS')
    @include('layouts.dashboardCSS')
    @include('layouts.navCSS')
    <style>
        .edit-profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .edit-profile-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .edit-profile-header {
            margin-bottom: 40px;
        }

        .edit-profile-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .edit-profile-header p {
            font-size: 14px;
            color: #8e8e93;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #FF6B35;
        }

        .form-input:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        .photo-upload-section {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e5e7eb;
            border-radius: 16px;
        }

        .current-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
            overflow: hidden;
            flex-shrink: 0;
        }

        .current-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .current-photo svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        .photo-upload-content {
            flex: 1;
        }

        .photo-upload-content h3 {
            font-size: 16px;
            font-weight: 700;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .photo-upload-content p {
            font-size: 13px;
            color: #8e8e93;
            margin-bottom: 16px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #2d2d2d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .upload-btn:hover {
            border-color: #FF6B35;
            color: #FF6B35;
        }

        .remove-photo-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 2px solid #ef4444;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #ef4444;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 12px;
        }

        .remove-photo-btn:hover {
            background: #ef4444;
            color: white;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-save {
            flex: 1;
            padding: 14px 24px;
            background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
        }

        .btn-cancel {
            flex: 1;
            padding: 14px 24px;
            background: white;
            color: #6e6e73;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            border-color: #d1d5db;
            color: #2d2d2d;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #16a34a;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        .file-name-display {
            margin-top: 8px;
            font-size: 13px;
            color: #6e6e73;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert_logo.jpg') }}" alt="CodeXpert Logo">
            <span class="logo-text">CodeXpert</span>
        </div>
        
        <div class="header-right">
            <nav class="nav-menu">
                <button class="nav-item" onclick="window.location.href='{{ route('learner.dashboard') }}'">Dashboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.practice') }}'">Practice</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.leaderboard') }}'">Leaderboard</button>
                <button class="nav-item" onclick="window.location.href='{{ route('learner.hackathon') }}'">Hackathon</button>
                <button class="nav-item active" onclick="window.location.href='{{ route('learner.profile') }}'">Profile</button>
            </nav>

            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">{{ $learner->username }}</div>
                    <div class="user-role">Learner</div>
                </div>
                <div class="user-avatar" onclick="toggleUserMenu(event)">
                    {{ strtoupper(substr($learner->username, 0, 1)) }}{{ strtoupper(substr($learner->username, 1, 1) ?? '') }}
                </div>
                
                <!-- User Dropdown Menu -->
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr($learner->username, 0, 2)) }}
                        </div>
                        <div>
                            <div class="user-dropdown-name">{{ $learner->username }}</div>
                            <div class="user-dropdown-email">{{ $learner->email }}</div>
                        </div>
                    </div>
                    
                    @if($learner->badge)
                    <div class="verified-badge-dropdown">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>{{ $learner->badge }} Badge</span>
                    </div>
                    @endif
                    
                    <a href="{{ route('learner.customization') }}" class="user-dropdown-item">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Customize Learning Path</span>
                    </a>
                    
                    <div class="user-dropdown-divider"></div>
                    
                    <form method="POST" action="{{ route('learner.logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="user-dropdown-item logout">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="edit-profile-container">
        <div class="edit-profile-card">
            <div class="edit-profile-header">
                <h1>Edit Profile</h1>
                <p>Update your profile information and photo</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('learner.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Profile Photo Section -->
                <div class="form-group">
                    <label class="form-label">Profile Photo</label>
                    <div class="photo-upload-section">
                        <div class="current-photo" id="photoPreview">
                            @if($learner->profile_photo)
                                <img src="{{ asset('storage/' . $learner->profile_photo) }}" alt="Profile Photo">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            @endif
                        </div>
                        <div class="photo-upload-content">
                            <h3>Upload Profile Picture</h3>
                            <p>JPG, PNG or GIF. Max size of 2MB. Recommended size: 400x400px</p>
                            <div class="file-input-wrapper">
                                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="previewPhoto(event)">
                                <label for="profile_photo" class="upload-btn">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Choose Photo
                                </label>
                            </div>
                            @if($learner->profile_photo)
                                <button type="button" class="remove-photo-btn" onclick="removePhoto()">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove Photo
                                </button>
                                <input type="hidden" id="remove_photo" name="remove_photo" value="0">
                            @endif
                            <div class="file-name-display" id="fileName"></div>
                        </div>
                    </div>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input" value="{{ old('username', $learner->username) }}" required maxlength="50">
                </div>

                <!-- Email (Readonly) -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" class="form-input" value="{{ $learner->email }}" disabled>
                    <small style="display: block; margin-top: 8px; font-size: 12px; color: #8e8e93;">
                        Email cannot be changed. Contact support if you need to update your email.
                    </small>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="window.location.href='{{ route('learner.profile') }}'">
                        Cancel
                    </button>
                    <button type="submit" class="btn-save">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle User Dropdown Menu
        function toggleUserMenu(event) {
            event.stopPropagation();
            const userDropdown = document.getElementById('userDropdown');
            userDropdown.classList.toggle('show');
        }

        // Close User Dropdown Menu when clicking outside
        window.onclick = function(event) {
            const userDropdown = document.getElementById('userDropdown');
            if (!event.target.matches('.user-avatar')) {
                if (userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                }
            }
        }

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
    </script>
</body>
</html>
