<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #FEF8F5;
            color: #1a1a1a;
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
            font-size: 36px;
            font-weight: 800;
            background-image: linear-gradient(to right,rgb(250, 90, 75) 30%,rgb(251, 192, 90));
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
            border-color: #FF6B35;
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
            background: linear-gradient(to right, #FF4432, #FFB83D);
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
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
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
            color: #FF6B35;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #FF6B35;
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
            border-color: #FF6B35;
            color: #FF6B35;
        }

        .btn-primary {
            background: linear-gradient(to right, #FF4432, #FFB83D);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #E5E7EB;
            border-top-color: #FF6B35;
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
    </style>