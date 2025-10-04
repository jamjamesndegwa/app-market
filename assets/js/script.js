// JavaScript for Prady Tec AppMarket

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

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // File upload drag and drop
    const fileUploadArea = document.getElementById('file-upload-area');
    if (fileUploadArea) {
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById('app_file');
                if (fileInput) {
                    fileInput.files = files;
                    updateFileInfo(files[0]);
                }
            }
        });

        fileUploadArea.addEventListener('click', function() {
            const fileInput = document.getElementById('app_file');
            if (fileInput) {
                fileInput.click();
            }
        });
    }

    // File input change handler
    const fileInput = document.getElementById('app_file');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateFileInfo(this.files[0]);
            }
        });
    }

    // Update file info display
    function updateFileInfo(file) {
        const fileInfo = document.getElementById('file-info');
        if (fileInfo) {
            fileInfo.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-file me-2"></i>
                    <strong>${file.name}</strong><br>
                    <small>Size: ${formatFileSize(file.size)} | Type: ${file.type}</small>
                </div>
            `;
        }
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }

    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = this.dataset.appId;
            const price = this.dataset.price;
            
            // Add to cart via AJAX
            fetch('ajax/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    app_id: appId,
                    price: price
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'App added to cart successfully!');
                    updateCartCount();
                } else {
                    showAlert('error', data.message || 'Failed to add app to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while adding to cart');
            });
        });
    });

    // Remove from cart functionality
    const removeFromCartButtons = document.querySelectorAll('.remove-from-cart');
    removeFromCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const appId = this.dataset.appId;
            
            // Remove from cart via AJAX
            fetch('ajax/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    app_id: appId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'App removed from cart');
                    updateCartCount();
                    // Remove the item from the DOM
                    this.closest('.cart-item').remove();
                    updateCartTotal();
                } else {
                    showAlert('error', data.message || 'Failed to remove app from cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while removing from cart');
            });
        });
    });

    // Update cart count
    function updateCartCount() {
        fetch('ajax/get-cart-count.php')
            .then(response => response.json())
            .then(data => {
                const cartBadge = document.querySelector('.navbar .badge');
                if (cartBadge) {
                    cartBadge.textContent = data.count;
                }
            })
            .catch(error => {
                console.error('Error updating cart count:', error);
            });
    }

    // Update cart total
    function updateCartTotal() {
        fetch('ajax/get-cart-total.php')
            .then(response => response.json())
            .then(data => {
                const totalElement = document.getElementById('cart-total');
                if (totalElement) {
                    totalElement.textContent = '$' + data.total.toFixed(2);
                }
            })
            .catch(error => {
                console.error('Error updating cart total:', error);
            });
    }

    // Show alert function
    function showAlert(type, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertContainer.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertContainer.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertContainer);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }

    // Search functionality
    const searchForm = document.querySelector('form[action*="apps.php"]');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 3) {
                        performSearch(this.value);
                    }
                }, 500);
            });
        }
    }

    // Perform search
    function performSearch(query) {
        fetch(`ajax/search-apps.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.results);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    // Display search results
    function displaySearchResults(results) {
        const resultsContainer = document.getElementById('search-results');
        if (resultsContainer) {
            if (results.length === 0) {
                resultsContainer.innerHTML = '<p class="text-muted">No apps found matching your search.</p>';
            } else {
                resultsContainer.innerHTML = results.map(app => `
                    <div class="search-result-item p-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-mobile-alt fa-2x text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${app.name}</h6>
                                <p class="text-muted small mb-1">${app.developer}</p>
                                <p class="text-muted small">${app.short_description}</p>
                            </div>
                            <div class="text-end">
                                <span class="h6 text-success mb-0">$${parseFloat(app.price).toFixed(2)}</span>
                                <br>
                                <a href="app-details.php?id=${app.id}" class="btn btn-sm btn-primary mt-1">View</a>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }
    }

    // Rating system
    const ratingStars = document.querySelectorAll('.rating-star');
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            const appId = this.dataset.appId;
            
            // Submit rating via AJAX
            fetch('ajax/submit-rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    app_id: appId,
                    rating: rating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Rating submitted successfully!');
                    updateRatingDisplay(appId, rating);
                } else {
                    showAlert('error', data.message || 'Failed to submit rating');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'An error occurred while submitting rating');
            });
        });
    });

    // Update rating display
    function updateRatingDisplay(appId, rating) {
        const ratingContainer = document.querySelector(`[data-app-id="${appId}"]`).closest('.rating-container');
        if (ratingContainer) {
            const stars = ratingContainer.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('text-warning');
                    star.classList.remove('text-muted');
                } else {
                    star.classList.remove('text-warning');
                    star.classList.add('text-muted');
                }
            });
        }
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(this.dataset.preview);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Initialize animations
    const animateElements = document.querySelectorAll('.fade-in-up');
    if ('IntersectionObserver' in window) {
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationDelay = '0.1s';
                    entry.target.classList.add('animate');
                }
            });
        });

        animateElements.forEach(el => {
            animationObserver.observe(el);
        });
    }
});

// Utility functions
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

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
