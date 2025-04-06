document.addEventListener("DOMContentLoaded", () => {
  const track   = document.querySelector(".carousel-track");
  const cards   = document.querySelectorAll(".product-card");
  const prevBtn = document.querySelector(".carousel-arrow.left");
  const nextBtn = document.querySelector(".carousel-arrow.right");

  if (cards.length) {
    const cardWidth = cards[0].offsetWidth + 20;
    prevBtn.addEventListener("click", () =>
      track.scrollBy({ left: -cardWidth, behavior: "smooth" }));
    nextBtn.addEventListener("click", () =>
      track.scrollBy({ left:  cardWidth, behavior: "smooth" }));
  }

  const header = document.querySelector(".sticky-header");
  window.addEventListener("scroll", () =>
    header.classList.toggle("shrink", window.scrollY > 80));
});
