// Toggle test results details
function toggleTestResults() {
    const details = document.getElementById('testResultsDetails');
    const chevron = document.getElementById('chevron');
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        details.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Animate progress bars on load
window.addEventListener('load', function() {
    const progressBars = document.querySelectorAll('.result-progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});

// Toggle feedback sections
function toggleFeedbackSection(section) {
    const content = document.getElementById(`feedback-${section}`);
    const chevron = content.previousElementSibling.querySelector('.chevron-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        content.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle plagiarism details
function togglePlagiarismDetails() {
    const details = document.getElementById('plagiarismDetails');
    const chevron = document.getElementById('chevronPlagiarism');
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        details.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}

// Toggle accordion sections
function toggleAccordion(accordionId) {
    const content = document.getElementById(accordionId);
    const icon = document.getElementById(accordionId + '-icon');
    
    if (content.style.maxHeight === '0px' || content.style.maxHeight === '') {
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.maxHeight = '0px';
        icon.style.transform = 'rotate(0deg)';
    }
}