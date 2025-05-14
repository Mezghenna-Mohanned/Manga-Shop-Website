const heroSlides = document.querySelectorAll('.hero-slide');
const heroDots = document.querySelectorAll('.hero-dots span');
let currentHeroSlide = 0;
let heroInterval;

function showHeroSlide(index) {
  heroSlides.forEach((slide, i) => {
    slide.classList.toggle('active', i === index);
    heroDots[i].classList.toggle('active', i === index);
  });
  currentHeroSlide = index;
}

function nextHeroSlide() {
  currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
  showHeroSlide(currentHeroSlide);
}

function startHeroSlider() {
  heroInterval = setInterval(nextHeroSlide, 5000);
}

heroDots.forEach((dot, index) => {
  dot.addEventListener('click', () => {
    clearInterval(heroInterval);
    showHeroSlide(index);
    startHeroSlider();
  });
});

function initCarousel(carouselId) {
  const track = document.getElementById(carouselId);
  const wrapper = track.parentElement;
  const leftBtn = wrapper.querySelector('.carousel-arrow.left');
  const rightBtn = wrapper.querySelector('.carousel-arrow.right');
  
  let position = 0;
  const cardWidth = 220;
  const gap = 20;
  const scrollAmount = (cardWidth + gap) * 3;
  const maxPosition = (track.scrollWidth - wrapper.offsetWidth) * -1;
  
  function updateButtons() {
    leftBtn.classList.toggle('disabled', position >= 0);
    rightBtn.classList.toggle('disabled', position <= maxPosition);
  }
  
  function moveCarousel(amount) {
    position = Math.max(maxPosition, Math.min(0, position + amount));
    track.style.transform = `translateX(${position}px)`;
    updateButtons();
  }
  
  leftBtn.addEventListener('click', () => moveCarousel(scrollAmount));
  rightBtn.addEventListener('click', () => moveCarousel(-scrollAmount));
  
  let startX, moveX;
  track.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
  });
  
  track.addEventListener('touchmove', (e) => {
    moveX = e.touches[0].clientX;
    const diff = startX - moveX;
    track.style.transform = `translateX(${position - diff}px)`;
  });
  
  track.addEventListener('touchend', (e) => {
    const diff = startX - moveX;
    if (Math.abs(diff) > 50) {
      moveCarousel(diff > 0 ? -scrollAmount/3 : scrollAmount/3);
    } else {
      track.style.transform = `translateX(${position}px)`;
    }
  });
  
  updateButtons();
}

const searchInput = document.getElementById('search-input');
const autocompleteResults = document.getElementById('autocomplete-results');
let timeoutId;

function searchProducts(query) {
  if (query.length < 2) {
    autocompleteResults.style.display = 'none';
    return;
  }
  
  fetch(`z_index.php?search=${encodeURIComponent(query)}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      if (data && data.length > 0) {
        autocompleteResults.innerHTML = '';
        data.forEach(item => {
          const div = document.createElement('div');
          div.className = 'autocomplete-item';
          div.innerHTML = `
            <img src="${item.image_url}" alt="${item.name}">
            <div>${item.name.replace(
              new RegExp(query, 'gi'), 
              match => `<strong>${match}</strong>`
            )}</div>
          `;
          div.addEventListener('click', () => {
            window.location.href = `product.php?id=${item.product_id}`;
          });
          autocompleteResults.appendChild(div);
        });
        autocompleteResults.style.display = 'block';
      } else {
        autocompleteResults.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error fetching search results:', error);
      autocompleteResults.style.display = 'none';
    });
}

searchInput.addEventListener('input', function() {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    searchProducts(this.value.trim());
  }, 300);
});

searchInput.addEventListener('focus', function() {
  if (this.value.trim().length >= 2 && autocompleteResults.innerHTML) {
    autocompleteResults.style.display = 'block';
  }
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('.search-container')) {
    autocompleteResults.style.display = 'none';
  }
});

searchInput.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    const query = searchInput.value.trim();
    if (query) {
      window.location.href = `search.php?q=${encodeURIComponent(query)}`;
    }
  }
});

searchInput.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    autocompleteResults.style.display = 'none';
  }
});

document.addEventListener('DOMContentLoaded', function() {
  startHeroSlider();
  initCarousel('discount-carousel');
  initCarousel('new-carousel');
  
  const toast = document.querySelector('.toast');
  if (toast) {
    setTimeout(() => toast.remove(), 3000);
  }
});