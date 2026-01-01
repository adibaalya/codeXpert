<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #FFF9F9;
        min-height: 100vh;
    }

    .reviewer-body {
        font-family: 'Inter', sans-serif;
        background:rgb(248, 244, 253);
        min-height: 100vh;
    }

    /* Main Content */
    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 32px;
    }

    .welcome-section {
        margin-bottom: 40px;
    }

    .welcome-title {
        font-size: 42px;
        font-weight: 800;
        background-image: linear-gradient(135deg, rgb(235, 51, 73) 0%, rgb(244, 92, 67) 50%, rgb(235, 51, 73) 100%);
        -webkit-background-clip: text; 
        background-clip: text; 
        -webkit-text-fill-color: transparent; 
        color: transparent; 
        margin-bottom: 8px;
    }

    .welcome-title-reviewer {
        font-size: 42px;
        font-weight: 800;
        background-image: linear-gradient(135deg,rgb(92, 33, 195) 0%,rgb(118, 47, 183) 50%,rgb(120, 33, 201) 100%);
        -webkit-background-clip: text; 
        background-clip: text; 
        -webkit-text-fill-color: transparent; 
        color: transparent; 
        margin-bottom: 8px;
    }

    .welcome-subtitle {
        font-size: 16px;
        color: #6B7280;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.pending {
        background: #FEE2E2;
        color: #DC2626;
    }

    .stat-icon.approved {
        background: #D1FAE5;
        color: #10B981;
    }

    .stat-icon.total {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .stat-icon.accuracy {
        background: #E9D5FF;
        color: #A855F7;
    }

    .stat-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .stat-badge.urgent {
        border: 2px solid rgb(255, 255, 255);
        background: #FEE2E2;
        color: #DC2626;
    }

    .stat-badge.trending {
        background: #D1FAE5;
        color: #10B981;
    }

    .stat-badge.info {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .stat-badge.purple {
        background: #E9D5FF;
        color: #A855F7;
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

    .stat-progress {
        width: 100%;
        height: 4px;
        background: #E5E7EB;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .stat-progress-bar {
        height: 100%;
        background: linear-gradient(to right, #FF6B35, #FFB83D);
        border-radius: 2px;
    }

    .stat-footer {
        font-size: 12px;
        color: #10B981;
        font-weight: 600;
    }

    /* Today's Challenge Card */
    .todays-challenge-card {
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 32px;
        display: flex;
        gap: 32px;
        align-items: flex-start;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.3);
        position: relative;
        overflow: hidden;
    }

    .todays-challenge-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .challenge-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
        position: relative;
        z-index: 1;
    }

    .challenge-content {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .challenge-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .challenge-title {
        font-size: 18px;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .challenge-badge {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .challenge-name {
        font-size: 28px;
        font-weight: 800;
        color: white;
        margin: 0 0 16px 0;
    }

    .challenge-description {
        font-size: 15px;
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .challenge-tags {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .challenge-tag {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: white;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .tag-icon {
        font-size: 12px;
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

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    /* Weekly Activity */
    .activity-card {
        background: white;
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-shrink: 0;
    }

    .weekly-total {
        text-align: right;
    }

    .weekly-total-label {
        font-size: 13px;
        color: #6B7280;
        margin-bottom: 4px;
    }

    .weekly-total-value {
        font-size: 32px;
        font-weight: 800;
        color: #1F2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .trend-icon {
        color: #EF4444;
        font-size: 20px;
    }

    .chart-container {
        height: 350px;
        max-height: 350px;
        position: relative;
        margin-bottom: 20px;
        flex-shrink: 0;
    }

    /* Action Cards */
    .action-cards {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Language Progress Card */
    .language-progress-card {
        background: white;
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .language-progress-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
    }

    /**.language-progress-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }*/

    .language-progress-title {
        font-size: 22px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 4px 0;
    }

    .language-progress-subtitle {
        font-size: 14px;
        color: #6B7280;
        margin: 0;
    }

    .language-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 24px;
    }

    .language-item {
        padding: 0;
    }

    .language-item.empty {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .empty-text {
        font-size: 16px;
        font-weight: 600;
        color: #6B7280;
        margin: 0;
    }

    .empty-subtext {
        font-size: 14px;
        color: #9CA3AF;
        margin: 0;
    }

    .language-item-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
    }

    .language-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .language-details {
        flex: 1;
    }

    .language-name {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .language-problems {
        font-size: 13px;
        color: #6B7280;
    }

    .language-percentage {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
    }

    .language-progress-bar-container {
        width: 100%;
        height: 8px;
        background: #E5E7EB;
        border-radius: 4px;
        overflow: hidden;
    }

    .language-progress-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .add-language-btn {
        width: 100%;
        padding: 16px;
        background: white;
        border: 2px dashed #D1D5DB;
        border-radius: 12px;
        color: #6B7280;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .add-language-btn:hover {
        background: #F9FAFB;
        border-color: #FF6B35;
        color: #FF6B35;
        transform: translateY(-2px);
    }

    .add-language-btn:active {
        transform: translateY(0);
    }

    /* Bottom Grid */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    /* Urgent Reviews */
    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 20px;
    }

    .review-item {
        background: #F9FAFB;
        padding: 20px;
        border-radius: 12px;
        border: 2px solid #E5E7EB;
        transition: all 0.2s;
    }

    .review-item:hover {
        border-color: #7C3AED;
        background: white;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .review-title-text {
        font-size: 15px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .review-meta {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .review-level {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .review-level.advanced {
        background: #FEE2E2;
        color: #DC2626;
    }

    .review-level.intermediate {
        background: #DBEAFE;
        color: #3B82F6;
    }

    .review-level.beginner {
        background: #D1FAE5;
        color: #10B981;
    }

    .review-level.priority {
        background: #FEE2E2;
        color: #DC2626;
    }

    .review-time {
        font-size: 12px;
        color: #6B7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .review-btn {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .review-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(164, 53, 255, 0.3);
    }

    /* Recent Activity */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 20px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: #F9FAFB;
        border-radius: 12px;
        border: 2px solid #E5E7EB;
    }

    .activity-item.approved {
        background: #F0FDF4;
        border-color: #BBF7D0;
    }

    .activity-item.rejected {
        background: #FEF2F2;
        border-color: #FECACA;
    }

    .activity-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .activity-avatar.approved {
        background: #10B981;
    }

    .activity-avatar.rejected {
        background: #EF4444;
    }

    .activity-content {
        flex: 1;
    }

    .activity-text {
        font-size: 14px;
        color: #1F2937;
        margin-bottom: 4px;
    }

    .activity-text strong {
        font-weight: 700;
    }

    .activity-time {
        font-size: 12px;
        color: #6B7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }

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
    }

    .floating-help:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5);
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .content-grid,
        .bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .nav-menu {
            display: none;
        }
    }

    /* Action Cards */
    .action-cards {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .action-card {
        background: white;
        padding: 32px 28px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .action-card.primary {
        border-color: rgba(255, 107, 53, 0.15);
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
    }

    .action-card.primary:hover {
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.15);
    }

    .action-card.secondary {
        border-color: rgba(168, 85, 247, 0.15);
        background: linear-gradient(to right, rgba(168, 85, 247, 0.02), rgba(206, 147, 216, 0.02));
    }

    .action-card.secondary:hover {
        border-color: rgba(168, 85, 247, 0.3);
        box-shadow: 0 8px 24px rgba(168, 85, 247, 0.15);
    }

    .action-card.tertiary {
        border-color: rgba(59, 130, 246, 0.15);
        background: linear-gradient(to right, rgba(59, 130, 246, 0.02), rgba(144, 202, 249, 0.02));
    }

    .action-card.tertiary:hover {
        border-color: rgba(59, 130, 246, 0.3);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
    }

    .action-icon-box {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        background: linear-gradient(135deg, #FF7B54 0%, #FFB562 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.25);
    }

    .action-icon-box.purple {
        background: linear-gradient(135deg, #B085F5 0%, #D4A5FF 100%);
        box-shadow: 0 4px 12px rgba(168, 85, 247, 0.25);
    }

    .action-icon-box.blue {
        background: linear-gradient(135deg, #5FA9F3 0%, #90C8F9 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    }

    .action-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .action-title-primary {
        font-size: 20px;
        font-weight: 700;
        color:rgb(255, 255, 255);
        margin: 0;
    }

    .action-description-primary {
        font-size: 14px;
        color:rgb(245, 245, 245);
        line-height: 1.5;
        margin: 0;
    }

    .action-title {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .action-description {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.5;
        margin: 0;
    }

    .action-btn {
        padding: 14px 28px;
        border: none;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .action-card.primary .action-btn {
        background: white;
        color: rgb(244, 92, 67);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .action-card.primary .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 107, 53, 0.4);
    }

    .action-card.secondary .action-btn {
        background: linear-gradient(135deg, rgba(186, 104, 200, 0.1) 0%, rgba(206, 147, 216, 0.1) 100%);
        color: #8B5CF6;
        border: 2px solid #B085F5;
    }

    .action-card.secondary .action-btn:hover {
        background: linear-gradient(135deg, rgba(186, 104, 200, 0.15) 0%, rgba(206, 147, 216, 0.15) 100%);
        transform: translateY(-2px);
    }

    .action-card.tertiary .action-btn {
        background: linear-gradient(135deg, rgba(100, 181, 246, 0.1) 0%, rgba(144, 202, 249, 0.1) 100%);
        color: #3B82F6;
        border: 2px solid #64B5F6;
    }

    .action-card.tertiary .action-btn:hover {
        background: linear-gradient(135deg, rgba(100, 181, 246, 0.15) 0%, rgba(144, 202, 249, 0.15) 100%);
        transform: translateY(-2px);
    }

    .action-btn:active {
        transform: translateY(0);
    }

    /* Hackathons Section */
    .hackathon-item {
        background: #F9FAFB;
        padding: 24px;
        border-radius: 16px;
        margin-bottom: 16px;
        border: 2px solid #E5E7EB;
        transition: all 0.3s ease;
    }

    .hackathon-item:last-child {
        margin-bottom: 0;
    }

    .hackathon-item.live {
        background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
        border-color: #FCA5A5;
    }

    .hackathon-item.upcoming {
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border-color: #93C5FD;
    }

    .hackathon-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .hackathon-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
    }

    .live-badge {
        background: #DC2626;
        color: white;
        animation: pulse 2s infinite;
    }

    .live-dot {
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        animation: blink 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
        }
    }

    @keyframes blink {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.3;
        }
    }

    .upcoming-badge {
        background: #3B82F6;
        color: white;
    }

    .hackathon-title {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 8px 0;
    }

    .hackathon-description {
        font-size: 14px;
        color: #6B7280;
        margin: 0 0 20px 0;
        line-height: 1.5;
    }

    .hackathon-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }

    .hackathon-stat {
        background: white;
        padding: 12px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .hackathon-stat-icon {
        font-size: 24px;
    }

    .hackathon-stat-label {
        font-size: 11px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .hackathon-stat-value {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
    }

    .hackathon-stat-value.red {
        color: #DC2626;
    }

    .hackathon-stat-value.blue {
        color: #3B82F6;
    }

    .hackathon-register-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .hackathon-register-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    }

    .hackathon-reminder-btn {
        width: 100%;
        padding: 14px;
        background: white;
        border: 2px solid #3B82F6;
        border-radius: 12px;
        color: #3B82F6;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .hackathon-reminder-btn:hover {
        background: #3B82F6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }

    /* --- Leaderboard List Layout --- */
    .leaderboard-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 20px;
    }

    /* --- Base Card Style (Rank 4+) --- */
    .leaderboard-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border-radius: 12px;
        transition: all 0.3s ease;
        
        /* Default Style for Rank 4, 5, etc. */
        background: #FFFFFF; 
        border: 1px solid #E5E7EB; /* Light Grey Border */
        color: #000000; /* Force Black text base */
    }

    /* --- Rank 1: Gold Theme --- */
    .leaderboard-item.champion {
        /* Soft Cream to Gold Gradient */
        background: linear-gradient(to right, #FFFBEB, #FDE68A);
        border: 2px solid #D97706; /* Strong Gold/Amber */
    }

    /* --- Rank 2: Silver Theme --- */
    .leaderboard-item.runner-up {
        /* White to Cool Silver Gradient */
        background: linear-gradient(to right, #F8FAFC, #E2E8F0);
        border: 2px solid #94A3B8; /* Metallic Silver */
    }

    /* --- Rank 3: Bronze Theme --- */
    .leaderboard-item.third-place {
        /* Soft Mist to Apricot/Bronze Gradient */
        background: linear-gradient(to right, #FFF7ED, #FFEDD5);
        border: 2px solid #EA580C; /* Deep Bronze/Orange */
    }

    /* --- Current User Highlight (If not top 3) --- */
    .leaderboard-item.current-user {
        background: linear-gradient(135deg, #FFE4E6 0%, #FECDD3 100%);
        border: 2px solid #F43F5E; /* Rose/Red Border */
    }

    /* --- Hover Effects --- */
    .leaderboard-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* --- Active User Highlight (Your existing logic) --- */
    .leaderboard-item.highlighted-user {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        /* Note: If you want the gold/silver border to stay, remove the line below. 
        If you want a specific "Active" border color, keep it: */
        border: 2px solid #F43F5E; 
        z-index: 10;
    }

    /* --- Rank Number Badges (The small boxes) --- */
    .leaderboard-rank {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        flex-shrink: 0;
        /* Default Grey for ranks > 3 */
        background: #F3F4F6;
        color: #374151;
        border: 1px solid #E5E7EB;
    }

    /* Badge: Gold (for champion rank - could be position 1 or tied) */
    .leaderboard-item.champion .leaderboard-rank {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
        border: none;
    }

    /* Badge: Silver (for runner-up rank - could be position 2 or tied) */
    .leaderboard-item.runner-up .leaderboard-rank {
        background: linear-gradient(135deg, #94A3B8 0%, #475569 100%);
        box-shadow: 0 4px 12px rgba(148, 163, 184, 0.4);
        color: white;
        border: none;
    }

    /* Badge: Bronze (for third-place rank - could be position 3, 4, or more if there were ties) */
    .leaderboard-item.third-place .leaderboard-rank {
        background: linear-gradient(135deg, #F97316 0%, #C2410C 100%);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        color: white;
        border: none;
    }

    /* Badge: Current User (when highlighted and not in top 3) */
    .leaderboard-item.current-user:not(.champion):not(.runner-up):not(.third-place) .leaderboard-rank {
        background: linear-gradient(135deg, #F43F5E 0%, #E11D48 100%);
        box-shadow: 0 4px 12px rgba(244, 63, 94, 0.4);
        color: white;
        border: none;
    }

    /* --- Text Styling --- */
    .leaderboard-info {
        flex: 1;
    }

    .leaderboard-name {
        font-size: 16px;
        font-weight: 700;
        color: #000000; /* PURE BLACK */
        margin-bottom: 4px;
    }

    .leaderboard-title {
        font-size: 13px;
        color: #374151; /* Dark Grey (almost black) for subtitle */
        font-weight: 500;
    }

    .leaderboard-xp .xp-value {
        font-weight: 700;
        color: #000000; /* Black XP count */
    }

    .trending-up {
        font-size: 13px;
        color: #10B981;
        font-weight: 700;
    }

    .trending-down {
        font-size: 13px;
        color: #EF4444;
        font-weight: 700;
    }

    .trending-neutral {
        font-size: 13px;
        color: #6B7280;
        font-weight: 700;
    }

    .trending-label {
        font-size: 12px;
        color: #6B7280;
    }

    .leaderboard-xp {
        text-align: right;
    }

    .xp-value {
        display: block;
        font-size: 20px;
        font-weight: 800;
        color: #1F2937;
        margin-bottom: 2px;
    }

    .xp-label {
        font-size: 11px;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .leaderboard-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px 0;
        position: relative;
    }

    .leaderboard-divider::before,
    .leaderboard-divider::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(to right, transparent, #E5E7EB, transparent);
    }

    .leaderboard-divider span {
        padding: 0 16px;
        font-size: 12px;
        color: #9CA3AF;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>