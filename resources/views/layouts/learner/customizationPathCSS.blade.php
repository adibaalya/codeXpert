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

        body {font-family: 'Inter', sans-serif;
        background: #FEF8F5;
        min-height: 100vh;
        }

        .customization-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .title-text {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(to right,rgb(250, 90, 75) 10%,rgb(250, 90, 75));
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-bottom: 8px;
            text-align: center;
        }

        .subtitle {
            font-size: 16px;
            color: #6B7280;
            margin-bottom: 30px;
            text-align: center;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #1a202c;
        }

        .section-title-icon {
            width: 28px;
            height: 28px;
        }

        .languages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .language-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .language-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .language-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .language-icon.orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .language-icon.yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }
        .language-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .language-icon.purple { background: linear-gradient(135deg, #ec4899, #db2777); }
        .language-icon.teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

        .language-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .language-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
            line-height: 1.2;
        }

        .language-level {
            background:#FF6B35;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
        }

        .remove-btn {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-btn:hover {
            color: #fff;
            background: #ef4444;
            border-color: #ef4444;
            transform: scale(1.1);
        }

        .remove-btn svg {
            width: 20px;
            height: 20px;
        }

        .add-language-section {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .add-language-btn {
            background: white;
            border: 2px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
        }

        .add-language-btn:hover {
            border-color: rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .add-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 300;
            color: white;
            flex-shrink: 0;
        }

        .language-btn-text {
            flex: 1;
            text-align: left;
        }

        .text-lng {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .text-sm {
            font-size: 14px;
            color: #718096;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #718096;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .empty-icon {
            font-size: 64px;
            color: #cbd5e0;
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 16px;
            color: #a0aec0;
        }

        .start-learning-section {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .start-btn {
            background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
            border: none;
            border-radius: 12px;
            padding: 18px 48px;
            cursor: pointer;
            transition: all 0.3s ease;
            max-width: 400px;
            width: 100%;
            font-size: 18px;
            font-weight: 700;
            color: white;
            text-align: center;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        .start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
        }

        .start-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 700px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f7fafc;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 26px;
            font-weight: 700;
            color: #1a202c;
        }
        
        h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #4a5568;
        }

        .close-btn {
            background: transparent;
            border: none;
            color: #a0aec0;
            font-size: 32px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .close-btn:hover {
            color: #1a202c;
            background: #f7fafc;
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .language-option {
            background: #f7fafc;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .language-option:hover {
            background: #edf2f7;
            border-color: #e2e8f0;
        }

        .language-option.selected {
            border-color: #FF8A5B;
            background: linear-gradient(135deg, #FFF5F0 0%, #FFE8DD 100%);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.25);
            transform: translateY(-2px);
        }

        .language-option .language-icon {
            margin: 0 auto 12px;
        }

        .language-option .text-sm {
            font-weight: 600;
            color: #2d3748;
        }

        .level-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .level-option {
            background: #f7fafc;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .level-option:hover {
            transform: translateY(-2px);
            border-color: #e2e8f0;
        }

        .level-option.selected,
        .level-option.border-indigo-500 {
            background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
            color: white;
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
        }

        .level-option.selected .level-name,
        .level-option.border-indigo-500 .level-name {
            color: white;
            font-weight: 700;
        }

        .level-name {
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 2px solid #f7fafc;
        }

        .btn {
            padding: 12px 28px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .help-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: white;
            color: #3b82f6;
            border: 2px solid #e2e8f0;
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .help-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        @media (max-width: 768px) {
            .languages-grid {
                grid-template-columns: 1fr;
            }

            .language-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .level-grid {
                grid-template-columns: 1fr;
            }

            .title-text {
                font-size: 32px;
            }
        }
    </style>

</head>
<body>
    @yield('content')
</body>
</html>