document.addEventListener('DOMContentLoaded', function() {
const header = document.querySelector('.header');
const topBanner = document.querySelector('.top-banner');
const logo = document.querySelector('.nav-logo-container');
const hamburger = document.querySelector('.hamburger');
const mobileMenu = document.querySelector('.mobile-menu');
const mobileOverlay = document.querySelector('.mobile-overlay');
const mobileLinks = document.querySelectorAll('.mobile-menu .nav-link');

// Header scroll effect
window.addEventListener('scroll', () => {
    const threshold = topBanner ? topBanner.offsetHeight : 40;
    
    if (window.scrollY > threshold) {
        header.classList.add('scrolled');
        logo.classList.add('scrolled-logo');
    } else {
        header.classList.remove('scrolled');
        logo.classList.remove('scrolled-logo');
    }
}, { passive: true });

// Toggle mobile menu
function toggleMenu() {
    hamburger.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    mobileOverlay.classList.toggle('active');
    
    // Prevent body scroll when menu is open
    if (mobileMenu.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Hamburger click
hamburger.addEventListener('click', toggleMenu);

// Overlay click to close
mobileOverlay.addEventListener('click', toggleMenu);

// Close menu when link clicked
mobileLinks.forEach(link => {
    link.addEventListener('click', () => {
        toggleMenu();
    });
});

// Add to cart button effect
const addToCartButtons = document.querySelectorAll('.add-to-cart');
addToCartButtons.forEach(button => {
    button.addEventListener('click', function() {
        const originalText = this.textContent;
        this.textContent = 'Ditambahkan! ✓';
        this.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
        
        setTimeout(() => {
            this.textContent = originalText;
            this.style.background = '';
        }, 2000);
    });
});
});

window.addEventListener("resize", () => {
if (window.innerWidth > 992) {
    document.body.style.overflow = "";
    document.querySelector(".mobile-menu").classList.remove("active");
    document.querySelector(".mobile-overlay").classList.remove("active");
}
});


