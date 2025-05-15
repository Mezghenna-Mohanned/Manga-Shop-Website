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

document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetSection = document.getElementById(targetId);
        
        if (targetSection) {
            window.scrollTo({
                top: targetSection.offsetTop - 100,
                behavior: 'smooth'
            });
            document.querySelectorAll('.nav-menu a').forEach(item => {
                item.style.color = '';
            });
            this.style.color = '#f47521';
        }
    });
});

function scrollCarousel(categoryId, direction) {
    const carousel = document.getElementById(`${categoryId}-carousel`);
    const scrollAmount = 300;
    carousel.scrollBy({
        left: scrollAmount * direction,
        behavior: 'smooth'
    });
}

// Hero Slider functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dots span');
    
    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[n].classList.add('active');
        dots[n].classList.add('active');
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Auto-advance slides
    setInterval(nextSlide, 5000);
    
    // Click on dots to change slides
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
    
    // Carousel functionality
    function initCarousels() {
        const carousels = document.querySelectorAll('.carousel-track');
        
        carousels.forEach(carousel => {
            const id = carousel.id;
            const leftArrow = carousel.parentElement.querySelector('.carousel-arrow.left');
            const rightArrow = carousel.parentElement.querySelector('.carousel-arrow.right');
            
            if (leftArrow) {
                leftArrow.addEventListener('click', () => scrollCarousel(id, -1));
            }
            
            if (rightArrow) {
                rightArrow.addEventListener('click', () => scrollCarousel(id, 1));
            }
        });
    }
    
    initCarousels();
    
    // Product modal functionality
    function setupProductModal() {
        // Close modal when clicking outside content
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeProductModal();
                }
            });
        }
        
        // Setup form submission
        const modalForm = document.getElementById('productModalForm');
        if (modalForm) {
            modalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    closeProductModal();
                    const toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.textContent = 'Produit ajouté au panier ✅';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue');
                });
            });
        }
        
        // Add click events to all product images (including dynamically loaded ones)
        setupProductImageListeners();
    }
    
    setupProductModal();
});

// Function to scroll carousel
function scrollCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const cardWidth = carousel.querySelector('.product-card')?.offsetWidth || 220;
    const gap = 20; // Gap between cards
    const scrollAmount = (cardWidth + gap) * 3 * direction;
    
    carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}

// Product modal functions
function openProductModal(product) {
    const modal = document.getElementById('productModal');
    if (!modal) return;
    
    const banner = document.getElementById('productModalBanner');
    const img = document.getElementById('productModalImage');
    const title = document.getElementById('productModalTitle');
    const price = document.getElementById('productModalPrice');
    const productId = document.getElementById('productModalId');
    
    img.src = product.image_url;
    img.alt = product.name;
    title.textContent = product.name;
    price.textContent = product.price + ' DA';
    productId.value = product.product_id;
    
    // Set banner background
    banner.style.backgroundImage = `url(${product.image_url})`;
    
    // Show modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }
}

function handleProductImageClick(e) {
    e.preventDefault(); // Prevent default behavior
    
    const productCard = this.closest('.product-card');
    if (!productCard) return;
    
    const productIdInput = productCard.querySelector('input[name="product_id"]');
    const nameElement = productCard.querySelector('.product-name');
    const priceElement = productCard.querySelector('.product-price');
    
    if (!productIdInput || !nameElement || !priceElement) return;
    
    const product = {
        product_id: productIdInput.value,
        name: nameElement.textContent,
        price: priceElement.textContent.trim().replace(' DA', ''),
        image_url: this.src
    };
    
    openProductModal(product);
}

// Close modal when clicking outside content
document.addEventListener('click', function(e) {
    const modal = document.getElementById('productModal');
    if (e.target === modal) {
        closeProductModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProductModal();
    }
});

function setupProductImageListeners() {
    document.querySelectorAll('.product-image').forEach(img => {
        // Remove existing listeners to avoid duplicates
        img.removeEventListener('click', handleProductImageClick);
        // Add new listener
        img.addEventListener('click', handleProductImageClick);
    });
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const resultsContainer = document.getElementById('autocomplete-results');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.trim();
            
            if (term.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            fetch(`?search=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultsContainer.style.display = 'none';
                        return;
                    }
                    
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-item';
                        div.innerHTML = `
                            <img src="${item.image_url}" alt="${item.name}">
                            <div>${item.name}</div>
                        `;
                        
                        div.addEventListener('click', function() {
                            window.location.href = `product.php?id=${item.product_id}`;
                        });
                        
                        resultsContainer.appendChild(div);
                    });
                    
                    resultsContainer.style.display = 'block';
                })
                .catch(error => console.error('Search error:', error));
        });
        
        // Close autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }
    
    // Initialize product image listeners again after a delay to catch all dynamic content
    setTimeout(setupProductImageListeners, 1000);
    
    // Reattach listeners when DOM changes (for dynamically loaded content)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setTimeout(setupProductImageListeners, 100);
            }
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});
