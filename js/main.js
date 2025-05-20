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



function searchProducts(query, category = '') {
  if (query.length < 2 && !category) {
    autocompleteResults.style.display = 'none';
    return;
  }
  let url = `z_index.php?search=${encodeURIComponent(query)}`;
  if (category) {
    url += `&category=${encodeURIComponent(category)}`;
  }

  fetch(url)
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
  const category = document.getElementById('category-filter').value;
  timeoutId = setTimeout(() => {
    searchProducts(this.value.trim(), category);
  }, 300);
});

searchInput.addEventListener('focus', function() {
  if ((this.value.trim().length >= 2 || document.getElementById('category-filter').value) && 
      autocompleteResults.innerHTML) {
    autocompleteResults.style.display = 'block';
  }
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('.search-container')) {
    autocompleteResults.style.display = 'none';
  }
});

document.getElementById('category-filter').addEventListener('change', function() {
  searchProducts(searchInput.value.trim(), this.value);
});

searchInput.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    const query = searchInput.value.trim();
    const category = document.getElementById('category-filter').value;
    if (query || category) {
      window.location.href = `search.php?q=${encodeURIComponent(query)}${category ? `&category=${encodeURIComponent(category)}` : ''}`;
    }
  }
});

function getSelectedCategory() {
  return document.getElementById('category-filter').value;
}

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
    
    setInterval(nextSlide, 5000);
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
    
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
    
    function setupProductModal() {
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeProductModal();
                }
            });
        }
        
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
        
        setupProductImageListeners();
    }
    
    setupProductModal();
});

function scrollCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    const cardWidth = carousel.querySelector('.product-card')?.offsetWidth || 220;
    const gap = 20;
    const scrollAmount = (cardWidth + gap) * 3 * direction;
    
    carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}

function openProductModal(product) {
  const modal = document.getElementById('productModal');
  if (!modal) return;

  const banner = document.getElementById('productModalBanner');
  banner.style.backgroundImage = `url(${product.image_url})`;

  const img = document.getElementById('productModalImage');
  const title = document.getElementById('productModalTitle');
  const price = document.getElementById('productModalPrice');
  const productId = document.getElementById('productModalId');

  img.src = product.image_url;
  img.alt = product.name;
  title.textContent = product.name;
  price.textContent = product.price + ' DA';
  productId.value = product.product_id;

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeProductModal() {
  const modal = document.getElementById('productModal');
  if (!modal) return;

  modal.classList.remove('active');
  document.body.style.overflow = 'auto';
}

function setupProductImageListeners() {
  document.querySelectorAll('.product-image').forEach(img => {
    img.removeEventListener('click', handleProductImageClick);
    img.addEventListener('click', handleProductImageClick);
  });
}

function handleProductImageClick(e) {
  e.preventDefault();

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

document.addEventListener('click', function(e) {
  const modal = document.getElementById('productModal');
  if (!modal) return;

  if (e.target === modal) {
    closeProductModal();
  }

  if (e.target.id === 'modalCloseBtn') {
    closeProductModal();
  }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeProductModal();
  }
});

const modalForm = document.getElementById('productModalForm');
if (modalForm) {
  modalForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('add_to_cart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Produit ajouté au panier ✅');
        closeProductModal();
      } else {
        alert('Erreur lors de l\'ajout au panier');
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      alert('Une erreur est survenue');
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setupProductImageListeners();
});

document.addEventListener('DOMContentLoaded', () => {
  const cartButton = document.getElementById('cart-button');
  const cartPopup = document.getElementById('cart-popup');
  const cartItemsContainer = document.getElementById('cart-items');
  const cartTotalContainer = document.getElementById('cart-total');

  function formatPrice(price) {
    return price.toLocaleString('fr-FR', { style: 'currency', currency: 'DZD' });
  }

  function loadCart() {
    fetch('cart_api.php')
      .then(response => response.json())
      .then(data => {
        cartItemsContainer.innerHTML = '';
        let total = 0;

        if (data.length === 0) {
          cartItemsContainer.innerHTML = '<p style="padding:10px;">Votre panier est vide.</p>';
          cartTotalContainer.textContent = '';
          return;
        }

        data.forEach(item => {
          const itemTotal = item.price * item.quantity;
          total += itemTotal;

          const div = document.createElement('div');
          div.className = 'cart-item';
          div.innerHTML = `
            <img src="${item.image_url}" alt="${item.name}">
            <div class="item-info">
              <div class="item-name">${item.name}</div>
              <div class="item-qty">Quantité: ${item.quantity}</div>
            </div>
            <div class="item-price">${formatPrice(itemTotal)}</div>
          `;
          cartItemsContainer.appendChild(div);
        });

        cartTotalContainer.textContent = 'Total : ' + formatPrice(total);
      })
      .catch(err => {
        cartItemsContainer.innerHTML = '<p style="padding:10px;">Erreur lors du chargement du panier.</p>';
        cartTotalContainer.textContent = '';
        console.error(err);
      });
  }

  cartButton.addEventListener('click', (e) => {
    e.preventDefault();
    if (cartPopup.style.display === 'none' || !cartPopup.style.display) {
      loadCart();
      cartPopup.style.display = 'block';
    } else {
      cartPopup.style.display = 'none';
    }
  });

  document.addEventListener('click', (e) => {
    if (!cartPopup.contains(e.target) && e.target !== cartButton) {
      cartPopup.style.display = 'none';
    }
  });
});

document.getElementById('finalize-order-btn').addEventListener('click', () => {
  window.location.href = 'finalize_order.php';
});


document.addEventListener('DOMContentLoaded', function() {
  const cookieBanner = document.getElementById('cookie-consent-banner');
  const acceptBtn = document.getElementById('accept-cookies');
  const declineBtn = document.getElementById('decline-cookies');

  if (cookieBanner && !document.cookie.includes('cookie_consent')) {
    setTimeout(() => {
      cookieBanner.classList.add('show');
    }, 1000);

    acceptBtn.addEventListener('click', function() {
      setCookieConsent('accept');
      cookieBanner.classList.remove('show');
    });

    declineBtn.addEventListener('click', function() {
      setCookieConsent('decline');
      cookieBanner.classList.remove('show');
    });
  }

  function setCookieConsent(action) {
    fetch('z_index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `cookie_consent=${action}`
    });
  }

});


document.querySelectorAll('.nav-menu a[href="history.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        return true;
    });
});