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

    /* Main Container */
    .main-container {
        display: grid;
        grid-template-columns: 340px 1fr;
        height: calc(100vh - 83px);
        gap: 0;
    }

    /* ========== SIDEBAR ========== */
    .sidebar {
        background: white;
        border-right: 2px solid #E5E7EB;
        overflow-y: auto;
        padding: 24px;
    }

    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .sidebar-title {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .pending-badge {
        background: #FEE2E2;
        color: #DC2626;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Question List */
    .question-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .question-card {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .question-card:hover {
        background: white;
        border-color: #FFB83D;
        transform: translateX(4px);
    }

    .question-card.active {
        background: white;
        border-color: #FF6B35;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
        transform: translateX(4px);
    }

    .question-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .difficulty-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .difficulty-badge.advanced {
        background: #FEE2E2;
        color: #DC2626;
    }

    .difficulty-badge.intermediate {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .difficulty-badge.beginner {
        background: #D1FAE5;
        color: #10B981;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.pending {
        background: #F3F4F6;
        color: #6B7280;
    }

    .question-card-title {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .question-tags {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .tag {
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 600;
    }

    .tag-python {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .tag-javascript {
        background: #FEF3C7;
        color: #D97706;
    }

    .tag-topic {
        background: #E9D5FF;
        color: #A855F7;
    }

    .question-meta {
        font-size: 12px;
        color: #6B7280;
        font-weight: 500;
    }

    /* ========== CONTENT AREA ========== */
    .content-area {
        background: #FEF8F5;
        overflow-y: auto;
        padding: 32px;
    }

    /* Question Header */
    .question-header {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .question-header-left {
        flex: 1;
    }

    .question-title {
        font-size: 28px;
        font-weight: 800;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .question-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .category-badge,
    .language-badge,
    .topic-badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .category-badge {
        background: #F3F4F6;
        color: #1F2937;
    }

    .language-badge {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .topic-badge {
        background: #E9D5FF;
        color: #A855F7;
    }

    .submitted-text {
        font-size: 13px;
        color: #6B7280;
        font-weight: 500;
    }

    /* Question Actions */
    .question-actions {
        display: flex;
        gap: 10px;
    }

    .btn-reject,
    .btn-edit,
    .btn-save,
    .btn-cancel,
    .btn-approve {
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-icon {
        font-size: 16px;
    }

    .btn-reject {
        background: white;
        border-color: #FCA5A5;
        color: #DC2626;
    }

    .btn-reject:hover {
        background: #FEE2E2;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
    }

    .btn-edit {
        background: white;
        border-color: #E5E7EB;
        color: #6B7280;
    }

    .btn-edit:hover {
        background: #F9FAFB;
        border-color: #D1D5DB;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.1);
    }

    .btn-save {
        background: linear-gradient(to right, #10B981, #34D399);
        border-color: transparent;
        color: white;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-cancel {
        background: white;
        border-color: #FCA5A5;
        color: #DC2626;
    }

    .btn-cancel:hover {
        background: #FEE2E2;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
    }

    .btn-approve {
        background: linear-gradient(to right, #FF6B35, #FFB83D);
        border-color: transparent;
        color: white;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
    }

    /* Editable content styling */
    .editable {
        outline: 2px dashed #FFB83D;
        outline-offset: 4px;
        padding: 8px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .editable:focus {
        outline: 2px solid #FF6B35;
        background: #FFFBF5;
    }

    .editable:hover {
        background: #FFFEF9;
    }

    /* ========== TABS ========== */
    .tabs {
        background: white;
        border-radius: 16px;
        padding: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        gap: 8px;
    }

    .tab {
        flex: 1;
        padding: 12px 16px;
        background: transparent;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .tab:hover {
        background: #F9FAFB;
        color: #1F2937;
    }

    .tab.active {
        background: #FEE2E2;
        color: #DC2626;
    }

    .tab-icon {
        font-size: 16px;
    }

    /* ========== CONTENT SECTION ========== */
    .content-section {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
    }

    .description-text {
        font-size: 16px;
        color: #4B5563;
        line-height: 1.6;
        margin-bottom: 32px;
    }

    /* Problem Box */
    .problem-box {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px;
    }

    .problem-text {
        font-size: 15px;
        color: #374151;
        line-height: 1.7;
        margin-bottom: 16px;
    }

    .problem-list {
        margin-left: 20px;
        margin-bottom: 16px;
    }

    .problem-list li {
        font-size: 15px;
        color: #374151;
        line-height: 1.7;
        margin-bottom: 8px;
    }

    /* Hint Box */
    .hint-box {
        background: #FEF3C7;
        border: 2px solid #FCD34D;
        border-radius: 10px;
        padding: 16px;
        margin-top: 16px;
        font-size: 14px;
        color: #92400E;
    }

    /* Options Box */
    .options-box {
        background: #EFF6FF;
        border: 2px solid #DBEAFE;
        border-radius: 10px;
        padding: 16px;
        margin-top: 16px;
    }

    .options-list {
        list-style: none;
        padding-left: 0;
        margin-top: 12px;
    }

    .options-list li {
        padding: 8px 12px;
        margin-bottom: 8px;
        background: white;
        border-radius: 8px;
        border: 1px solid #DBEAFE;
        font-size: 14px;
        color: #1F2937;
    }

    /* Test Cases */
    .test-case-box {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .test-case-header {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E5E7EB;
    }

    .test-case-title {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
    }

    .test-case-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .test-case-item {
        background: white;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #E5E7EB;
    }

    .test-case-item strong {
        display: block;
        margin-bottom: 8px;
        color: #6B7280;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .code-block {
        background: #1F2937;
        color: #10B981;
        padding: 16px;
        border-radius: 8px;
        font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
        margin: 0;
    }

    /* Solution Box */
    .solution-box {
        background: #F9FAFB;
        border: 2px solid #E5E7EB;
        border-radius: 12px;
        padding: 24px;
    }

    .answer-display {
        background: white;
        padding: 16px;
        border-radius: 8px;
        border: 2px solid #10B981;
        font-size: 15px;
        color: #1F2937;
    }

    .answer-display strong {
        color: #10B981;
        margin-right: 8px;
    }

    .solution-code {
        margin: 0;
    }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6B7280;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .empty-state-text {
        font-size: 16px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .empty-state-subtext {
        font-size: 14px;
        color: #6B7280;
    }

    .empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 500px;
        text-align: center;
        padding: 40px;
    }

    .empty-content-icon {
        font-size: 80px;
        margin-bottom: 24px;
        opacity: 0.5;
    }

    .empty-content-title {
        font-size: 28px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 12px;
    }

    .empty-content-text {
        font-size: 16px;
        color: #6B7280;
        margin-bottom: 32px;
        max-width: 500px;
    }

    .btn-primary {
        padding: 14px 28px;
        background: linear-gradient(to right, #FF6B35, #FFB83D);
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
    }

    /* ========== FLOATING HELP ========== */
    .floating-help {
        position: fixed;
        bottom: 32px;
        right: 32px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #FF6B35, #FFB83D);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
        transition: all 0.2s;
        z-index: 999;
    }

    .floating-help:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5);
    }

    /* ========== SCROLLBAR ========== */
    .sidebar::-webkit-scrollbar,
    .content-area::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar::-webkit-scrollbar-track,
    .content-area::-webkit-scrollbar-track {
        background: #F3F4F6;
    }

    .sidebar::-webkit-scrollbar-thumb,
    .content-area::-webkit-scrollbar-thumb {
        background: #D1D5DB;
        border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover,
    .content-area::-webkit-scrollbar-thumb:hover {
        background: #9CA3AF;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1024px) {
        .main-container {
            grid-template-columns: 1fr;
        }

        .sidebar {
            display: none;
        }

        .question-actions {
            flex-direction: column;
        }

        .btn-reject,
        .btn-edit,
        .btn-approve {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .content-area {
            padding: 16px;
        }

        .question-header {
            flex-direction: column;
            padding: 16px;
        }

        .question-title {
            font-size: 22px;
        }

        .tabs {
            flex-direction: column;
        }

        .content-section {
            padding: 20px;
        }
    }

    /* ========== MODAL STYLES ========== */
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

    .modal-container {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        background: linear-gradient(135deg, #FF6B6B 0%, #FFB88C 100%);
        padding: 24px 32px;
        border-radius: 16px 16px 0 0;
    }

    .modal-header-content {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FF6B6B;
    }

    .modal-title {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .modal-subtitle {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.9);
        margin: 4px 0 0 0;
    }

    .modal-body {
        padding: 32px;
    }

    .overall-grade-box {
        background: linear-gradient(135deg, #FFF5F5 0%, #FFF8F0 100%);
        border: 2px solid #FFE0E0;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        margin-bottom: 32px;
    }

    .overall-grade-label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .overall-grade-value {
        font-size: 48px;
        font-weight: 800;
        color: #FF6B6B;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    .pass-indicator {
        font-size: 14px;
        font-weight: 600;
        color: white;
        background: #4CAF50;
        padding: 8px 16px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .criteria-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
        margin-bottom: 24px;
    }

    .criteria-item {
        background: white;
        border: 1px solid #E0E0E0;
        border-radius: 12px;
        padding: 20px;
    }

    .criteria-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .criteria-name {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }

    .criteria-description {
        font-size: 13px;
        color: #666;
    }

    .criteria-value {
        font-size: 24px;
        font-weight: 700;
        color: #FF6B6B;
    }

    .criteria-slider {
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: linear-gradient(to right, #FFE0E0 0%, #FF6B6B 100%);
        outline: none;
        -webkit-appearance: none;
    }

    .criteria-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: white;
        border: 3px solid #FF6B6B;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s;
    }

    .criteria-slider::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
    }

    .criteria-slider::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: white;
        border: 3px solid #FF6B6B;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s;
    }

    .feedback-section {
        margin-top: 24px;
    }

    .feedback-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .feedback-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #E0E0E0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: #333;
        resize: vertical;
        box-sizing: border-box;
    }

    .feedback-textarea:focus {
        outline: none;
        border-color: #FF6B6B;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
    }

    .modal-footer {
        padding: 24px 32px;
        border-top: 1px solid #E0E0E0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-modal-cancel {
        padding: 12px 24px;
        border: 2px solid #E0E0E0;
        background: white;
        color: #666;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover {
        border-color: #999;
        color: #333;
    }

    .btn-modal-submit {
        padding: 12px 32px;
        border: none;
        background: linear-gradient(135deg, #FF6B6B 0%, #FFB88C 100%);
        color: white;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-modal-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .btn-modal-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Edit Modal Specific Styles */
    .edit-section {
        margin-bottom: 24px;
    }

    .edit-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .edit-textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #E0E0E0;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: #333;
        resize: vertical;
        box-sizing: border-box;
        transition: all 0.2s;
    }

    .edit-textarea:focus {
        outline: none;
        border-color: #FF6B6B;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
    }

    .edit-section small {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #666;
    }
</style>
