<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - CodeXpert</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('layouts.profileCSS')
    @include('layouts.navCSS')
    <script src="{{ asset('js/navBar.js') }}"></script>
    <script src="{{ asset('js/edit-profile.js') }}"></script>
    
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            <img class="logo" src="{{ asset('assets/images/codeXpert.png') }}" alt="CodeXpert Logo">
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
                        <div class="current-photo-learner" id="photoPreview">
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
                                <label for="profile_photo" class="upload-btn-learner">
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
                    <input type="text" id="username" name="username" class="form-input-learner" value="{{ old('username', $learner->username) }}" required maxlength="50">
                </div>

                <!-- Email (Readonly) -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" class="form-input-learner" value="{{ $learner->email }}" disabled>
                    <small style="display: block; margin-top: 8px; font-size: 12px; color: #8e8e93;">
                        Email cannot be changed. Contact support if you need to update your email.
                    </small>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="window.location.href='{{ route('learner.profile') }}'">
                        Cancel
                    </button>
                    <button type="submit" class="btn-save-learner">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
