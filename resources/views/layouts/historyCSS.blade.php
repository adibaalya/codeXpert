<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CodeXpert - History</title>

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
            background: rgb(248, 244, 253);
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 32px;
        }

        .page-header {
            margin-bottom: 40px;
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

        /* Filters */
        .filters-section {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filters-title {
            font-size: 18px;
            font-weight: 700;
            color: #1F2937;
        }

        .showing-count {
            font-size: 14px;
            color: #6B7280;
        }

        .filters-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
            display: block;
        }

        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #1F2937;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-select:focus {
            outline: none;
            border-color: rgb(92, 33, 195);
        }

        .search-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #E5E7EB;
            border-radius: 10px;
            font-size: 14px;
            color: #1F2937;
            background: white;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: rgb(92, 33, 195);
            box-shadow: 0 0 0 3px rgba(164, 53, 255, 0.1);
        }

        /* Questions List */
        .questions-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .question-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 2px solid transparent;
            transition: all 0.2s;
            cursor: pointer;
        }

        .question-card:hover {
            border-color: rgb(92, 33, 195);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(164, 53, 255, 0.15);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .question-left {
            flex: 1;
        }

        .question-badges {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge.intermediate {
            background: #DBEAFE;
            color: #3B82F6;
        }

        .badge.beginner {
            background: #D1FAE5;
            color: #10B981;
        }

        .badge.advanced {
            background: #FEE2E2;
            color: #DC2626;
        }

        .badge.algorithms {
            background: #E9D5FF;
            color: #A855F7;
        }

        .badge.data-structures {
            background: #FEF3C7;
            color: #F59E0B;
        }

        .badge.basics {
            background: #DBEAFE;
            color: #3B82F6;
        }

        .badge.trees {
            background: #D1FAE5;
            color: #10B981;
        }

        .badge.graphs {
            background: #E9D5FF;
            color: #A855F7;
        }

        .badge.dynamic-programming {
            background: #FEE2E2;
            color: #DC2626;
        }

        .question-title {
            font-size: 18px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .question-description {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .question-meta {
            display: flex;
            gap: 16px;
            align-items: center;
            font-size: 13px;
            color: #6B7280;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .question-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-badge.approved {
            background: #D1FAE5;
            color: #10B981;
        }

        .status-badge.rejected {
            background: #FEE2E2;
            color: #DC2626;
        }

        .status-badge.pending {
            background: #FEF3C7;
            color: #F59E0B;
        }

        .approved-info {
            text-align: right;
            font-size: 12px;
            color: #6B7280;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.2s ease-in-out;
        }

        .modal-overlay.active {
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.3s ease-out;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 32px 32px 24px 32px;
            border-bottom: 1px solid #E5E7EB;
            position: sticky;
            top: 0;
            background: white;
            border-radius: 20px 20px 0 0;
            z-index: 10;
        }

        .modal-title-main {
            font-size: 24px;
            font-weight: 700;
            color: #1F2937;
            margin: 0;
            padding-right: 40px;
        }

        .modal-title-section {
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .modal-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .close-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #F3F4F6;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #6B7280;
            transition: all 0.2s;
        }

        .close-btn:hover {
            background: #E5E7EB;
            color: #1F2937;
        }

        .modal-body {
            padding: 32px;
            max-height: calc(90vh - 100px);
            overflow-y: auto;
        }

        .problem-content-box {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 24px;
        }

        .content-section-modal {
            margin-bottom: 24px;
        }

        .content-section-modal:last-child {
            margin-bottom: 0;
        }

        .section-heading {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 12px;
        }

        .section-text {
            font-size: 14px;
            color: #4B5563;
            line-height: 1.8;
        }

        .bullet-item {
            margin-left: 0;
            margin-bottom: 6px;
            line-height: 1.8;
        }

        .hint-box-modal {
            background: #FEF3C7;
            border: 2px solid #FCD34D;
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
            font-size: 14px;
            color: #92400E;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .hint-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description-text {
            font-size: 14px;
            color: #4B5563;
            line-height: 1.8;
            margin-bottom: 24px;
            padding: 16px;
            background: #F9FAFB;
            border-radius: 12px;
            border-left: 4px solid #FF6B35;
        }

        .modal-body {
            padding: 32px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description-text {
            font-size: 14px;
            color: #4B5563;
            line-height: 1.8;
            margin-bottom: 24px;
            padding: 16px;
            background: #F9FAFB;
            border-radius: 12px;
            border-left: 4px solid #FF6B35;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-item {
            padding: 16px;
            background: #F9FAFB;
            border-radius: 12px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 700;
            color: #1F2937;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .empty-description {
            font-size: 14px;
            color: #6B7280;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 16px;
            }

            .filters-grid {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .question-header {
                flex-direction: column;
            }

            .question-right {
                align-items: flex-start;
                width: 100%;
                margin-top: 12px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                max-width: 100%;
                max-height: 100vh;
                border-radius: 0;
            }

            .modal-header {
                border-radius: 0;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 28px;
            }

            .question-title {
                font-size: 16px;
            }

            .modal-title {
                font-size: 20px;
            }

            .modal-header,
            .modal-body {
                padding: 20px;
            }
        }
    </style>

</head>
<body>
    
</body>
</html>
