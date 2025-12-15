<style>
    /* Leaderboard Page Styles */
    .leaderboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 32px;
    }

    .leaderboard-header {
        margin-bottom: 40px;
        justify-content: center;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .leaderboard-main-title {
        font-size: 36px;
        font-weight: 800;
        background-image: linear-gradient(to right,rgb(250, 90, 75) 10%,rgb(250, 90, 75));
        -webkit-background-clip: text; 
        background-clip: text; 
        -webkit-text-fill-color: transparent; 
        color: transparent; 
        margin-bottom: 8px;
    }

    .leaderboard-subtitle {
        font-size: 16px;
        color: #6B7280;
    }

    /* User Rank Card */
    .user-rank-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px;
        border-radius: 20px;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .user-rank-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .user-rank-info {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        z-index: 1;
    }

    .user-rank-icon {
        width: 64px;
        height: 64px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .user-rank-icon svg {
        width: 32px;
        height: 32px;
        color: white;
    }

    .user-rank-details h3 {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
        margin: 0 0 4px 0;
        font-weight: 600;
    }

    .user-rank-number {
        font-size: 32px;
        font-weight: 800;
        color: white;
        margin: 0;
    }

    .user-stats-row {
        display: flex;
        gap: 48px;
        position: relative;
        z-index: 1;
    }

    .user-stat-item h3 {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
        margin: 0 0 4px 0;
        font-weight: 600;
    }

    .user-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: white;
        margin: 0;
    }

    .user-stat-value.positive {
        color: #86efac;
    }

    /* Top Performers Section */
    .top-performers-section {
        background: white;
        padding: 32px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #F3F4F6;
    }

    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1F2937;
        margin: 0;
    }

    .section-subtitle {
        font-size: 14px;
        color: #6B7280;
        margin: 4px 0 0 0;
    }

    /* Leaderboard Table */
    .leaderboard-table {
        width: 100%;
    }

    .leaderboard-table-header {
        display: grid;
        grid-template-columns: 80px 1fr 150px 150px;
        gap: 16px;
        padding: 12px 20px;
        margin-bottom: 8px;
    }

    .table-header-cell {
        font-size: 12px;
        font-weight: 700;
        color: #FF6B35;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-header-cell.align-right {
        text-align: right;
    }

    /* Leaderboard Row */
    .leaderboard-row {
        display: grid;
        grid-template-columns: 80px 1fr 150px 150px;
        gap: 16px;
        align-items: center;
        padding: 20px;
        margin-bottom: 8px;
        border-radius: 16px;
        transition: all 0.3s ease;
        background: #F9FAFB;
        border: 2px solid transparent;
    }

    .leaderboard-row:hover {
        background: white;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* Top 3 Rows */
    .leaderboard-row.rank-1 {
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        border-color: #FCD34D;
    }

    .leaderboard-row.rank-2 {
        background: linear-gradient(135deg, #E0E7FF 0%, #C7D2FE 100%);
        border-color: #A5B4FC;
    }

    .leaderboard-row.rank-3 {
        background: linear-gradient(135deg, #FFEDD5 0%, #FED7AA 100%);
        border-color: #FDBA74;
    }

    .leaderboard-row.rank-1:hover,
    .leaderboard-row.rank-2:hover,
    .leaderboard-row.rank-3:hover {
        transform: translateX(4px) translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    /* Rank Badge */
    .rank-badge-cell {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rank-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 800;
        color: white;
        position: relative;
    }

    .rank-badge.top-1 {
        background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }

    .rank-badge.top-1::after {
        content: '🥇';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 20px;
    }

    .rank-badge.top-2 {
        background: linear-gradient(135deg, #6366F1 0%, #818CF8 100%);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    .rank-badge.top-2::after {
        content: '🥈';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 20px;
    }

    .rank-badge.top-3 {
        background: linear-gradient(135deg, #F97316 0%, #FB923C 100%);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
    }

    .rank-badge.top-3::after {
        content: '🥉';
        position: absolute;
        top: -8px;
        right: -8px;
        font-size: 20px;
    }

    .rank-badge.regular {
        background: linear-gradient(135deg, #9CA3AF 0%, #6B7280 100%);
        font-size: 16px;
    }

    /* Username Cell */
    .username-cell {
        font-size: 16px;
        font-weight: 600;
        color: #1F2937;
    }

    /* XP Cell */
    .xp-cell {
        text-align: right;
    }

    .xp-amount {
        font-size: 18px;
        font-weight: 700;
        color: #1F2937;
    }

    .xp-label-small {
        font-size: 12px;
        color: #9CA3AF;
        margin-left: 4px;
    }

    /* This Week Cell */
    .week-cell {
        text-align: right;
    }

    .week-gain {
        font-size: 16px;
        font-weight: 700;
        color: #10B981;
    }

    .week-gain.positive::before {
        content: '+';
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .leaderboard-table-header,
        .leaderboard-row {
            grid-template-columns: 60px 1fr 120px 120px;
            gap: 12px;
            padding: 16px;
        }

        .user-stats-row {
            gap: 32px;
        }

        .user-stat-value {
            font-size: 24px;
        }
    }

    @media (max-width: 768px) {
        .user-rank-card {
            flex-direction: column;
            gap: 24px;
            text-align: center;
        }

        .user-rank-info {
            flex-direction: column;
        }

        .user-stats-row {
            width: 100%;
            justify-content: space-around;
        }

        .leaderboard-table-header,
        .leaderboard-row {
            grid-template-columns: 50px 1fr 100px;
            gap: 8px;
            padding: 12px;
        }

        .week-cell {
            display: none;
        }

        .table-header-cell:last-child {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .leaderboard-main-title {
            font-size: 28px;
        }

        .user-rank-number {
            font-size: 28px;
        }

        .user-stat-value {
            font-size: 20px;
        }

        .section-title {
            font-size: 20px;
        }

        .rank-badge {
            width: 40px;
            height: 40px;
            font-size: 14px;
        }
    }
</style>
