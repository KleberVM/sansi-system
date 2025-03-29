document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.competition-areas');
    const slides = document.querySelectorAll('.carousel-slide');
    const indicatorsContainer = document.querySelector('.carousel-indicators');
    let currentSlide = 0;
    const slideCount = slides.length;
    let autoplayInterval;
    const visibleSlides = window.innerWidth > 992 ? 3 : window.innerWidth > 768 ? 2 : 1;
    
    // Clonar los primeros slides para el efecto infinito
    const slidesToClone = Math.min(visibleSlides, slideCount);
    for (let i = 0; i < slidesToClone; i++) {
        const clone = slides[i].cloneNode(true);
        carousel.appendChild(clone);
    }
    
    // Crear indicadores
    for (let i = 0; i < slideCount; i++) {
        const indicator = document.createElement('div');
        indicator.classList.add('indicator');
        if (i === 0) indicator.classList.add('active');
        indicatorsContainer.appendChild(indicator);
        
        // Añadir click event a los indicadores
        indicator.addEventListener('click', () => {
            goToSlide(i);
            startAutoplay();
        });
    }
    
    const indicators = document.querySelectorAll('.indicator');
    
    function updateCarousel(direction = 'next') {
        const slideWidth = 100 / visibleSlides;
        currentSlide = direction === 'next' ? 
            (currentSlide + 1) % slideCount : 
            (currentSlide - 1 + slideCount) % slideCount;
            
        const offset = -(currentSlide * slideWidth);
        carousel.style.transform = `translateX(${offset}%)`;
        
        // Actualizar indicadores
        indicators.forEach((ind, index) => {
            ind.classList.toggle('active', index === currentSlide);
        });
        
        // Actualizar clases activas
        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentSlide);
        });
    }
    
    function goToSlide(index) {
        currentSlide = index;
        const slideWidth = 100 / visibleSlides;
        const offset = -(currentSlide * slideWidth);
        carousel.style.transform = `translateX(${offset}%)`;
        
        indicators.forEach((ind, i) => {
            ind.classList.toggle('active', i === currentSlide);
        });
    }
    
    function startAutoplay() {
        stopAutoplay();
        autoplayInterval = setInterval(() => updateCarousel('next'), 3000);
    }
    
    function stopAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
    }
    
    // Event Listeners
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    
    // Responsive handling
    window.addEventListener('resize', () => {
        const newVisibleSlides = window.innerWidth > 992 ? 3 : window.innerWidth > 768 ? 2 : 1;
        if (newVisibleSlides !== visibleSlides) {
            location.reload();
        }
    });
    
    // Iniciar autoplay
    startAutoplay();
});