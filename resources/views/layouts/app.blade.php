<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - From practice to pro — powered by AI</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #000000;
            font-family: 'Inter', sans-serif;
            color: white;
            min-height: 100vh;
            padding-top: 114px;
            margin: 0;
            display: flex; /* Turn body into a flex container */
            flex-direction: column; /* Stack children vertically */
            align-items: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            margin: auto;
            width: 100%;
            max-width: 100%;
        }
        
        .auth-card {
            background-color: #111827;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 0 20px 4px rgba(255, 255, 255, 0.6);
            width: 100%;
            max-width: 556px;
            border: 1px solid #6D6D6D;
            overflow: auto;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            background-color: #f97316;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            overflow: hidden;
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .logo-text {
    
            font-family: 'Georgia', serif; 
            font-size: 25px; 
            font-weight: 800; 
            background-image: linear-gradient(to right,rgb(250, 90, 75) 30%,rgb(251, 192, 90));
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
        }
        
        .header {
            position: fixed;
            z-index: 1000;
            width: 100%;
            height: 83px;
            background-color: #111827;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(131, 131, 131,0.5);
            top: 0;
            left: 0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            padding: 15px;
            padding-left: 34px;
        }
        
        .main-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.4), 0 10px 20px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .main-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
        }
        
        .main-logo-text {
            color: white;
            font-size: 28px;
            font-weight: bold;
        }
        
        .welcome-title {
            font-size: 32px;
            font-family: 'Inter', sans-serif;
            font-weight: bold;
            margin-bottom: 12px;
            text-align: center;
            background-image: linear-gradient(to right,rgb(250, 90, 75) 30%,rgb(251, 192, 90));
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
        }
        
        .welcome-subtitle {
            color: #C6C6C6;
            font-size: 18px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-input {
            width: 100%;
            padding: 18px 20px;
            border: 1px solid #4a5568;
            border-radius: 12px;
            background-color: #E5E7EB;
            color: black;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
            background-color: #E5E7EB;
        }
        
        .form-input::placeholder {
            color: #a0aec0;
        }
        
        .btn-primary {
            width: 100%;
            padding: 18px 20px;
            background: linear-gradient(to right,rgb(255, 68, 50)29%,rgb(255, 184, 61));
            border: none;
            border-radius: 12px;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #e55a2b, #f59e0b);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
            transform: translateY(-2px);
        }
        
        .separator {
            position: relative;
            margin: 32px 0;
        }
        
        .separator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #4a5568;
        }
        
        .separator-text {
            position: relative;
            background-color: #2d3748;
            padding: 0 16px;
            color: #a0aec0;
            font-size: 14px;
            text-align: center;
        }
        
        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .btn-social {
            padding: 16px 20px;
            border: 1px solid #4a5568;
            border-radius: 12px;
            background-color: #2d3748;
            color: white;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-social:hover {
            background-color: #4a5568;
            border-color: #ff6b35;
        }
        
        .auth-link {
            text-align: center;
            color: #a0aec0;
            font-size: 14px;
        }
        
        .auth-link a {
            color: #ff6b35;
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-link a:hover {
            color: #fbbf24;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .dashboard-header {
            background-color: #1f2937;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 24px;
            padding-bottom: 24px;
        }
        
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 48px 20px;
        }
        
        .dashboard-title {
            font-size: 36px;
            font-weight: bold;
            color: white;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .dashboard-subtitle {
            font-size: 20px;
            color: #d1d5db;
            margin-bottom: 32px;
            text-align: center;
        }
        
        .dashboard-card {
            background-color: #1f2937;
            border-radius: 16px;
            padding: 32px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .dashboard-card h2 {
            font-size: 24px;
            font-weight: 600;
            color: white;
            margin-bottom: 16px;
        }
        
        .dashboard-card p {
            color: #d1d5db;
            margin-bottom: 24px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .feature-card {
            background-color: #374151;
            border-radius: 8px;
            padding: 24px;
        }
        
        .feature-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: white;
            margin-bottom: 8px;
        }
        
        .feature-card p {
            color: #d1d5db;
            font-size: 14px;
        }
        
        .logout-btn {
            color: #f97316;
            text-decoration: none;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            color: #ea580c;
        }
        
        @media (max-width: 768px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .social-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
