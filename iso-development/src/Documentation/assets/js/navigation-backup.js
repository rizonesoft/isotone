// Collapsible navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all section headers
    const sectionHeaders = document.querySelectorAll('.nav-section h3');
    
    // Add click event to each header
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Toggle collapsed class
            this.classList.toggle('collapsed');
            
            // Save state to localStorage
            const sectionText = this.textContent.trim();
            const isCollapsed = this.classList.contains('collapsed');
            
            // Get existing state or create new
            let navState = JSON.parse(localStorage.getItem('navState') || '{}');
            navState[sectionText] = isCollapsed;
            localStorage.setItem('navState', JSON.stringify(navState));
        });
    });
    
    // Restore collapsed state from localStorage
    const navState = JSON.parse(localStorage.getItem('navState') || '{}');
    sectionHeaders.forEach(header => {
        const sectionText = header.textContent.trim();
        if (navState[sectionText]) {
            header.classList.add('collapsed');
        }
    });
});