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
            background-color: #FFFCF9;
            font-family: 'Inter', sans-serif;
            color: white;
            min-height: 100vh;
            padding-top: 114px;
            margin: 0;
            display: flex; /* Turn body into a flex container */
            flex-direction: column; /* Stack children vertically */
            align-items: center;
        }

        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
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
            font-size: 25px; 
            font-weight: 800; 
            /* Deep Violet to Dark Slate Gradient */
            background-image: linear-gradient(to right, rgb(106, 17, 203), rgb(252, 102, 37));
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-top: -2px;
        }
        
        .header {
            position: fixed;
            z-index: 1000;
            width: 100%;
            height: 83px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
            top: 0;
            left: 0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            padding: 15px;
            padding-left: 34px;
        }


        /* Left Hero Section */
        .hero-section {
            background-color:#FFFCF9;
            padding: 0px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: rgba(106, 17, 203, 0.05); /* Very light purple tint */
            border: 1px solid rgba(106, 17, 203, 0.2);
            color: rgb(106, 17, 203); /* Purple text for AI/Tech feel */
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
            width: fit-content;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            color: #1a1a1a;
            line-height: 1.2;
            margin-bottom: 24px;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 48px;
            max-width: 500px;
            color: #4A5565;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 48px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #E5E7EB;
        }

        .feature-card:hover {
            /* Bridge Gradient Border Effect */
            border-color: transparent;
            background: linear-gradient(#fff, #fff) padding-box,
                        linear-gradient(135deg, rgb(106, 17, 203), rgb(255, 75, 43)) border-box;
            border: 1px solid transparent;
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .feature-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .feature-description {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        /* Right Form Section */
        .form-section {
            padding: 0px 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 600px;
            border-radius: 16px;
            padding: 60px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background: #FFFFFF;
            shadow: 0 25px 20px rgba(255, 111, 97, 0.1);
        }

        .form-logo {
            width: 80px;
            height: 80px;
            /* Bridge Gradient */
            background: linear-gradient(135deg, rgb(106, 17, 203) 0%, rgb(255, 75, 43) 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.2);
            overflow: hidden;
        }

        .form-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .welcome-subtitle {
            font-size: 16px;
            color: #666;
            text-align: center;
            margin-bottom: 32px;
        }

        .role-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 32px;
        }

        .role-btn {
            padding: 16px;
            border: 2px solid #E5E7EB;
            border-radius: 14px;
            background: white;
            color: #666;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Active State: LEARNER (Orange) */
        .role-btn.active[data-role="learner"] {
            border-color: #FF6B35;
            background-color: #FFF5F2; /* Very light orange */
            color: #FF6B35;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
        }

        /* Active State: REVIEWER (Purple) */
        .role-btn.active[data-role="reviewer"] {
            border-color: #7C3AED;
            background-color: #F5F3FF; /* Very light purple */
            color: #7C3AED;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
        }

        .role-btn:hover {
            border-color: rgba(255, 111, 97, 0.5);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: block;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.2s;
            background: white;
            color: #1a1a1a;
        }

        .form-input:focus {
            outline: none;
            background: white;
            border-color: #FF6B35; /* Default focus color is Orange (Action) */
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
        }

        .form-input::placeholder {
            color: #9CA3AF;
        }

        .forgot-link {
            text-align: right;
            margin-top: 8px;
        }

        .forgot-link a {
            color: #FF6B35;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .forgot-link a:hover {
            color: #e55a2b;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, rgb(106, 17, 203), rgb(252, 102, 37));
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 75, 43, 0.5);
        }

        .separator {
            position: relative;
            text-align: center;
            margin: 24px 0;
        }

        .separator::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #E5E7EB;
        }

        .separator-text {
            position: relative;
            background: white;
            padding: 0 16px;
            color: #9CA3AF;
            font-size: 14px;
        }

        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn-social {
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            background: white;
            color: #1a1a1a;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-social:hover {
            border-color: #FF6B35;
            background: #FFF5F2;
        }

        .auth-link {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .auth-link a {
            color: #FF6B35;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link a:hover {
            color: #e55a2b;
        }

        .error-message {
            color: #EF4444;
            font-size: 14px;
            margin-top: 6px;
        }

        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
            }

            .hero-section {
                display: none;
            }

            .form-section {
                padding: 40px 24px;
            }
        }

    </style>
    
</head>
<body>
    @yield('content')
</body>
</html>
