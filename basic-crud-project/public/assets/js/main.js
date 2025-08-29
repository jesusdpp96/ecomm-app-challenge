// Initialize UI components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize table sorting and product filtering with AJAX
    const productsTable = document.getElementById('products-table');
    const searchForm = document.getElementById('search-form');
    const pagination = document.getElementById('pagination');
    
    if (productsTable) {
        new TableSorter('products-table');
    }
    
    if (searchForm && productsTable) {
        new ProductFilter('#search-form', '#products-table', '#pagination');
    }
    
    // Initialize form validation and AJAX handling
    const productForm = document.getElementById('product-form');
    if (productForm) {
        new FormValidator('product-form');
        
        // Use AJAX for both create and edit forms
        new ProductFormHandler('product-form');
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
