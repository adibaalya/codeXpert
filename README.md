# CodeXpert - AI-Powered Coding Platform

A Laravel-based authentication system with a modern dark theme design for CodeXpert - "From practice to pro — powered by AI."

## Features

- **Modern Dark Theme**: Beautiful dark UI with orange accents matching the CodeXpert brand
- **Authentication System**: Complete login and registration functionality
- **Responsive Design**: Works perfectly on desktop and mobile devices
- **Laravel Framework**: Built with Laravel 11 for robust backend functionality

## Installation

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd codeXpert
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**:
   ```bash
   php artisan migrate
   ```

5. **Start the development server**:
   ```bash
   php artisan serve
   ```

6. **Visit the application**:
   Open your browser and go to `http://localhost:8000`

## Authentication Routes

- `/login` - Login page
- `/register` - Registration page
- `/dashboard` - Protected dashboard (requires authentication)
- `/logout` - Logout functionality

## Features Implemented

### Login Page
- Modern dark theme with CodeXpert branding
- Email/password authentication
- Social login buttons (GitHub, Google) - UI ready
- Responsive design
- Form validation with error handling

### Registration Page
- Complete registration form with name, email, password, and confirmation
- Same modern design as login page
- Password confirmation validation
- Automatic login after registration

### Dashboard
- Protected route requiring authentication
- Welcome message with user's name
- Logout functionality
- Modern dark theme consistency

## Design Features

- **Color Scheme**: Black background with gray cards and orange accents
- **Typography**: Clean, modern fonts with proper hierarchy
- **Logo**: Custom CodeXpert logo with "CX" branding
- **Responsive**: Mobile-first design that works on all screen sizes
- **Animations**: Smooth transitions and hover effects

## Social Authentication (Ready for Implementation)

The UI includes buttons for GitHub and Google authentication. To implement:

1. Install Laravel Socialite:
   ```bash
   composer require laravel/socialite
   ```

2. Configure OAuth providers in `config/services.php`

3. Add social authentication routes and controllers

## File Structure

```
app/
├── Http/Controllers/Auth/
│   ├── LoginController.php
│   └── RegisterController.php
resources/views/
├── layouts/
│   └── app.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── dashboard.blade.php
routes/
└── web.php
```

## Technologies Used

- **Laravel 11**: PHP framework
- **Blade Templates**: Laravel's templating engine
- **Tailwind CSS**: Utility-first CSS framework
- **HTML5**: Modern semantic markup
- **JavaScript**: For interactive elements

## Next Steps

1. Implement social authentication (GitHub, Google)
2. Add email verification
3. Implement password reset functionality
4. Add user profile management
5. Build the main CodeXpert application features

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).