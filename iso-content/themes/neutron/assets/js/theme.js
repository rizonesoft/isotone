/**
 * Neutron Theme - JavaScript
 * 
 * Theme functionality and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Dark mode management
    const darkModeToggle = () => {
        const html = document.documentElement;
        const currentMode = html.classList.contains('dark') ? 'light' : 'dark';
        
        // Toggle class
        html.classList.toggle('dark');
        
        // Save preference
        localStorage.setItem('theme-mode', currentMode);
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('theme-mode-changed', { 
            detail: { mode: currentMode } 
        }));
    };
    
    // Initialize dark mode from localStorage or system preference
    const initDarkMode = () => {
        const savedMode = localStorage.getItem('theme-mode');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const themeMode = document.documentElement.dataset.themeMode;
        
        if (savedMode === 'dark' || (!savedMode && systemPrefersDark && themeMode === 'auto')) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };
    
    // Smooth scroll for anchor links
    const smoothScroll = () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    };
    
    // Search functionality
    const initSearch = () => {
        const searchToggle = document.querySelector('[data-search-toggle]');
        const searchModal = document.querySelector('[data-search-modal]');
        const searchInput = document.querySelector('[data-search-input]');
        const searchClose = document.querySelector('[data-search-close]');
        
        if (searchToggle && searchModal) {
            searchToggle.addEventListener('click', () => {
                searchModal.classList.remove('hidden');
                searchInput?.focus();
            });
            
            searchClose?.addEventListener('click', () => {
                searchModal.classList.add('hidden');
            });
            
            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !searchModal.classList.contains('hidden')) {
                    searchModal.classList.add('hidden');
                }
            });
        }
    };
    
    // Mobile menu enhancements
    const initMobileMenu = () => {
        const mobileMenuButton = document.querySelector('[data-mobile-menu-toggle]');
        const mobileMenu = document.querySelector('[data-mobile-menu]');
        
        if (mobileMenuButton && mobileMenu) {
            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    };
    
    // Lazy loading images
    const lazyLoadImages = () => {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.add('loaded');
            });
        }
    };
    
    // Back to top button
    const initBackToTop = () => {
        const button = document.querySelector('[data-back-to-top]');
        
        if (button) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 500) {
                    button.classList.remove('hidden');
                } else {
                    button.classList.add('hidden');
                }
            });
            
            button.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    };
    
    // Copy code blocks
    const initCodeCopy = () => {
        document.querySelectorAll('pre code').forEach(block => {
            const button = document.createElement('button');
            button.className = 'copy-code-btn';
            button.textContent = 'Copy';
            
            button.addEventListener('click', () => {
                navigator.clipboard.writeText(block.textContent).then(() => {
                    button.textContent = 'Copied!';
                    setTimeout(() => {
                        button.textContent = 'Copy';
                    }, 2000);
                });
            });
            
            const pre = block.parentElement;
            pre.style.position = 'relative';
            pre.appendChild(button);
        });
    };
    
    // Initialize all functions
    initDarkMode();
    smoothScroll();
    initSearch();
    initMobileMenu();
    lazyLoadImages();
    initBackToTop();
    initCodeCopy();
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme-mode')) {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
});

// Alpine.js components (if Alpine is loaded)
if (window.Alpine) {
    // Register Alpine components here
    document.addEventListener('alpine:init', () => {
        Alpine.data('themeSearch', () => ({
            query: '',
            results: [],
            loading: false,
            
            async search() {
                if (this.query.length < 3) {
                    this.results = [];
                    return;
                }
                
                this.loading = true;
                
                // Simulate API call - replace with actual search endpoint
                setTimeout(() => {
                    this.results = [
                        { title: 'Sample Result 1', url: '/page1' },
                        { title: 'Sample Result 2', url: '/page2' },
                    ];
                    this.loading = false;
                }, 500);
            }
        }));
    });
}