// Collapsible navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all section headers
    const sectionHeaders = document.querySelectorAll('.nav-section h3');
    
    // Add click event to each header
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle collapsed class with animation
            const isCollapsing = !this.classList.contains('collapsed');
            this.classList.toggle('collapsed');
            
            // Get the ul element
            const ul = this.nextElementSibling;
            
            // Smooth height animation
            if (ul && ul.tagName === 'UL') {
                if (isCollapsing) {
                    // Collapsing
                    ul.style.maxHeight = ul.scrollHeight + 'px';
                    setTimeout(() => {
                        ul.style.maxHeight = '0';
                    }, 10);
                } else {
                    // Expanding
                    ul.style.maxHeight = ul.scrollHeight + 'px';
                    setTimeout(() => {
                        ul.style.maxHeight = '500px';
                    }, 300);
                }
            }
            
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
            // Also set initial maxHeight for collapsed sections
            const ul = header.nextElementSibling;
            if (ul && ul.tagName === 'UL') {
                ul.style.maxHeight = '0';
            }
        }
    });
    
    // Add keyboard support (Enter/Space to toggle)
    sectionHeaders.forEach(header => {
        header.setAttribute('tabindex', '0');
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});