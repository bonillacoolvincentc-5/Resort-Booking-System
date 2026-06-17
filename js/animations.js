// YP4K Resort - Animation Scripts
// Enhanced interactive animations for the homepage

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false,
        offset: 100
    });

    // Animate counter numbers
    animateCounters();

    // Smooth scroll behavior
    setupSmoothScroll();

    // Parallax effect on scroll
    setupParallax();

    // Mouse follow effect
    setupMouseFollow();

    // Button ripple effect
    setupButtonRipples();

    // Navbar transparency on scroll
    setupNavbarScroll();

    // Navigation active state management
    setupNavigationActiveState();
});

// ============================================
// Animate Counter Numbers
// ============================================
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observerOptions = {
        threshold: 0.5
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // 2 seconds
                const startTime = Date.now();

                function updateCounter() {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const current = Math.floor(progress * target);
                    
                    counter.textContent = current.toLocaleString();

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    }
                }

                updateCounter();
                observer.unobserve(counter);
            }
        });
    }, observerOptions);

    counters.forEach(counter => observer.observe(counter));
}

// ============================================
// Smooth Scroll
// ============================================
function setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ============================================
// Parallax Effect
// ============================================
function setupParallax() {
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.hero-animation, .floating-image');

        parallaxElements.forEach(element => {
            const yPos = scrolled * 0.5;
            element.style.transform = `translateY(${yPos}px)`;
        });

        // Update navbar background opacity
        const navbar = document.querySelector('.nav-animated');
        if (navbar) {
            const opacity = Math.min(scrolled / 300, 0.95);
            navbar.style.backdropFilter = `blur(${opacity * 10}px)`;
        }
    });
}

// ============================================
// Mouse Follow on Hero Section
// ============================================
function setupMouseFollow() {
    const heroSection = document.querySelector('.hero-section-enhanced');
    
    if (heroSection) {
        document.addEventListener('mousemove', function(e) {
            const floatingElements = document.querySelectorAll('.floating-element');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            floatingElements.forEach((element, index) => {
                const moveX = (mouseX - 0.5) * (index + 1) * 50;
                const moveY = (mouseY - 0.5) * (index + 1) * 50;
                element.style.transform = `translate(${moveX}px, ${moveY}px)`;
            });
        });
    }
}

// ============================================
// Button Ripple Effect
// ============================================
function setupButtonRipples() {
    document.querySelectorAll('.btn-ripple').forEach(button => {
        button.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const ripple = document.createElement('span');
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// ============================================
// Navbar Scroll Effect
// ============================================
function setupNavbarScroll() {
    let lastScroll = 0;
    const navbar = document.querySelector('.nav-animated');

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });
}

// ============================================
// Text Typing Animation
// ============================================
function typeWriter(element, text, speed = 50) {
    let index = 0;
    element.textContent = '';

    function type() {
        if (index < text.length) {
            element.textContent += text.charAt(index);
            index++;
            setTimeout(type, speed);
        }
    }

    type();
}

// Apply typing animation to hero title on load
window.addEventListener('load', function() {
    const heroTitle = document.querySelector('.typing-animation');
    if (heroTitle) {
        const text = heroTitle.textContent;
        typeWriter(heroTitle, text, 30);
    }
});

// ============================================
// Intersection Observer for card interactions
// ============================================
function observeCards() {
    const cards = document.querySelectorAll('.card-interactive');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => observer.observe(card));
}

observeCards();

// ============================================
// Contact Card Hover Effects
// ============================================
document.querySelectorAll('.contact-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.05)';
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// ============================================
// Stat Card Animations
// ============================================
function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-animate');

    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animated');
        }, index * 200);
    });
}

window.addEventListener('load', animateStatCards);

// ============================================
// Image Hover Effects
// ============================================
document.querySelectorAll('.img-hover-zoom').forEach(img => {
    img.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1) rotate(1deg)';
    });

    img.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1) rotate(0deg)';
    });
});

// ============================================
// Floating Items Animation
// ============================================
function createFloatingParticles() {
    const container = document.querySelector('.animated-bg');
    if (!container) return;

    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'floating-particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 2 + 's';
        container.appendChild(particle);
    }
}

createFloatingParticles();

// ============================================
// Scroll Reveal for Elements
// ============================================
function revealOnScroll() {
    const reveals = document.querySelectorAll('.fade-in-delay-1, .fade-in-delay-2, .fade-in-delay-3');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    });

    reveals.forEach(reveal => observer.observe(reveal));
}

revealOnScroll();

// ============================================
// Scroll Indicator Animation
// ============================================
function setupScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    
    if (scrollIndicator) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 100) {
                scrollIndicator.style.opacity = '0';
                scrollIndicator.style.pointerEvents = 'none';
            } else {
                scrollIndicator.style.opacity = '1';
            }
        });
    }
}

setupScrollIndicator();

// ============================================
// Navigation Active State Management
// ============================================
function setupNavigationActiveState() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link[href^="#"]');
    const sections = document.querySelectorAll('section[id]');
    
    function updateActiveNav() {
        let current = '';
        
        // Get current scroll position
        const scrollY = window.pageYOffset;
        
        // Find which section is currently in view
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100; // Offset for navbar height
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                current = sectionId;
            }
        });
        
        // Remove active class from all nav links
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href').substring(1); // Remove #
            if (href === current) {
                link.classList.add('active');
            }
        });
    }
    
    // Update active state on scroll
    window.addEventListener('scroll', updateActiveNav);
    
    // Update active state on click
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all links
            navLinks.forEach(navLink => navLink.classList.remove('active'));
            // Add active class to clicked link
            this.classList.add('active');
        });
    });
    
    // Initial call to set active state
    updateActiveNav();
}

console.log('✨ YP4K Resort - All animations initialized!');
