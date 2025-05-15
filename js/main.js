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
    
    // Show modal with animation
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Animate content
    const content = modal.querySelector('.product-modal-content');
    content.style.transform = 'translateY(0)';
    content.style.opacity = '1';
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    if (!modal) return;
    
    // Animate out
    const content = modal.querySelector('.product-modal-content');
    content.style.transform = 'translateY(20px)';
    content.style.opacity = '0';
    
    // Remove modal after animation
    setTimeout(() => {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }, 300);
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

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    setupProductImageListeners();
    
    // Handle modal form submission
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
                // Show success toast
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
    
    // Observe DOM changes for dynamic content
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setTimeout(setupProductImageListeners, 100);
            }
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});