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
            font-family: 'Inter', sans-serif;
            background: #FEF8F5;
            min-height: 100vh;
        }

        .title-text {
            background-image: linear-gradient(to right,rgb(250, 90, 75) 30%,rgb(251, 192, 90));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 48px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #99A1AF;
            font-size: 18px;
            margin-bottom: 50px;
            text-align: center;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .languages-container {
            width: 100%;
            max-width: 600px;
            margin-bottom: 30px;
        }

        .language-card {
            background: #111827;
            border-radius: 12px;
            border: 2px solid #364153;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .language-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .language-icon.orange { background: linear-gradient(135deg, #ff8a00, #ff6b00); }
        .language-icon.yellow { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; }
        .language-icon.blue { background: linear-gradient(135deg, #4a90e2, #357abd); }
        .language-icon.purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }

        /* Fix 1: Ensure the icon and info block are vertically aligned */
        .language-card .flex.items-center { 
            display: flex;
            align-items: center; 
        }

        /* Fix 2: Ensure the name and level stack vertically inside the info block */
        .language-info {
            margin-left: 15px;
            flex: 1;
            display: flex; /* Make it a flex container */
            flex-direction: column; /* Stack children (name and level) vertically */
        }

        .language-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .language-level {
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
        }

        .remove-btn {
            background: transparent;
            border: none;
            color: #ff4444;
            cursor: pointer;
            font-size: 20px;
            padding: 5px 10px;
        }

        .remove-btn:hover {
            color: #ff0000;
        }

        .add-language-btn {
            background: #111827;
            border: 2px dashed #364153;
            border-radius: 14px;
            padding: 20px;
            width: 100%;
            max-width: 350px;
            height: 120px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 30px;
            margin-left: auto;
            margin-right: auto;
        }

        .add-language-btn:hover {
            border-color: #ff6b6b;
        }

        .add-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(100deg,rgb(237, 95, 82)29%,rgb(255, 184, 61));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 300;
            color: white;
        }

        .text-lng {
            font-size: 18px;
            font-weight: 600;
            color:white;
            text-align: left;
        }
        
        .text-sm {
            font-size: 14px;
            color: #99A1AF;
            text-align: left;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #1a1f2e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }

        .start-btn {
            background: linear-gradient(to right,rgb(255, 68, 50)29%,rgb(255, 184, 61));
            border: none;
            border-radius: 12px;
            padding: 16px 40px;
            cursor: pointer;
            display: flex;
            gap: 10px;
            transition: transform 0.2s;
            width: 100%;
            max-width: 400px;
            height: 60px;
            margin: 0 auto;
            font-size: 18px;
            font-weight: 700;
            color: white;
            text-align: center;
            justify-content: center;
        }

        .start-btn:hover {
            transform: scale(1.05);
        }

        .start-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #1a1f2e;
            border: 2px solid #ff6b6b;
            border-radius: 20px;
            padding: 40px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
        }
        
        h3 {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 15px;
            color: #99A1AF;
        }

        .text-sm{
            font-size: 14px;
            color: #888;
            margin-top: 5px;
        }

        .close-btn {
            background: transparent;
            border: none;
            color: #888;
            font-size: 30px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
        }

        .close-btn:hover {
            color: #fff;
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .language-option {
            background: #252b3e;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 20px;
            allign-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .language-option:hover {
            background: #2d3548;
        }

        .language-option.selected {
            border-color: #ff6b6b;
            background: #2d3548;
        }

        .level-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .level-option {
            background: #252b3e;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .level-option:hover {
            transform: translateY(-5px);
        }

        .level-option.selected {
            border-color: #fff;
        }

        .level-option.beginner { background: linear-gradient(135deg, #10b981, #059669); }
        .level-option.intermediate { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .level-option.advanced { background: linear-gradient(135deg, #a855f7, #7c3aed); }

        .level-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .level-name {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: transparent;
            color: #888;
        }

        .btn-cancel:hover {
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #ffa07a);
            color: white;
        }

        .btn-primary:hover {
            transform: scale(1.05);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .help-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1a1f2e;
            color: #888;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
    </style>

</head>
<body>
    @yield('content')
</body>
</html>