// Your existing code...

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

// Your existing code...