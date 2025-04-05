document.addEventListener("DOMContentLoaded", function () {
  // Carousel functionality for product cards
  const track = document.querySelector(".carousel-track");
  const cards = document.querySelectorAll(".product-card");
  const prevBtn = document.querySelector(".carousel-arrow.left");
  const nextBtn = document.querySelector(".carousel-arrow.right");
  
  if (cards.length > 0) {
    const cardWidth = cards[0].offsetWidth + 20;
  
    prevBtn.addEventListener("click", () => {
      track.scrollBy({ left: -cardWidth, behavior: "smooth" });
    });
  
    nextBtn.addEventListener("click", () => {
      track.scrollBy({ left: cardWidth, behavior: "smooth" });
    });
  }

  // Slideshow functionality for banner images
  const slides = document.querySelectorAll(".banner-slideshow img");
  let currentSlide = 0;
  const slideInterval = 5000; // 5 seconds

  // Initialize slides: show first slide only
  slides.forEach((slide, index) => {
    if (index === 0) {
      slide.classList.add("active");
    } else {
      slide.classList.remove("active");
    }
  });

  function nextSlide() {
    slides[currentSlide].classList.remove("active");
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add("active");
  }

  setInterval(nextSlide, slideInterval);
});
