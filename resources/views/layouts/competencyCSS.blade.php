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

        body{
            font-family: 'Inter', sans-serif;
            background:rgb(248, 244, 253);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .title {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(135deg,rgb(92, 33, 195) 0%,rgb(118, 47, 183) 50%,rgb(120, 33, 201) 100%);
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

        .language-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            font-size: 28px;
            margin-bottom: 20px;
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

        .submit-btn{
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
            transition: all 0.3s;"
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* MCQ Test Styles */

        /* Modern MCQ Test Layout */
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

        /* Sidebar */
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

        .question-counter {
            font-size: 13px;
            color: #666;
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

        /* Main Content */
        .main-content {
            flex: 1;
            min-width: 0;
        }
        .test-header-left {
            margin-top: 30px;
            margin-bottom: 10px;
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

        .progress-title{
            font-size: 14px; 
            font-weight: 600; 
            color: #1a1a1a; 
            margin-bottom: 8px;
        }

        .question-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .question-card .question-text {
            font-size: 22px;
            color: #1a1a1a;
            line-height: 1.6;
            margin-bottom: 35px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Code Block Wrapper */
        .code-block-wrapper {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .code-block-header {
            display: none;
        }

        .code-language {
            display: none;
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
        .code-block::-webkit-scrollbar {
            height: 5px;
        }

        .code-block::-webkit-scrollbar-track {
            background: #252526;
            border-radius: 10px;
        }

        .code-block::-webkit-scrollbar-thumb {
            background: rgb(92, 33, 195);
            border-radius: 10px;
        }

        .code-block::-webkit-scrollbar-thumb:hover {
            background: rgb(92, 33, 195);
        }

        /* Inline code styling */
        .inline-code {
            background:rgb(240, 224, 255);
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

        /* Inline code styling - only for inline code, not code blocks */
        .question-text code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
            padding: 4px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Inline code styling - only for inline code, not code blocks */
        .question-text code {
            background: rgb(240, 224, 255);
            color: rgb(92, 33, 195);
            padding: 4px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        /* Code block styling for multi-line code in questions */
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

        /* Override inline code styling when inside pre blocks */
        .question-text pre code {
            background: transparent !important;
            color: #ffffff !important;
            padding: 0;
            border-radius: 0;
            font-size: 10px;
            display: block;
        }

        /* Syntax highlighting colors for code blocks */
        .question-text pre .keyword {
            color: #569cd6;
            font-weight: bold;
        }

        .question-text pre .string {
            color: #ce9178;
        }

        .question-text pre .number {
            color: #b5cea8;
        }

        .question-text pre .comment {
            color: #6a9955;
            font-style: italic;
        }

        .question-text pre .function {
            color: #dcdcaa;
        }

        /* Code scrollbar styling */
        .question-text pre::-webkit-scrollbar {
            height: 8px;
        }

        .question-text pre::-webkit-scrollbar-track {
            background: #2d2d2d;
            border-radius: 10px;
        }

        .question-text pre::-webkit-scrollbar-thumb {
            background: #FFB366;
            border-radius: 10px;
        }

        .question-text pre::-webkit-scrollbar-thumb:hover {
            background: #FF6B6B;
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

        .question-card .question-text code {
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
            background:rgb(250, 245, 255);
            transform: translateX(5px);
        }

        .option-item.selected {
            border-color: rgb(92, 33, 195);;
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
            box-shadow: 0 10px 30px rgba(164, 53, 255,  0.2);
        }

        /* Code Test Styles */
        .code-test-container-reviewer {
            width: 96%;
            max-width: 100%;
            padding: 0 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
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

        .example-box {
            background: #F8F9FA;
            border-left: 4px solid #FF6B6B;
            padding: 20px;
            border-radius: 12px;
            margin-top: 10px;
        }

        .example-label {
            font-size: 13px;
            font-weight: 700;
            color: #666;
            margin-bottom: 8px;
            margin-top: 15px;
        }

        .example-label:first-child {
            margin-top: 0;
        }

        .example-content {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .example-explanation {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            padding: 8px 0;
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

        .dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4C6EF5;
            animation: dotPulse 1.4s infinite ease-in-out;
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

        /* Loading Animation */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .editor-footer {
            background: #F8F9FA;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
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

        @media (max-width: 768px) {
            .language-grid {
                grid-template-columns: 1fr;
            }
            
            .title {
                font-size: 32px;
            }
        }

        .code-test-body-reviewer {
            background: rgb(248, 244, 253);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .code-test-body {
            background: #FFF9F9;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        /* Result Page Styles */
        .result-body {
            background: #FFF9F9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px 20px;
            font-family: 'Inter', sans-serif;
        }

        /* Result Page Styles */
        .result-body-reviewer {
            background: rgb(248, 244, 253);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px 20px;
            font-family: 'Inter', sans-serif;
        }

        .result-container {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
        }

        .result-card {
            background: white;
            border-radius: 24px;
            padding: 50px 60px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
        }

        .result-header-title {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .result-header-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 40px;
        }

        .success-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px auto;
            position: relative;
        }

        .success-icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            border: 4px solid #10B981;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon-circle.failed {
            border-color: #EF4444;
        }

        .success-icon-circle svg {
            width: 50px;
            height: 50px;
            color: #10B981;
        }

        .success-icon-circle.failed svg {
            color: #EF4444;
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

        .result-message-title {
            font-size: 28px;
            font-weight: 800;
            color: #10B981;
            margin-bottom: 15px;
        }

        .result-message-title.failed {
            color: #EF4444;
        }

        .result-congrats-box {
            background: #D1FAE5;
            border-radius: 16px;
            padding: 20px 25px;
            margin: 25px 0 30px 0;
        }

        .result-congrats-box.failed {
            background: #FEE2E2;
        }

        .result-congrats-text {
            font-size: 15px;
            color: #059669;
            line-height: 1.6;
            margin: 0;
            font-weight: 500;
        }

        .result-congrats-text.failed {
            color: #DC2626;
        }

        .result-scores-section {
            margin: 35px 0;
        }

        .result-score-item {
            background: #F9FAFB;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 15px;
            text-align: left;
        }

        .result-score-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .result-score-label {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
        }

        .result-score-value {
            font-size: 28px;
            font-weight: 800;
            color: #10B981;
        }

        .result-score-sublabel {
            font-size: 13px;
            color: #6B7280;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .result-progress-bar {
            height: 10px;
            background: #E5E7EB;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 12px;
        }

        .result-progress-fill {
            height: 100%;
            background: #10B981;
            border-radius: 10px;
            transition: width 0.8s ease-out;
        }

        .result-progress-fill.medium {
            background: #F59E0B;
        }

        .result-progress-fill.low {
            background: #EF4444;
        }

        .result-actions {
            display: flex;
            gap: 12px;
            margin-top: 35px;
        }

        .result-btn {
            flex: 1;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .result-btn-primary {
            background-image: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .result-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .result-btn-reviewer-primary {
            background-image: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .result-btn-reviewer-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(164, 53, 255, 0.4);
        }

        .result-btn-secondary {
            background: white;
            color: #374151;
            border: 2px solid #E5E7EB;
        }

        .result-btn-secondary:hover {
            background: #F9FAFB;
            border-color: #D1D5DB;
            transform: translateY(-2px);
        }

        .result-tooltip {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #1F2937;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideInRight 0.5s ease-out;
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

        .result-tooltip svg {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .result-card {
                padding: 40px 30px;
            }

            .result-header-title {
                font-size: 26px;
            }

            .result-actions {
                flex-direction: column;
            }

            .result-tooltip {
                position: static;
                margin-bottom: 20px;
                justify-content: center;
            }
        }
        
        /* Collapsible Feedback Styles */
        .feedback-toggle-btn {
            width: 100%;
            background: white;
            border: 2px solid #E5E7EB;
            padding: 16px 20px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .feedback-toggle-btn:hover {
            border-color: #D1D5DB;
            background: #F9FAFB;
            transform: translateX(3px);
        }

        .feedback-content {
            display: none;
            background: white;
            border: 2px solid #E5E7EB;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 12px 12px;
            margin-top: -8px;
            margin-bottom: 16px;
            animation: slideDown 0.3s ease-out;
        }

        .chevron-icon {
            transition: transform 0.3s ease;
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