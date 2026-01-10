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

        /* ========== LEARNER RESULT ========== */

        .result-body {
            background: #FFF9F9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px 20px;
            font-family: 'Inter', sans-serif;
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

        .result-btn-secondary {
            background: white;
            color: #374151;
            border: 2px solid #D1D5DB;
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

        .result-tooltip svg {
            width: 16px;
            height: 16px;
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

        /* ========== REVIEWER RESULT ========== */

        .result-body-reviewer {
            background: rgb(248, 244, 253);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px 20px;
            font-family: 'Inter', sans-serif;
        }

        .result-btn-reviewer {
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

        .result-btn-reviewer-primary {
            background-image: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(164, 53, 255, 0.3);
        }

        .result-btn-reviewer-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(164, 53, 255, 0.4);
        }

        /* ========== CODE-FEEDBACK ========== */

        .code-test-body-reviewer {
            background: rgb(248, 244, 253);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .feedback-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .feedback-card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .feedback-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .feedback-icon.success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
        }

        .feedback-icon.partial {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: white;
        }

        .feedback-icon.failed {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
        }

        .feedback-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .feedback-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .score-summary {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
        }

        .score-item {
            text-align: center;
        }

        .score-value {
            font-size: 36px;
            font-weight: 700;
            color: #4C6EF5;
        }

        .score-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .test-results {
            margin-top: 30px;
        }

        .test-results-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-case-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #e5e7eb;
        }

        .test-case-card.passed {
            border-left-color: #10B981;
            background: #f0fdf4;
        }

        .test-case-card.failed {
            border-left-color: #EF4444;
            background: #fef2f2;
        }

        .test-case-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .test-case-number {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
        }

        .test-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .test-badge.passed {
            background: #10B981;
            color: white;
        }

        .test-badge.failed {
            background: #EF4444;
            color: white;
        }

        .test-badge.sample {
            background: #4C6EF5;
            color: white;
            margin-left: 10px;
        }

        .test-case-detail {
            margin-top: 10px;
        }

        .test-detail-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .test-detail-label {
            font-weight: 600;
            color: #666;
        }

        .test-detail-value {
            color: #1a1a1a;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            word-break: break-all;
        }

        .hidden-text {
            color: #999;
            font-style: italic;
        }

        .continue-btn {
            background: linear-gradient(135deg, #4C6EF5 0%, #4338CA 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 40px auto 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .continue-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(76, 110, 245, 0.3);
        }

        .info-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 16px;
            margin-top: 30px;
            display: flex;
            gap: 12px;
            font-size: 14px;
            color: #1E40AF;
        }

        .info-box svg {
            flex-shrink: 0;
            margin-top: 2px;
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

        @media (max-width: 768px) {
            .language-grid {
                grid-template-columns: 1fr;
            }
            
            .title {
                font-size: 32px;
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