document.addEventListener("DOMContentLoaded", () => {
  const track = document.querySelector(".carousel-track");
  const cards = document.querySelectorAll(".product-card");
  const prevBtn = document.querySelector(".carousel-arrow.left");
  const nextBtn = document.querySelector(".carousel-arrow.right");

  if (cards.length) {
    const cardWidth = cards[0].offsetWidth + 20;
    prevBtn.addEventListener("click", () =>
      track.scrollBy({ left: -cardWidth, behavior: "smooth" })
    );
    nextBtn.addEventListener("click", () =>
      track.scrollBy({ left: cardWidth, behavior: "smooth" })
    );
  }

  const header = document.querySelector(".sticky-header");
  window.addEventListener("scroll", () =>
    header.classList.toggle("shrink", window.scrollY > 80)
  );
});

const slides = document.querySelectorAll(".hero-slide");
const dotsContainer = document.querySelector(".hero-dots");
const prevSlide = document.querySelector(".hero-arrow.left");
const nextSlide = document.querySelector(".hero-arrow.right");

let currentSlide = 0;

// Create dots
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

nextSlide.addEventListener("click", next);
prevSlide
