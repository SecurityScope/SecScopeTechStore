// SecScope Tech Store - JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Cart quantity update functionality
    const quantityInputs = document.querySelectorAll('input[name^="quantities"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.closest('form').querySelector('button[name="update_quantity"]').click();
        });
    });

    // Product search autocomplete
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value;
            if (searchTerm.length > 2) {
                fetchSearchSuggestions(searchTerm);
            }
        }, 300));
    }

    // Product image zoom functionality
    const productImages = document.querySelectorAll('.product-card img');
    productImages.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Price range slider functionality
    const priceRangeSlider = document.getElementById('priceRange');
    if (priceRangeSlider) {
        noUiSlider.create(priceRangeSlider, {
            start: [0, 1000],
            connect: true,
            range: {
                'min': 0,
                'max': 1000
            },
            step: 10
        });

        const priceMin = document.getElementById('priceMin');
        const priceMax = document.getElementById('priceMax');
        
        priceRangeSlider.noUiSlider.on('update', function(values, handle) {
            const value = Math.round(values[handle]);
            if (handle === 0) {
                priceMin.value = value;
            } else {
                priceMax.value = value;
            }
        });
    }

    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('button[name="add_to_cart"]');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const productCard = this.closest('.product-card');
            if (productCard) {
                productCard.style.boxShadow = '0 0 15px rgba(255, 215, 0, 0.5)';
                setTimeout(() => {
                    productCard.style.boxShadow = '';
                }, 1000);
            }
        });
    });

    // Form validation enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }
        });
    });

    // Responsive navigation
    initResponsiveNavigation();

    // Lazy loading for images
    initLazyLoading();

    // Shopping cart counter animation
    initCartCounterAnimation();
});

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Fetch search suggestions
function fetchSearchSuggestions(term) {
    fetch(`api/search-suggestions.php?term=${encodeURIComponent(term)}`)
        .then(response => response.json())
        .then(data => {
            showSearchSuggestions(data);
        })
        .catch(error => {
            console.error('Error fetching search suggestions:', error);
        });
}

// Display search suggestions
function showSearchSuggestions(suggestions) {
    // Implementation for showing search suggestions dropdown
    const suggestionsContainer = document.getElementById('searchSuggestions');
    if (suggestionsContainer && suggestions.length > 0) {
        suggestionsContainer.innerHTML = suggestions.map(suggestion => 
            `<a href="products.php?search=${encodeURIComponent(suggestion)}" class="list-group-item list-group-item-action">${suggestion}</a>`
        ).join('');
        suggestionsContainer.classList.remove('d-none');
    }
}


// Responsive navigation
function initResponsiveNavigation() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
    }
}

// Lazy loading for images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Shopping cart counter animation
function initCartCounterAnimation() {
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        // Observe cart count changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'characterData' || mutation.type === 'childList') {
                    // Animate the badge when count changes
                    cartBadge.classList.add('animate__animated', 'animate__bounce');
                    setTimeout(() => {
                        cartBadge.classList.remove('animate__animated', 'animate__bounce');
                    }, 1000);
                }
            });
        });

        observer.observe(cartBadge, {
            characterData: true,
            childList: true,
            subtree: true
        });
    }
}

// AJAX add to cart functionality
function addToCartAjax(productId, quantity = 1) {
    fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount(data.cartCount);
            
            // Show success message
            showToast('Product added to cart!', 'success');
        } else {
            showToast('Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    });
}

// Update cart count
function updateCartCount(count) {
    const cartBadge = document.querySelector('.cart-badge');
    const cartCount = document.querySelector('.cart-count');
    
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'block' : 'none';
    }
    
    if (cartCount) {
        cartCount.textContent = count;
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }

    // Create toast
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.role = 'alert';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    toastContainer.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

// Price filter functionality
function filterByPrice(min, max) {
    const products = document.querySelectorAll('.product-card');
    products.forEach(product => {
        const priceElement = product.querySelector('.product-price');
        if (priceElement) {
            const price = parseFloat(priceElement.textContent.replace('$', ''));
            if (price >= min && price <= max) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        }
    });
}

// Product sorting functionality
function sortProducts(sortBy) {
    const productContainer = document.querySelector('.products-container');
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    products.sort((a, b) => {
        let aValue, bValue;
        
        switch(sortBy) {
            case 'price-asc':
                aValue = parseFloat(a.querySelector('.product-price').textContent.replace('$', ''));
                bValue = parseFloat(b.querySelector('.product-price').textContent.replace('$', ''));
                return aValue - bValue;
                
            case 'price-desc':
                aValue = parseFloat(a.querySelector('.product-price').textContent.replace('$', ''));
                bValue = parseFloat(b.querySelector('.product-price').textContent.replace('$', ''));
                return bValue - aValue;
                
            case 'name-asc':
                aValue = a.querySelector('.product-name').textContent;
                bValue = b.querySelector('.product-name').textContent;
                return aValue.localeCompare(bValue);
                
            case 'name-desc':
                aValue = a.querySelector('.product-name').textContent;
                bValue = b.querySelector('.product-name').textContent;
                return bValue.localeCompare(aValue);
                
            default:
                return 0;
        }
    });
    
    // Clear and re-add sorted products
    productContainer.innerHTML = '';
    products.forEach(product => {
        productContainer.appendChild(product);
    });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            highlightError(input);
        } else {
            removeErrorHighlight(input);
        }
    });
    
    return isValid;
}

function highlightError(element) {
    element.classList.add('is-invalid');
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.textContent = 'This field is required';
    element.parentNode.appendChild(feedback);
}

function removeErrorHighlight(element) {
    element.classList.remove('is-invalid');
    const feedback = element.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.remove();
    }
}