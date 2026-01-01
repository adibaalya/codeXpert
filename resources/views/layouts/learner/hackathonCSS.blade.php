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

    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 32px;
    }
    
    .page-header {
        margin-bottom: 12px;
        justify-content: center;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .page-title {
        font-size: 42px;
        font-weight: 800;
        background-image: linear-gradient(135deg, rgb(235, 51, 73) 0%, rgb(244, 92, 67) 50%, rgb(235, 51, 73) 100%);
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

    .featured-challenge {
        background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
        border-radius: 20px;
        padding: 32px;
        margin-bottom: 32px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .featured-challenge::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .featured-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.25);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
        backdrop-filter: blur(10px);
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .featured-challenge h2 {
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 12px 0;
        position: relative;
        z-index: 1;
    }

    .featured-challenge p {
        font-size: 16px;
        opacity: 0.95;
        margin-bottom: 24px;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }

    .featured-stats {
        display: flex;
        gap: 32px;
        margin-bottom: 24px;
        position: relative;
        z-index: 1;
    }

    .featured-stat {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .featured-stat-icon {
        font-size: 24px;
    }

    .featured-stat-info {
        display: flex;
        flex-direction: column;
    }

    .featured-stat-label {
        font-size: 13px;
        opacity: 0.9;
        font-weight: 500;
    }

    .featured-stat-value {
        font-size: 18px;
        font-weight: 700;
    }

    .featured-btn {
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        text-decoration: none;
    }

    .featured-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .countdown-timer {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 12px 20px;
        border-radius: 12px;
        margin-left: 16px;
        backdrop-filter: blur(10px);
    }

    .countdown-timer svg {
        width: 20px;
        height: 20px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .filter-tabs {
        display: flex;
        gap: 12px;
        background: #F3F4F6;
        padding: 6px;
        border-radius: 12px;
    }

    .filter-tab {
        padding: 10px 20px;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #6B7280;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-tab.active {
        background: white;
        color: #FF6B35;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .hackathon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .hackathon-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 2px solid #E5E7EB;
        transition: all 0.3s ease;
        position: relative;
    }

    .hackathon-card:hover {
        border-color: #FF6B35;
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(255, 107, 53, 0.1);
    }

    .hackathon-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .hackathon-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .hackathon-status-badge.live {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: white;
    }

    .hackathon-status-badge.upcoming {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        color: white;
    }

    .hackathon-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 8px 0;
    }

    .hackathon-card-description {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .hackathon-card-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 20px;
        padding: 16px;
        background: #F9FAFB;
        border-radius: 12px;
    }

    .hackathon-card-stat {
        text-align: center;
    }

    .hackathon-card-stat-icon {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .hackathon-card-stat-label {
        font-size: 11px;
        color: #6B7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .hackathon-card-stat-value {
        font-size: 16px;
        font-weight: 700;
        color: #1F2937;
    }

    .hackathon-card-footer {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .hackathon-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        background: #EEF2FF;
        color: #6366F1;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .hackathon-card-button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .hackathon-card-button.primary {
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        color: white;
    }

    .hackathon-card-button.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(255, 107, 53, 0.3);
    }

    .hackathon-card-button.secondary {
        background: #F3F4F6;
        color: #6B7280;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stats-overview-card {
        background: white;
        border-radius: 16px;
        padding: 28px 24px;
        border: 2px solid #E5E7EB;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }

    .stats-overview-card:hover {
        border-color: #FF6B35;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.1);
    }

    .stats-overview-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }

    .stats-overview-icon.active {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    }

    .stats-overview-icon.prizes {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }

    .stats-overview-icon.participants {
        background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    }

    .stats-overview-content {
        flex: 1;
        min-width: 0;
    }

    .stats-overview-label {
        font-size: 14px;
        color: #6B7280;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    .stats-overview-value {
        font-size: 32px;
        font-weight: 800;
        color: #1F2937;
        line-height: 1;
        display: block;
    }

    .stats-overview-value.highlight {
        background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stats-overview-card {
        background: white;
        border-radius: 16px;
        padding: 28px 24px;
        border: 2px solid #E5E7EB;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }

    .stats-overview-card:hover {
        border-color: #FF6B35;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.1);
    }

    .stats-overview-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }

    .stats-overview-icon.active {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    }

    .stats-overview-icon.prizes {
        background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    }

    .stats-overview-icon.participants {
        background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    }

    .stats-overview-content {
        flex: 1;
        min-width: 0;
    }

    .stats-overview-label {
        font-size: 14px;
        color: #6B7280;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }

    .stats-overview-value {
        font-size: 32px;
        font-weight: 800;
        color: #1F2937;
        line-height: 1;
        display: block;
    }

    .stats-overview-value.highlight {
        background: linear-gradient(135deg, #FF6B35 0%, #FFB83D 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Stats Sidebar (Right side of hackathon grid) */
    .hackathon-grid-container {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        margin-bottom: 32px;
    }

    .stats-sidebar {
        background: white;
        border-radius: 20px;
        padding: 28px;
        border: 2px solid #E5E7EB;
        height: fit-content;
        position: sticky;
        top: 20px;
    }

    .stats-sidebar-header {
        margin-bottom: 24px;
    }

    .stats-sidebar-title {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 8px 0;
    }

    .stats-sidebar-subtitle {
        font-size: 14px;
        color: #6B7280;
        margin: 0;
    }

    .stats-sidebar-divider {
        height: 1px;
        background: #E5E7EB;
        margin: 20px 0;
    }

    .stats-sidebar-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
    }

    .stats-sidebar-item:not(:last-child) {
        border-bottom: 1px solid #F3F4F6;
    }

    .stats-sidebar-label {
        font-size: 14px;
        color: #4B5563;
        font-weight: 500;
    }

    .stats-sidebar-value {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .stats-sidebar-value.highlight {
        color: #F59E0B;
    }

    @media (max-width: 1200px) {
        .hackathon-grid-container {
            grid-template-columns: 1fr;
        }

        .stats-sidebar {
            position: relative;
            top: 0;
        }
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        border: 2px dashed #E5E7EB;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1F2937;
        margin: 0 0 8px 0;
    }

    .empty-state p {
        font-size: 14px;
        color: #6B7280;
        margin: 0;
    }
</style>
