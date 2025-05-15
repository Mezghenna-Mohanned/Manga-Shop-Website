<!-- Add this to your existing styles section -->
<style>
/* Product Modal Styles */
.product-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.95);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(8px);
}

.product-modal.active {
    display: flex;
}

.product-modal-content {
    background: var(--bg-card);
    border-radius: 20px;
    max-width: 500px;
    width: 100%;
    overflow: hidden;
    position: relative;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s ease-out;
}

.product-modal.active .product-modal-content {
    transform: translateY(0);
    opacity: 1;
}

.product-modal-banner {
    width: 100%;
    height: 180px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
}

.product-modal-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.8));
}

.product-modal-image {
    width: 140px;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
    position: absolute;
    top: 80px;
    left: 30px;
    border: 4px solid var(--bg-card);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    transition: transform 0.3s ease;
}

.product-modal-image:hover {
    transform: scale(1.05);
}

.product-modal-body {
    padding: 120px 30px 30px;
}

.product-modal-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    background: linear-gradient(45deg, var(--accent), #ff9f5a);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.product-modal-price {
    font-size: 2rem;
    color: var(--accent);
    margin-bottom: 25px;
    font-weight: 700;
}

.product-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.1);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    backdrop-filter: blur(4px);
}

.product-modal-close:hover {
    background: var(--accent);
    transform: rotate(90deg);
}

.product-modal button[type="submit"] {
    background: var(--accent);
    color: #000;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    padding: 16px 24px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.product-modal button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(244, 117, 33, 0.3);
}

.product-modal button[type="submit"] i {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .product-modal-content {
        max-width: 90%;
    }

    .product-modal-image {
        width: 120px;
        height: 170px;
        left: 50%;
        transform: translateX(-50%);
    }

    .product-modal-body {
        text-align: center;
        padding-top: 100px;
    }

    .product-modal-title {
        font-size: 1.5rem;
    }

    .product-modal-price {
        font-size: 1.6rem;
    }
}
</style>

<!-- Add this before closing body tag -->
<div id="productModal" class="product-modal">
    <div class="product-modal-content">
        <button class="product-modal-close" onclick="closeProductModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="product-modal-banner" id="productModalBanner">
            <!-- Banner will be set via JavaScript -->
        </div>
        
        <img src="" alt="Product Image" class="product-modal-image" id="productModalImage">
        
        <div class="product-modal-body">
            <h2 class="product-modal-title" id="productModalTitle"></h2>
            <p class="product-modal-price" id="productModalPrice"></p>
            
            <form method="post" id="productModalForm">
                <input type="hidden" name="product_id" id="productModalId">
                <button type="submit">
                    <i class="fas fa-cart-plus"></i>
                    Ajouter au panier
                </button>
            </form>
        </div>
    </div>
</div>