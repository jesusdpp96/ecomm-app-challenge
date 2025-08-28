// Initialize UI components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize table sorting
    const productsTable = document.getElementById('products-table');
    if (productsTable) {
        new TableSorter('products-table');
    }
    
    // Initialize form validation and AJAX handling
    const productForm = document.getElementById('product-form');
    if (productForm) {
        new FormValidator('product-form');
        
        // Use AJAX for both create and edit forms
        new ProductFormHandler('product-form');
    }
    
    // Initialize search form
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        const resetBtn = searchForm.querySelector('button[type="reset"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                setTimeout(() => {
                    window.location.href = window.location.pathname;
                }, 100);
            });
        }
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('confirmation-modal');
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });
    
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    });
});
