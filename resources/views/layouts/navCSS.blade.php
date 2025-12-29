<style>
    /* Header Styles */
    .header {
            background: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 24px;
        }

        .logo-text {
            font-size: 25px; 
            font-weight: 800; 
            /* Deep Violet to Dark Slate Gradient */
            background-image: linear-gradient(to right, rgb(106, 17, 203), rgb(252, 102, 37));
            -webkit-background-clip: text; 
            background-clip: text; 
            -webkit-text-fill-color: transparent; 
            color: transparent; 
            margin-top: -2px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-menu {
            display: flex;
            gap: 8px;
            margin-left: 32px;
        }

        .nav-item {
            padding: 10px 24px;
            background: transparent;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgb(255, 65, 108) 0%, rgb(255, 75, 43) 50%, rgb(255, 140, 0) 100%);
            color: white;
        }

        .nav-item:hover:not(.active) {
            background: #F3F4F6;
            color: #1F2937;
        }

        .nav-item.active-reviewer {
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
        }

        .nav-item:hover:not(.active-reviewer) {
            background: #F3F4F6;
            color: #1F2937;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #1F2937;
        }

        .user-role {
            font-size: 12px;
            color: #6B7280;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
        }

        .user-avatar-reviewer {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        /* User Dropdown Menu */
        .user-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            width: 340px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .user-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
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

        /* REVIEWER*/
        .user-dropdown-header-reviewer {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: linear-gradient(135deg, #7C3AED 0%, #A855F7 50%, #9333EA 100%);
            color: white;
        }

        /*LEARNER*/

        .user-dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: linear-gradient(135deg, rgb(255, 65, 108) 0%, rgb(255, 75, 43) 50%, rgb(255, 140, 0) 100%);
            color: white;
        }

        .user-dropdown-avatar {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .user-dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-dropdown-name {
            font-size: 16px;
            font-weight: 700;
        }

        .user-dropdown-email {
            font-size: 13px;
            opacity: 0.9;
        }

        .verified-badge-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #DEF7EC;
            color: #03543F;
            font-size: 13px;
            font-weight: 600;
        }

        .verified-badge-dropdown svg {
            color: #0E9F6E;
        }

        .user-dropdown-section {
            padding: 16px 20px;
        }

        .user-dropdown-title {
            font-size: 11px;
            font-weight: 700;
            color: #FF6B35;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .user-dropdown-role {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #FFF9F5;
            border-radius: 10px;
            border: 1px solid #FFE5D9;
        }

        .role-icon {
            font-size: 24px;
        }

        .role-name {
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
        }

        .role-desc {
            font-size: 12px;
            color: #6B7280;
        }

        .user-dropdown-divider {
            height: 1px;
            background: #E5E7EB;
            margin: 8px 0;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
        }

        .user-dropdown-item:hover {
            background: #F9FAFB;
            color: #FF6B35;
        }

        .user-dropdown-item svg {
            color: #6B7280;
            transition: all 0.2s;
        }

        .user-dropdown-item:hover svg {
            color: #FF6B35;
        }

        .user-dropdown-item.logout {
            color: #DC2626;
        }

        .user-dropdown-item.logout:hover {
            background: #FEE2E2;
            color: #DC2626;
        }

        .user-dropdown-item.logout svg {
            color: #DC2626;
        }
</style>