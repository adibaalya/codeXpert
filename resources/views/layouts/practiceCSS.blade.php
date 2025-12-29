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

    .practice-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .practice-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .practice-title {
        font-size: 42px;
        font-weight: 800;
        background-image: linear-gradient(135deg, rgb(235, 51, 73) 0%, rgb(244, 92, 67) 50%, rgb(235, 51, 73) 100%);
        -webkit-background-clip: text; 
        background-clip: text; 
        -webkit-text-fill-color: transparent; 
        color: transparent; 
        margin-bottom: 8px;
    }

    .practice-subtitle {
        font-size: 16px;
        color: #6B7280;
    }

    /* Selection Card Styles */
    .selection-card {
        background: white;
        border-radius: 20px;
        padding: 35px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .selection-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: linear-gradient(135deg, #FFB83D 0%, #FF6B35 100%);
    }

    .card-header-text h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 4px;
    }

    .card-header-text p {
        font-size: 0.9rem;
        color: #64748B;
        font-weight: 500;
    }

    /* Programming Language Grid */
    .language-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 18px;
        margin-bottom: 10px;
    }

    .language-option {
        background: #F8FAFC;
        border: 2.5px solid #E2E8F0;
        border-radius: 16px;
        padding: 24px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .language-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4C6EF5 0%, #8B5CF6 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .language-option:hover {
        border-color: #C7D2FE;
        background: #F1F5F9;
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(76, 110, 245, 0.15);
    }

    .language-option:hover::before {
        transform: scaleX(1);
    }

    .language-option.selected {
        border-color: #FF8A5B;
        background: linear-gradient(135deg, #FFF5F0 0%, #FFE8DD 100%);
        box-shadow: 0 8px 25px rgba(255, 107, 53, 0.25);
        transform: translateY(-2px);
    }

    .language-option.selected::before {
        transform: scaleX(1);
        background: linear-gradient(90deg, #FF6B35 0%, #FFB83D 100%);
    }

    .language-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 28px;
        transition: transform 0.3s ease;
    }

    .language-option:hover .language-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .language-option.selected .language-icon {
        transform: scale(1.05);
    }

    .language-icon.python { background: linear-gradient(135deg, #4C6EF5 0%, #3B5BDB 100%); }
    .language-icon.java { background: linear-gradient(135deg, #FF8A5B 0%, #F76707 100%); }
    .language-icon.cpp { background: linear-gradient(135deg, #4C6EF5 0%, #364FC7 100%); }
    .language-icon.javascript { background: linear-gradient(135deg, #FFD43B 0%, #FAB005 100%); }

    .language-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 6px;
    }

    .language-description {
        font-size: 0.8rem;
        color: #64748B;
        font-weight: 500;
    }

    /* Difficulty Level Options */
    .difficulty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
    }

    .difficulty-option {
        background: #F8FAFC;
        border: 2.5px solid #E2E8F0;
        border-radius: 16px;
        padding: 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .difficulty-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4C6EF5 0%, #8B5CF6 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .difficulty-option:hover {
        border-color: #C7D2FE;
        background: #F1F5F9;
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(76, 110, 245, 0.15);
    }

    .difficulty-option:hover::before {
        transform: scaleX(1);
    }

    .difficulty-option.selected {
        border-color: #FF8A5B;
        background: linear-gradient(135deg, #FFF5F0 0%, #FFE8DD 100%);
        box-shadow: 0 8px 25px rgba(255, 107, 53, 0.25);
        transform: translateY(-2px);
    }

    .difficulty-option.selected::before {
        transform: scaleX(1);
        background: linear-gradient(90deg, #FF6B35 0%, #FFB83D 100%);
    }

    .difficulty-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .difficulty-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: transform 0.3s ease;
    }

    .difficulty-option:hover .difficulty-icon {
        transform: scale(1.1) rotate(-5deg);
    }

    .difficulty-icon.beginner { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
    .difficulty-icon.intermediate { background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); }
    .difficulty-icon.advanced { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); }

    .difficulty-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 4px;
    }

    .difficulty-subtitle {
        font-size: 0.85rem;
        color: #64748B;
        font-weight: 500;
    }

    /* Skills Grid */
    .skills-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 14px;
    }

    .skill-option {
        background: #F8FAFC;
        border: 2.5px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 0.95rem;
        font-weight: 600;
        color: #475569;
    }

    .skill-option:hover {
        border-color: #C7D2FE;
        background: #F1F5F9;
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.15);
        color: #1E293B;
    }

    .skill-option.selected {
        border-color: #FF8A5B;
        background: linear-gradient(135deg, #FFF5F0 0%, #FFE8DD 100%);
        color: #FF6B35;
        box-shadow: 0 6px 20px rgba(255, 107, 53, 0.2);
        transform: translateY(-2px);
    }

    /* Action Section */
    .action-section {
        background: white;
        border-radius: 20px;
        padding: 30px 35px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .ready-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .ready-text {
        font-size: 0.9rem;
        color: #64748B;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .ready-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ready-tag {
        background: linear-gradient(135deg, #FFF5F0 0%, #FFE8DD 100%);
        color: #FF8A5B;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1.5px solid #FF8A5B;
    }

    .start-button {
        background: linear-gradient(135deg, rgb(255, 87, 34) 0%, rgb(255, 167, 38) 100%);
        color: white;
        border: none;
        padding: 18px 50px;
        border-radius: 14px;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 6px 25px rgba(255, 107, 53, 0.3);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .start-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 35px rgba(255, 107, 53, 0.4);
    }

    .start-button:active {
        transform: translateY(-1px);
    }

    .start-button:disabled {
        background: #E2E8F0;
        color: #94A3B8;
        cursor: not-allowed;
        box-shadow: none;
    }

    .start-button:disabled:hover {
        transform: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .practice-title {
            font-size: 2rem;
        }

        .language-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .difficulty-grid {
            grid-template-columns: 1fr;
        }

        .skills-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-section {
            flex-direction: column;
            text-align: center;
        }

        .ready-section {
            flex-direction: column;
            width: 100%;
        }

        .start-button {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .language-grid {
            grid-template-columns: 1fr;
        }

        .selection-card {
            padding: 25px 20px;
        }

        .practice-container {
            padding: 30px 15px;
        }
    }

    /* Loading spinner animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
