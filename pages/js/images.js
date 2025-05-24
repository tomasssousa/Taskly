function moveCarousel(gigId, direction) {
    const carousel = document.querySelector(`.carousel[data-gig-id="${gigId}"]`);
    const images = carousel.querySelectorAll('.gig-image');
    const dots = carousel.querySelectorAll('.dot');
    let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
    images[currentIndex].classList.remove('active');
    dots[currentIndex].classList.remove('active');
    currentIndex = (currentIndex + direction + images.length) % images.length;
    images[currentIndex].classList.add('active');
    dots[currentIndex].classList.add('active');
}

function setCarouselImage(gigId, index) {
    const carousel = document.querySelector(`.carousel[data-gig-id="${gigId}"]`);
    const images = carousel.querySelectorAll('.gig-image');
    const dots = carousel.querySelectorAll('.dot');
    images.forEach(img => img.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    images[index].classList.add('active');
    dots[index].classList.add('active');
}