<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:rgb(248, 244, 253);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px 40px;
        }

        .page-header {
            margin-bottom: 12px;
        }

        .page-title {
            font-size: 42px;
            font-weight: 800;
            background-image: linear-gradient(135deg,rgb(92, 33, 195) 0%,rgb(118, 47, 183) 50%,rgb(120, 33, 201) 100%);
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 16px;
            color: #6B7280;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 32px;
            margin-top: 32px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            display: block;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Figtree', sans-serif;
            transition: all 0.2s;
            background: white;
            color: #1a1a1a;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: rgb(92, 33, 195);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-hint {
            font-size: 13px;
            color: #9CA3AF;
            margin-top: 6px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn-generate {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-generate:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(164, 53, 255, 0.3);
        }

        .btn-generate:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .result-card {
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            color: #9CA3AF;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 16px;
            color: #9CA3AF;
        }

        .generated-content {
            display: none;
        }

        .generated-content.active {
            display: block;
        }

        .question-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-intermediate {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-algorithms {
            background: #E0E7FF;
            color: #3730A3;
        }

        .badge-python {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .tabs {
            display: flex;
            gap: 2px;
            border-bottom: 2px solid #E5E7EB;
            margin-bottom: 24px;
        }

        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            font-size: 14px;
            font-weight: 600;
            color: #6B7280;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .tab.active {
            color: rgb(92, 33, 195);
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgb(92, 33, 195);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .problem-text {
            font-size: 14px;
            color: #4B5563;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .constraints-list {
            list-style: none;
            padding-left: 0;
        }

        .constraints-list li {
            font-size: 14px;
            color: #4B5563;
            padding: 6px 0;
            padding-left: 20px;
            position: relative;
        }

        .constraints-list li::before {
            content: '-';
            position: absolute;
            left: 0;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid #E5E7EB;
            background: white;
            color: #4B5563;
        }

        .btn:hover {
            border-color: rgb(92, 33, 195);
            color: rgb(92, 33, 195);
        }

        .btn-primary {
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(164, 53, 255, 0.3);
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #E5E7EB;
            border-top-color: rgb(92, 33, 195);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 16px;
            color: #6B7280;
        }

        @media (max-width: 1024px) {
            .container {
                padding: 24px 20px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        /* ========== SUCCESS MODAL STYLES ========== */
        .success-modal-container {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: successModalSlideIn 0.4s ease-out;
        }

        @keyframes successModalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .success-icon-circle {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            animation: successIconPulse 0.6s ease-out;
        }

        @keyframes successIconPulse {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-checkmark {
            animation: checkmarkDraw 0.6s ease-out 0.2s both;
        }

        @keyframes checkmarkDraw {
            from {
                stroke-dasharray: 100;
                stroke-dashoffset: 100;
            }
            to {
                stroke-dasharray: 100;
                stroke-dashoffset: 0;
            }
        }

        .success-title {
            font-size: 32px;
            font-weight: 800;
            color: #1F2937;
            margin: 0 0 16px 0;
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message {
            font-size: 16px;
            color: #6B7280;
            line-height: 1.6;
            margin: 0 0 32px 0;
            animation: fadeInUp 0.5s ease-out 0.4s both;
        }

        .success-score {
            font-weight: 700;
            color: #10B981;
            font-size: 18px;
        }

        .btn-continue {
            width: 100%;
            padding: 16px 32px;
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            animation: fadeInUp 0.5s ease-out 0.5s both;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4);
        }

        .btn-continue:active {
            transform: translateY(0);
        }
    </style>