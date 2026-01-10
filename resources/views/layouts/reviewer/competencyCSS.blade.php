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
        /* ============================================
           GLOBAL STYLES
           ============================================ */
        body {
            font-family: 'Inter', sans-serif;
            background: rgb(248, 244, 253);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        /* ============================================
           CHOOSE LANGUAGE PAGE - HEADER STYLES
           ============================================ */
        .title {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(135deg, rgb(92, 33, 195) 0%, rgb(118, 47, 183) 50%, rgb(120, 33, 201) 100%);
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

        /* ============================================
           CHOOSE LANGUAGE PAGE - LANGUAGE GRID
           ============================================ */
        .language-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 20px;
            justify-items: center;
        }

        /* Center the 7th card (SQL) when it's alone in the last row */
        .language-card:nth-child(7):last-child {
            grid-column: 2 / 3;
        }

        /* If there are 8 cards, center both in the last row */
        .language-card:nth-child(7):nth-last-child(2) {
            grid-column: 2 / 3;
        }

        .language-card:nth-child(8):last-child {
            grid-column: 3 / 4;
        }

        .language-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #E5E7EB;
            padding: 40px 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 350px;
        }

        .language-card.insufficient {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .language-card.insufficient:hover {
            transform: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .language-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.2);
        }

        .language-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .language-card:hover::before {
            opacity: 1;
        }

        .language-icon-box {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px auto;
            transition: transform 0.3s ease;
        }

        .language-card:hover .language-icon-box {
            transform: scale(1.1);
        }

        .language-name {
            font-size: 24px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 10px;
        }

        .language-description {
            font-size: 14px;
            color: #000000;
            line-height: 1.5;
        }

        .insufficient-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FEF2F2;
            color: #DC2626;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 15px;
            border: 1px solid #FECACA;
        }

        .question-counts {
            margin-top: 5px;
            text-align: center;
        }

        /* ============================================
           CHOOSE LANGUAGE PAGE - INFO BOX
           ============================================ */
        .info-box {
            background: white;
            border-left: 4px solid rgb(92, 33, 195);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .info-title {
            font-size: 16px;
            font-weight: 700;
            color: black;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-text {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.6;
        }

        .info-list {
            list-style: none;
            margin-top: 15px;
        }

        .info-list li {
            padding: 8px 0;
            color: #6B7280;
            font-size: 14px;
        }

        .info-list li::before {
            content: "✓";
            color: #48bb78;
            font-weight: bold;
            margin-right: 10px;
        }

        /* ============================================
           CHOOSE LANGUAGE PAGE - SUBMIT BUTTON
           ============================================ */
        .submit-btn {
            width: 100%; 
            margin-top: 10px;
            margin-bottom: 30px;
            padding: 18px; 
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%); 
            border: none; 
            border-radius: 12px; 
            color: white; 
            font-size: 18px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: all 0.3s;
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ============================================
           MCQ TEST PAGE - BODY & LAYOUT
           ============================================ */
        .mcq-test-body {
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

        .test-container {
            width: 100%;
            margin: 0 0 40px 0;
            padding: 0 50px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        /* ============================================
           MCQ TEST PAGE - HEADER
           ============================================ */
        .test-header-left {
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .test-header-left .test-title {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(135deg, rgb(92, 33, 195) 0%, rgb(118, 47, 183) 50%, rgb(120, 33, 201) 100%);
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-bottom: 8px;
        }

        .test-subtitle {
            font-size: 16px;
            color: #6B7280;
            text-align: left;
        }

        /* ============================================
           MCQ TEST PAGE - TIMER
           ============================================ */
        .timer-card {
            background: white; 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 10px 20px; 
            display: flex; 
            flex-direction: column; 
            align-items: flex-end; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            min-width: 140px;
        }

        .timer-label {
            color: #6b7280; 
            font-size: 12px; 
            font-weight: 500; 
            margin-bottom: 2px;
        }

        /* ============================================
           MCQ TEST PAGE - SIDEBAR
           ============================================ */
        .sidebar {
            width: 320px;
            flex-shrink: 0;
        }

        .sidebar-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .progress-title {
            font-size: 14px; 
            font-weight: 600; 
            color: #1a1a1a; 
            margin-bottom: 8px;
        }

        .questions-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-top: 20px;
        }

        .question-number {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .question-number.current {
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
        }

        .question-number.answered {
            background: #4CAF50;
            color: white;
        }

        .question-number.unanswered {
            background: #f0f0f0;
            color: #999;
        }

        /* ============================================
           MCQ TEST PAGE - SIDEBAR LEGEND
           ============================================ */
        .legend {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 12px;
            color: #666;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-dot.current {
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        }

        .legend-dot.answered {
            background: #4CAF50;
        }

        .legend-dot.unanswered {
            background: #f0f0f0;
        }

        /* ============================================
           MCQ TEST PAGE - MAIN CONTENT
           ============================================ */
        .main-content {
            flex: 1;
            min-width: 0;
        }

        .question-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .question-badge {
            background: rgba(255, 107, 107, 0.1);
            color: #FF6B6B;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        /* ============================================
           MCQ TEST PAGE - QUESTION TEXT & CODE BLOCKS
           ============================================ */
        .question-text {
            font-size: 22px;
            color: #1a1a1a;
            line-height: 1.6;
            margin-bottom: 35px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Inline code styling */
        .question-text code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
            padding: 4px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 20px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .inline-code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
            padding: 2px 5px;
            border-radius: 3px;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 16px !important;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Code block styling for multi-line code */
        .question-text pre {
            background: #000000 !important;
            color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            margin: 20px 0;
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.5;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            white-space: pre;
            tab-size: 4;
        }

        .question-text pre code {
            background: transparent !important;
            color: #ffffff !important;
            padding: 0;
            border-radius: 0;
            font-size: 10px;
            display: block;
        }

        .code-block {
            background: #1e1e1e !important;
            color: #d4d4d4 !important;
            padding: 8px 12px;
            margin: 0;
            overflow-x: auto;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace !important;
            font-size: 10px !important;
            line-height: 1.3 !important;
            white-space: pre;
            tab-size: 2;
            -moz-tab-size: 2;
            font-weight: 400 !important;
            border-radius: 6px;
        }

        .code-block code {
            background: transparent !important;
            color: #d4d4d4 !important;
            padding: 0 !important;
            border-radius: 0;
            font-size: 12px !important;
            display: block;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace !important;
            font-weight: 400 !important;
            line-height: 1.3 !important;
        }

        /* Code scrollbar styling */
        .code-block::-webkit-scrollbar,
        .question-text pre::-webkit-scrollbar {
            height: 8px;
        }

        .code-block::-webkit-scrollbar-track,
        .question-text pre::-webkit-scrollbar-track {
            background: #252526;
            border-radius: 10px;
        }

        .code-block::-webkit-scrollbar-thumb,
        .question-text pre::-webkit-scrollbar-thumb {
            background: rgb(92, 33, 195);
            border-radius: 10px;
        }

        .code-block::-webkit-scrollbar-thumb:hover,
        .question-text pre::-webkit-scrollbar-thumb:hover {
            background: rgb(92, 33, 195);
        }

        /* ============================================
           MCQ TEST PAGE - OPTIONS
           ============================================ */
        .options-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .option-item {
            background: #ffffff;
            border: 2px solid #e8e8e8;
            border-radius: 15px;
            padding: 20px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .option-item:hover {
            border-color: rgb(213, 158, 231);
            background: rgb(250, 245, 255);
            transform: translateX(5px);
        }

        .option-item.selected {
            border-color: rgb(92, 33, 195);
            background: linear-gradient(135deg, rgba(181, 107, 255, 0.1) 0%, rgba(133, 102, 255, 0.1) 100%);
        }

        .option-item .option-letter {
            width: 45px;
            height: 45px;
            background: #f5f5f5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #666;
            font-size: 18px;
            flex-shrink: 0;
        }

        .option-item.selected .option-letter {
            background: linear-gradient(135deg, #FF6B6B 0%, #FFB366 100%);
            color: white;
        }

        .option-item .option-text {
            flex: 1;
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }

        /* Code in options */
        .option-text code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 14px;
        }

        .option-item.selected .option-text code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
        }

        .option-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #ddd;
            border-radius: 50%;
            flex-shrink: 0;
            position: relative;
        }

        .option-item.selected .option-radio {
            border-color: #FF6B6B;
        }

        .option-item.selected .option-radio::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background: #FF6B6B;
            border-radius: 50%;
        }

        /* ============================================
           MCQ TEST PAGE - NAVIGATION BUTTONS
           ============================================ */
        .navigation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 16px 35px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-next {
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
            flex: 1;
            justify-content: center;
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(164, 53, 255, 0.4);
        }

        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-previous {
            background: white;
            color: rgb(92, 33, 195);
            border: 2px solid rgb(92, 33, 195);
            justify-content: center;
        }

        .btn-previous:hover {
            background: #FFF9F5;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(164, 53, 255, 0.2);
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        @media (max-width: 768px) {
            .language-grid {
                grid-template-columns: 1fr;
            }
            
            .title {
                font-size: 32px;
            }

            .test-header-wrapper {
                padding: 0 20px;
            }

            .test-container {
                flex-direction: column;
                padding: 0 20px;
            }

            .sidebar {
                width: 100%;
            }

            .question-card {
                padding: 25px;
            }
        }
    </style>

</head>
<body>
    @yield('content')
</body>
</html>
```
</copilot-edited-file>