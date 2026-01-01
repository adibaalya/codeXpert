<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #FFF9F9;
        color: #333;
        line-height: 1.6;
    }

    .profile-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
    }

    .profile-sidebar {
        background: white;
        border-radius: 24px;
        padding: 40px 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        height: fit-content;
        position: sticky;
        top: 20px;
    }


    .profile-avatar svg {
        width: 60px;
        height: 60px;
        color: white;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 700;
        color: #2d2d2d;
        text-align: center;
        margin-bottom: 8px;
    }

    .profile-email {
        font-size: 13px;
        color: #8e8e93;
        text-align: center;
        margin-bottom: 20px;
    }

    .info-label {
        font-size: 11px;
        color: #8e8e93;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 14px;
        color: #2d2d2d;
        font-weight: 500;
    }

    .profile-main {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    /* Stats Cards */
    .stats-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .stats-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 25px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .stat-label {
        font-size: 13px;
        color: #6B7280;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 800;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .competency-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 25px;
    }

    /* Achievements Section */
    .achievements-section {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .achievement-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .achievement-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .achievement-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .achievement-icon.blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .achievement-icon.purple {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
    }

    .achievement-icon.green {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .achievement-icon.orange {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .achievement-icon svg {
        width: 28px;
        height: 28px;
        color: white;
    }

    .achievement-content h4 {
        font-size: 14px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 2px;
    }

    .achievement-content p {
        font-size: 12px;
        color: #8e8e93;
    }

    /* ========== EDIT PROFILE ========== */
    .edit-profile-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .edit-profile-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .edit-profile-header {
        margin-bottom: 40px;
    }

    .edit-profile-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 8px;
    }

    .edit-profile-header p {
        font-size: 14px;
        color: #8e8e93;
    }

    .form-group {
        margin-bottom: 30px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #2d2d2d;
        margin-bottom: 8px;
    }

    .photo-upload-section {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 24px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e5e7eb;
        border-radius: 16px;
    }

    .photo-upload-content {
        flex: 1;
    }

    .photo-upload-content h3 {
        font-size: 16px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 8px;
    }

    .photo-upload-content p {
        font-size: 13px;
        color: #8e8e93;
        margin-bottom: 16px;
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .remove-photo-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 2px solid #ef4444;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #ef4444;
        cursor: pointer;
        transition: all 0.2s;
        margin-left: 12px;
    }

    .remove-photo-btn:hover {
        background: #ef4444;
        color: white;
    }

    .form-actions {
        display: flex;
        gap: 16px;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-cancel {
        flex: 1;
        padding: 14px 24px;
        background: white;
        color: #6e6e73;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        border-color: #d1d5db;
        color: #2d2d2d;
    }

    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #16a34a;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #dc2626;
    }

    .file-name-display {
        margin-top: 8px;
        font-size: 13px;
        color: #6e6e73;
    }

    /* ========== REVIEWER PROFILE ========== */

    .reviewer-body {
        font-family: 'Inter', sans-serif;
        background:rgb(248, 244, 253);
        min-height: 100vh;
    }

    .profile-avatar-reviewer {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(164, 53, 255, 0.3);
        overflow: hidden;
    }

    .profile-avatar-reviewer svg {
        width: 60px;
        height: 60px;
        color: white;
    }

    /* reviewer */
    .verified-badge-reviewer {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(218, 53, 255, 0.1);
        border: 1px solid rgba(167, 53, 255, 0.3);
        border-radius: 20px;
        padding: 8px 16px;
        margin: 20px auto;
        display: flex;
        width: fit-content;
    }

    .verified-badge-reviewer  svg {
        width: 16px;
        height: 16px;
        color: rgb(92, 33, 195);
    }

    .verified-badge-reviewer  span {
        font-size: 11px;
        font-weight: 700;
        color: rgb(92, 33, 195);
        letter-spacing: 0.5px;
    }

    /* reviewer */
    .profile-info-item-reviewer {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .profile-info-item-reviewer svg {
        width: 18px;
        height: 18px;
        color: rgb(92, 33, 195);
        flex-shrink: 0;
    }

    .profile-info-item-reviewer .info-content {
        flex: 1;
    }

    .challenge-start-btn-reviewer {
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        color: white;
        padding: 14px 32px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(164, 53, 255,  0.4);
    }

    .challenge-start-btn-reviewer:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(164, 53, 255, 0.5);
    }

    .challenge-start-btn-reviewer:active {
        transform: translateY(0);
    }

    .stat-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #f0f0f0;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
    }

    .stat-icon.green {
        background: rgba(34, 197, 94, 0.1);
    }

    .stat-icon.purple {
        background: rgba(168, 85, 247, 0.1);
    }

    .stat-icon.orange {
        background: rgba(249, 115, 22, 0.1);
    }

    .stat-icon svg {
        width: 20px;
        height: 20px;
    }

    .stat-icon.blue svg {
        color: #3b82f6;
    }

    .stat-icon.green svg {
        color: #22c55e;
    }

    .stat-icon.purple svg {
        color: #a855f7;
    }

    .stat-icon.orange svg {
        color: #f97316;
    }

    .certification-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 10px;
    }

    .certification-info h4 {
        font-size: 16px;
        font-weight: 700;
        color: #2d2d2d;
        margin-bottom: 4px;
    }

    .certification-info p {
        font-size: 13px;
        color: #8e8e93;
    }

    .certified-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 20px;
        padding: 8px 16px;
    }

    .certified-badge svg {
        width: 16px;
        height: 16px;
        color: #22c55e;
    }

    .certified-badge span {
        font-size: 11px;
        font-weight: 700;
        color: #22c55e;
        letter-spacing: 0.5px;
    }

    .scores-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .score-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 15px;
    }

    .score-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .score-label {
        font-size: 13px;
        color: #6e6e73;
        font-weight: 500;
    }

    .score-value {
        font-size: 28px;
        font-weight: 700;
        color: #2d2d2d;
    }

    .score-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid;
    }

    .score-icon.green {
        border-color: #22c55e;
        background: rgba(34, 197, 94, 0.1);
    }

    .score-icon.blue {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .score-icon svg {
        width: 24px;
        height: 24px;
    }

    .score-icon.green svg {
        color: #22c55e;
    }

    .score-icon.blue svg {
        color: #3b82f6;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: #f0f0f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #22c55e 0%, #10b981 100%);
        border-radius: 8px;
        transition: width 1s ease;
    }

    .progress-bar.blue {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    }

    /* ========== REVIEWER EDIT PROFILE ========== */
    .form-input-reviewer {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.2s;
    }

    .form-input-reviewer:focus {
        outline: none;
        border-color: rgb(92, 33, 195);
    }

    .form-input-reviewer:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }

    .current-photo-reviewer {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(164, 53, 255, 0.3);
        overflow: hidden;
        flex-shrink: 0;
    }

    .current-photo-reviewer img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .current-photo-reviewer svg {
        width: 60px;
        height: 60px;
        color: white;
    }

    .upload-btn-reviewer {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #2d2d2d;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-btn-reviewer:hover {
        border-color: rgb(92, 33, 195);
        color: rgb(92, 33, 195);
    }

    .btn-save-reviewer {
        flex: 1;
        padding: 14px 24px;
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-save-reviewer:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(164, 53, 255, 0.3);
    }

    /* ========== LEARNER PROFILE  ========== */

    .profile-avatar {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
        overflow: hidden;
    }

    .profile-info-section {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #f0f0f0;
    }

    .profile-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .profile-info-item svg {
        width: 18px;
        height: 18px;
        color: #FF6B35;
        flex-shrink: 0;
    }

    .profile-info-item .info-content {
        flex: 1;
    }

    .challenge-start-btn {
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        color: white;
        padding: 14px 32px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
    }

    .challenge-start-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5);
    }

    .challenge-start-btn:active {
        transform: translateY(0);
    }

    .left-column {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .progress-stats-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 25px;
        align-items: start;
    }
    
    .stats-list-vertical {
        background: white;
        border-radius: 16px;
        padding: 0;
    }

    .stat-item-vertical {
        padding: 10px 0;
    }

    .stat-item-vertical .stat-label {
        font-size: 12px;
        color: #6e6e73;
        margin-bottom: 8px;
        font-weight: 400;
    }

    .stat-item-vertical .stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #2d2d2d;
        line-height: 1;
    }

    .stat-divider {
        height: 1px;
        background: #f0f0f0;
        margin: 0;
    }

    /* ========== LEARNER EDIT PROFILE ========== */
    .form-input-learner {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.2s;
    }

    .form-input-learner:focus {
        outline: none;
        border-color: #FF6B35;
    }

    .form-input-learner:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }

    .current-photo-learner {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
        overflow: hidden;
        flex-shrink: 0;
    }

    .current-photo-learner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .current-photo-learner svg {
        width: 60px;
        height: 60px;
        color: white;
    }

    .upload-btn-learner {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #2d2d2d;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-btn-learner:hover {
        border-color: #FF6B35;
        color: #FF6B35;
    }

    .btn-save-learner {
        flex: 1;
        padding: 14px 24px;
        background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-save-learner:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .profile-container {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            position: static;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .scores-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .achievements-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
