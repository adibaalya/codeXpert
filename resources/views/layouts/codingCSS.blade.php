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

        .test-header-left {
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .code-test-container {
            width: 96%;
            max-width: 100%;
            margin: 20px 0 40px 0;
            padding: 0 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .code-test-left-panel {
            flex: 0 0 42%;
            min-width: 0;
            max-width: 42%;
        }

        .code-test-right-panel {
            flex: 0 0 58%;
            min-width: 0;
            max-width: 58%;
        }

        /* Problem Card */
        .problem-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        height: calc(100vh - 60px);
        min-height: 700px;
        position: relative;
        overflow-y: auto;
        }

        .problem-card::-webkit-scrollbar {
            width: 8px;
        }

        .problem-card::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .problem-card::-webkit-scrollbar-thumb {
            background: #FFB366;
            border-radius: 10px;
        }

        .problem-card::-webkit-scrollbar-thumb:hover {
            background: #FF6B6B;
        }

        .problem-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 107, 107, 0.1);
            color: #FF6B6B;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .problem-title {
            font-size: 28px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 25px;
            line-height: 1.3;
        }

        .problem-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .problem-description {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin: 0;
        }

        .constraints-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .constraints-list li {
            padding: 8px 0 8px 0;
            color: #555;
            font-size: 14px;
            line-height: 1.8;
            position: relative;
            display: block;
        }

        /* Style for the main constraint label (e.g., "Input parameters:", "Output:") */
        .constraints-list li strong {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 4px;
        }

        /* Style for sub-items within constraints */
        .constraints-list li ul {
            list-style: none;
            padding: 0;
            margin: 8px 0 8px 0;
        }

        .constraints-list li ul li {
            padding: 4px 0 4px 20px;
            position: relative;
        }

        .constraints-list li ul li::before {
            content: "—";
            color: #FF6B6B;
            font-weight: bold;
            position: absolute;
            left: 0;
            top: 4px;
        }

        .hints-box {
            background: #FFF9F5;
            border: 2px solid #FFE5D0;
            padding: 18px;
            border-radius: 12px;
            color: #FF6B35;
            font-size: 14px;
            line-height: 1.6;
        }

        .hints-box svg {
            flex-shrink: 0;
            margin-top: 2px;
        }

        .hints-box-content {
            flex: 1;
        }

        /* Format hints as numbered list */
        .hints-box-content ol {
            margin: 0;
            padding-left: 20px;
            list-style-type: decimal;
        }

        .hints-box-content ol li {
            padding: 4px 0;
            line-height: 1.6;
        }

        .hints-box-content p {
            margin: 0;
        }

        .hints-toggle-btn {
            width: 100%;
            background: #FFF9F5;
            border: 2px solid #FFE5D0;
            padding: 14px 18px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #FF6B35;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }

        .hints-toggle-btn:hover {
            background: #FFE5D0;
            border-color: #FFD4B8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
        }

        .hints-toggle-btn .hints-icon {
            flex-shrink: 0;
        }

        .hints-toggle-btn .chevron-icon {
            flex-shrink: 0;
            transition: transform 0.3s ease;
            margin-left: auto;
        }

        /* Tab Navigation Styles */
        .problem-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0;
        }

        .problem-tab {
            background: transparent;
            border: none;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            font-family: 'Inter', sans-serif;
        }

        .problem-tab:hover {
            color: #FF6B6B;
            background: rgba(255, 107, 107, 0.05);
        }

        .problem-tab.active {
            color: #FF6B6B;
            border-bottom-color: #FF6B6B;
        }

        .problem-tab svg {
            flex-shrink: 0;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .tab-content.active {
            display: block;
        }

        /* Test Case Styles */
        .test-case-item {
            background: #F8F9FA;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .test-case-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-case-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .test-case-badge {
            background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .test-case-section {
            margin-bottom: 15px;
        }

        .test-case-section:last-child {
            margin-bottom: 0;
        }

        .test-case-label {
            font-size: 13px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }

        .test-case-code {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 12px 15px;
            border-radius: 8px;
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.5;
            overflow-x: auto;
            margin: 0;
            border: 1px solid #2d2d2d;
        }

        .test-case-code::-webkit-scrollbar {
            height: 6px;
        }

        .test-case-code::-webkit-scrollbar-track {
            background: #252526;
            border-radius: 10px;
        }

        .test-case-code::-webkit-scrollbar-thumb {
            background: #FF6B6B;
            border-radius: 10px;
        }

        .test-case-code::-webkit-scrollbar-thumb:hover {
            background: #FFB366;
        }

        /* Editor Card */
        .editor-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
            min-height: 700px;
        }

        .editor-header {
            background: #2D2D2D;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #1e1e1e;
        }

        .editor-tabs {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .editor-tab {
            background: #1e1e1e;
            padding: 8px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e0e0e0;
            font-size: 13px;
            font-weight: 500;
        }

        .editor-tab.active {
            background: #252526;
        }

        .tab-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .tab-dot.red {
            background: #FF5F56;
        }

        .tab-dot.yellow {
            background: #FFBD2E;
        }

        .tab-dot.green {
            background: #27C93F;
        }

        .tab-filename {
            font-family: 'Courier New', monospace;
        }

        .language-badge {
            justify-content: center;
            background: linear-gradient(135deg, #FF6B6B 0%, #FFB366 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .editor-wrapper {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .monaco-editor-container {
            width: 100%;
            height: 100%;
            min-height: 400px;
        }

        .editor-footer {
            background: #F8F9FA;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
        }

        .validation-message {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            font-size: 13px;
        }

        .validation-message svg {
            flex-shrink: 0;
            color: #4CAF50;
        }

        /* Editor Actions Container */
        .editor-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Run Code Button */
        .run-code-btn {
            background: linear-gradient(135deg, #4C6EF5 0%, #6B8AFF 100%);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 110, 245, 0.3);
            font-family: 'Inter', sans-serif;
        }

        .run-code-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 110, 245, 0.4);
            background: linear-gradient(135deg, #3B5BDB 0%, #5A7AFF 100%);
        }

        .run-code-btn:active {
            transform: translateY(0);
        }

        .run-code-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 15px rgba(76, 110, 245, 0.2);
        }

        .run-code-btn svg {
            flex-shrink: 0;
        }

        /* Submit/Next Button */
        .submit-test-btn {
            background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            font-family: 'Inter', sans-serif;
        }

        .submit-test-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #10B981 100%);
        }

        .submit-test-btn:active {
            transform: translateY(0);
        }

        .submit-test-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .submit-test-btn svg {
            flex-shrink: 0;
        }

        /* Output Section */
        .output-section {
            background: #1e1e1e;
            border-top: 1px solid #2d2d2d;
            max-height: 400px;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            animation: slideDown 0.3s ease-out;
            overflow: hidden;
        }

        .dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4C6EF5;
            animation: dotPulse 1.4s infinite ease-in-out;
        }

        .output-header {
            background: #252526;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #2d2d2d;
            flex-shrink: 0;
        }

        .output-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e0e0e0;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .output-title svg {
            color: #4C6EF5;
            flex-shrink: 0;
        }

        .close-output-btn {
            background: transparent;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .close-output-btn:hover {
            background: #333;
            color: #fff;
        }

        .output-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            padding: 20px;
            margin: 0;
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #d4d4d4;
            background: #1e1e1e;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 100%;
        }

        .output-content::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .output-content::-webkit-scrollbar-track {
            background: #252526;
            border-radius: 5px;
        }

        .output-content::-webkit-scrollbar-thumb {
            background: #4C6EF5;
            border-radius: 5px;
        }

        .output-content::-webkit-scrollbar-thumb:hover {
            background: #5A7AFF;
        }

        .output-content::-webkit-scrollbar-corner {
            background: #252526;
        }

        .editor-footer {
            background: #F8F9FA;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
        }

        .code-test-body {
            background: #FFF9F9;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        /* ========== REVIEWER CODE-TEST ========== */

        .code-test-body-reviewer {
            background: rgb(248, 244, 253);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .test-header-wrapper {
            width: 100%;
            margin: 30px 0 30px 0;
            padding: 0 50px;
        }

        .test-header-left .test-title {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(135deg,rgb(92, 33, 195) 0%,rgb(118, 47, 183) 50%,rgb(120, 33, 201) 100%);
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-bottom: 8px;
        }

        .test-header .test-subtitle {
            font-size: 16px;
            color: #6B7280;
            text-align: left;
        }

        .timer-card{
            background: white; 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 10px 20px; 
            display: flex; 
            flex-direction: column; 
            align-items: flex-end; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            min-width: 140px;
        }

        .timer-label{
            color: #6b7280; 
            font-size: 12px; 
            font-weight: 500; 
            margin-bottom: 2px;
        }

        .code-test-container-reviewer {
            width: 96%;
            max-width: 100%;
            padding: 0 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading Modal Animations */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        @keyframes dotPulse {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* Loading Animation */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive Design for Code Test */
        @media (max-width: 1400px) {
            .code-test-container {
                padding: 0 30px 40px 30px;
            }
            
            .code-test-left-panel {
                flex: 0 0 400px;
                max-width: 400px;
            }
        }

        @media (max-width: 1200px) {
            .code-test-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .code-test-left-panel {
                flex: 1;
                max-width: 100%;
            }
            
            .problem-card {
                position: static;
                max-height: none;
            }
            
            .editor-card {
                height: 700px;
            }
        }

        @media (max-width: 768px) {
            .code-test-container {
                padding: 0 20px 30px 20px;
            }
            
            .test-header-wrapper {
                padding: 0 20px;
                margin: 80px 0 20px 0;
            }
            
            .problem-card,
            .editor-card {
                padding: 20px;
            }
            
            .problem-title {
                font-size: 22px;
            }
            
            .editor-footer {
                flex-direction: column;
                gap: 15px;
            }
            
            .submit-test-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
    </style>

</head>
<body>
    @yield('content')
</body>
</html>