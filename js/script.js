/* ───────── PRODUCT CAROUSEL ───────── */
document.addEventListener("DOMContentLoaded", () => {
  const track = document.querySelector(".carousel-track");
  const cards = document.querySelectorAll(".product-card");
  const prevBtn = document.querySelector(".carousel-arrow.left");
  const nextBtn = document.querySelector(".carousel-arrow.right");

  if (cards.length) {
    const cardWidth = cards[0].offsetWidth + 20; // card + gap

    prevBtn.addEventListener("click", () => {
      track.scrollBy({ left: -cardWidth, behavior: "smooth" });
    });
    nextBtn.addEventListener("click", () => {
      track.scrollBy({ left: cardWidth, behavior: "smooth" });
    });
  }

  /* ───────── STICKY HEADER SHRINK ───────── */
  const header = document.querySelector(".sticky-header");
  const shrinkPoint = 80; // px scrolled before shrink

  window.addEventListener("scroll", () => {
    if (window.scrollY > shrinkPoint) {
      header.classList.add("shrink");
    } else {
      header.classList.remove("shrink");
    }
  });

  /* ───────── SIMPLE BANNER SLIDESHOW ───────── */
  const slides = document.querySelectorAll(".banner-slideshow img");
  let current = 0;
  setInterval(() => {
    slides[current].classList.remove("active");
    current = (current + 1) % slides.length;
    slides[current].classList.add("active");
  }, 5000);
});
