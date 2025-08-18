/**
 * Glassmorphic Theme JavaScript
 * 
 * @package Glassmorphic
 * @version 1.0.0
 */

(function() {
    'use strict';
    
    // Theme settings from PHP
    const settings = window.glassmorphicSettings || {};
    
    // Dark mode handler
    class DarkModeManager {
        constructor() {
            this.html = document.documentElement;
            this.toggleButtons = document.querySelectorAll('[data-dark-toggle]');
            this.mode = settings.darkMode || 'auto';
            
            this.init();
        }
        
        init() {
            // Set initial mode
            if (this.mode === 'auto') {
                this.detectSystemPreference();
            } else if (this.mode === 'dark') {
                this.enableDarkMode();
            }
            
            // Add event listeners
            this.toggleButtons.forEach(button => {
                button.addEventListener('click', () => this.toggle());
            });
            
            // Listen for system preference changes
            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addListener(e => {
                    if (this.mode === 'auto') {
                        e.matches ? this.enableDarkMode() : this.disableDarkMode();
                    }
                });
            }
        }
        
        detectSystemPreference() {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                this.enableDarkMode();
            } else {
                this.disableDarkMode();
            }
        }
        
        enableDarkMode() {
            this.html.classList.add('dark');
            localStorage.setItem('darkMode', 'enabled');
        }
        
        disableDarkMode() {
            this.html.classList.remove('dark');
            localStorage.setItem('darkMode', 'disabled');
        }
        
        toggle() {
            if (this.html.classList.contains('dark')) {
                this.disableDarkMode();
            } else {
                this.enableDarkMode();
            }
        }
    }
    
    // Parallax effect for orbs
    class ParallaxOrbs {
        constructor() {
            this.orbs = document.querySelectorAll('.orb');
            this.init();
        }
        
        init() {
            if (this.orbs.length === 0) return;
            
            window.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        }
        
        handleMouseMove(e) {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            this.orbs.forEach((orb, index) => {
                const speed = (index + 1) * 20;
                const xPos = (x - 0.5) * speed;
                const yPos = (y - 0.5) * speed;
                
                orb.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        }
    }
    
    // Smooth scroll for anchor links
    class SmoothScroll {
        constructor() {
            this.links = document.querySelectorAll('a[href^="#"]');
            this.init();
        }
        
        init() {
            this.links.forEach(link => {
                link.addEventListener('click', (e) => this.handleClick(e));
            });
        }
        
        handleClick(e) {
            e.preventDefault();
            const targetId = e.currentTarget.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    }
    
    // Intersection Observer for animations
    class ScrollAnimations {
        constructor() {
            this.elements = document.querySelectorAll('[data-animate]');
            this.init();
        }
        
        init() {
            if (!('IntersectionObserver' in window)) return;
            
            const options = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        observer.unobserve(entry.target);
                    }
                });
            }, options);
            
            this.elements.forEach(element => {
                observer.observe(element);
            });
        }
    }
    
    // Dynamic gradient colors
    class GradientAnimator {
        constructor() {
            this.gradient = document.querySelector('.gradient-bg');
            this.hue = 0;
            this.animationId = null;
            
            if (settings.gradientAnimated !== false) {
                this.init();
            }
        }
        
        init() {
            if (!this.gradient) return;
            
            // Add dynamic color shift on scroll
            window.addEventListener('scroll', () => {
                const scrollPercent = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
                this.hue = scrollPercent * 360;
                this.updateGradient();
            });
        }
        
        updateGradient() {
            if (!this.gradient) return;
            
            const primary = `hsl(${this.hue}, 70%, 50%)`;
            const secondary = `hsl(${(this.hue + 120) % 360}, 70%, 50%)`;
            const accent = `hsl(${(this.hue + 240) % 360}, 70%, 50%)`;
            
            this.gradient.style.background = `linear-gradient(135deg, ${primary}, ${secondary}, ${accent}, ${primary})`;
        }
    }
    
    // Glass card tilt effect
    class CardTilt {
        constructor() {
            this.cards = document.querySelectorAll('.glass-card');
            this.init();
        }
        
        init() {
            this.cards.forEach(card => {
                card.addEventListener('mousemove', (e) => this.handleMouseMove(e, card));
                card.addEventListener('mouseleave', (e) => this.handleMouseLeave(e, card));
            });
        }
        
        handleMouseMove(e, card) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
        }
        
        handleMouseLeave(e, card) {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
        }
    }
    
    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize features
        new DarkModeManager();
        new ParallaxOrbs();
        new SmoothScroll();
        new ScrollAnimations();
        new GradientAnimator();
        new CardTilt();
        
        // Add loaded class for animations
        document.body.classList.add('loaded');
        
        // Header scroll effect (backup for non-Alpine implementation)
        const header = document.querySelector('.header');
        if (header && !window.Alpine) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    });
    
})();