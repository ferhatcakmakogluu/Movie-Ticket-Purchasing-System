let slideIndex = 1;
let slideInterval;

// Initialize slider when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    showSlides(slideIndex);
    startAutoSlide();
});

function moveSlide(n) {
    showSlides(slideIndex += n);
    resetAutoSlide();
}

function currentSlide(n) {
    showSlides(slideIndex = n);
    resetAutoSlide();
}

function showSlides(n) {
    const slides = document.getElementsByClassName("slide");
    const dots = document.getElementsByClassName("dot");
    
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
    }
    
    // Remove active state from all dots
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Show current slide and activate corresponding dot
    slides[slideIndex-1].classList.add("active");
    dots[slideIndex-1].classList.add("active");
}

function startAutoSlide() {
    slideInterval = setInterval(() => {
        moveSlide(1);
    }, 5000); // Change slide every 5 seconds
}

function resetAutoSlide() {
    clearInterval(slideInterval);
    startAutoSlide();
}

// Pause auto-slide when hovering over slider
document.querySelector('.movie-slider-container').addEventListener('mouseenter', () => {
    clearInterval(slideInterval);
});

// Resume auto-slide when mouse leaves slider
document.querySelector('.movie-slider-container').addEventListener('mouseleave', () => {
    startAutoSlide();
}); 