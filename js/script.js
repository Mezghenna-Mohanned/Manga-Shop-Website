document.addEventListener("DOMContentLoaded", function () {
    const track = document.querySelector(".carousel-track");
    const cards = document.querySelectorAll(".product-card");
    const prevBtn = document.querySelector(".carousel-arrow.left");
    const nextBtn = document.querySelector(".carousel-arrow.right");
  
    const cardWidth = cards[0].offsetWidth + 20;
  
    prevBtn.addEventListener("click", () => {
      track.scrollBy({ left: -cardWidth, behavior: "smooth" });
    });
  
    nextBtn.addEventListener("click", () => {
      track.scrollBy({ left: cardWidth, behavior: "smooth" });
    });
  });
  