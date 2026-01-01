// Toggle User Dropdown Menu
function toggleUserMenu(event) {
    event.stopPropagation();
    const userDropdown = document.getElementById('userDropdown');
    userDropdown.classList.toggle('show');
}

// Close User Dropdown Menu when clicking outside
window.onclick = function(event) {
    const userDropdown = document.getElementById('userDropdown');
    if (!event.target.matches('.user-avatar')) {
        if (userDropdown.classList.contains('show')) {
            userDropdown.classList.remove('show');
        }
    }
}