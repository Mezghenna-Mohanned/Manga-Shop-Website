document.addEventListener("DOMContentLoaded", () => {
  const slides = document.querySelectorAll(".hero-slide");
  const dotsContainer = document.querySelector(".hero-dots");

  let currentSlide = 0;

  slides.forEach((_, idx) => {
    const dot = document.createElement("span");
    if (idx === 0) dot.classList.add("active");
    dotsContainer.appendChild(dot);
  });

  const dots = document.querySelectorAll(".hero-dots span");

  function showSlide(index) {
    slides.forEach((slide, idx) => {
      slide.classList.toggle("active", idx === index);
      dots[idx].classList.toggle("active", idx === index);
    });
    currentSlide = index;
  }
  function next() {
    const nextIndex = (currentSlide + 1) % slides.length;
    showSlide(nextIndex);
  }

  function prev() {
    const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
    showSlide(prevIndex);
  }

  setInterval(next, 5000);

  const prevSlide = document.querySelector(".hero-arrow.left");
  const nextSlide = document.querySelector(".hero-arrow.right");

  nextSlide.addEventListener("click", next);
  prevSlide.addEventListener("click", prev);
});

