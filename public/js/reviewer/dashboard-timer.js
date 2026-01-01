/**
 * public/js/dashboard-timer.js
 * Handles real-time updates for:
 * 1. Urgent Review counters (counting up duration)
 * 2. Recent Activity timestamps (updating "X minutes ago")
 */

document.addEventListener('DOMContentLoaded', function() {
    // State variables
    let urgentTimers = [];
    let recentTimers = [];
    let timerInterval = null;

    // 1. Initialize timers based on DOM elements
    function initializeTimers() {
        // Setup Urgent Review Timers (e.g., "Pending 3d")
        document.querySelectorAll('.urgent-timer').forEach((element) => {
            const seconds = parseInt(element.getAttribute('data-seconds')) || 0;
            urgentTimers.push({
                element: element.querySelector('.timer-value'),
                startTime: Date.now() - (seconds * 1000),
                seconds: seconds
            });
        });

        // Setup Recent Activity Timers (e.g., "2 minutes ago")
        document.querySelectorAll('.recent-timer').forEach((element) => {
            const timeText = element.getAttribute('data-time');
            const milliseconds = parseTimeToMilliseconds(timeText);
            
            recentTimers.push({
                element: element.querySelector('.timer-value'),
                startTime: Date.now() - milliseconds,
                originalText: timeText
            });
        });

        // Start the loop
        startTimerUpdates();
    }

    // 2. Helper: Parse readable strings (e.g., "2 days") into ms
    function parseTimeToMilliseconds(timeText) {
        if (!timeText) return 0;
        
        const secondsMatch = timeText.match(/(\d+)\s*second/i);
        const minutesMatch = timeText.match(/(\d+)\s*minute/i);
        const hoursMatch = timeText.match(/(\d+)\s*hour/i);
        const daysMatch = timeText.match(/(\d+)\s*day/i);
        
        let total = 0;
        if (secondsMatch) total += parseInt(secondsMatch[1]) * 1000;
        if (minutesMatch) total += parseInt(minutesMatch[1]) * 60 * 1000;
        if (hoursMatch) total += parseInt(hoursMatch[1]) * 60 * 60 * 1000;
        if (daysMatch) total += parseInt(daysMatch[1]) * 24 * 60 * 60 * 1000;
        
        return total;
    }

    // 3. Helper: Format ms back into readable strings
    function formatElapsedTime(milliseconds, format = 'full') {
        const seconds = Math.floor(milliseconds / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (format === 'full') {
            // Short format for Urgent badges (e.g., "3d")
            if (days > 0) return `${days}d`;
            else if (hours > 0) return `${hours}h`;
            else if (minutes > 0) return `${minutes}m`;
            else return `${seconds}s`;
        } else {
            // Long format for Recent Activity (e.g., "3 days ago")
            if (days > 0) return days === 1 ? '1 day ago' : `${days} days ago`;
            else if (hours > 0) return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
            else if (minutes > 0) return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
            else return seconds <= 1 ? 'just now' : `${seconds} seconds ago`;
        }
    }

    // 4. Main Update Loop
    function updateTimers() {
        const now = Date.now();

        // Update Urgent Review Timers
        urgentTimers.forEach(timer => {
            const elapsed = now - timer.startTime;
            if(timer.element) timer.element.textContent = formatElapsedTime(elapsed, 'full');
        });

        // Update Recent Activity Timers
        recentTimers.forEach(timer => {
            const elapsed = now - timer.startTime;
            if(timer.element) timer.element.textContent = formatElapsedTime(elapsed, 'smart');
        });
    }

    function startTimerUpdates() {
        if (timerInterval) clearInterval(timerInterval);
        updateTimers(); // Immediate update
        timerInterval = setInterval(updateTimers, 1000); // Recurring update
    }

    // Cleanup on page exit
    window.addEventListener('beforeunload', function() {
        if (timerInterval) clearInterval(timerInterval);
    });

    // Run initialization
    initializeTimers();
});