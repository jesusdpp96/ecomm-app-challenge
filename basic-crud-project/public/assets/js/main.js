// Utility functions for UI interactions
class UIHelpers {
    static showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notifications');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <p>${message}</p>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;

        container.appendChild(notification);

        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    }
    
    static showLoading(element) {
        if (!element) return;
        
        const spinner = element.querySelector('.btn-spinner');
        const text = element.querySelector('.btn-text');
        
        if (spinner && text) {
            spinner.classList.remove('hidden');
            spinner.classList.add('show');
            text.classList.add('hide');
        }
        
        element.disabled = true;
    }
    
    static hideLoading(element) {
        if (!element) return;
        
        const spinner = element.querySelector('.btn-spinner');
        const text = element.querySelector('.btn-text');
        
        if (spinner && text) {
            spinner.classList.add('hidden');
            spinner.classList.remove('show');
            text.classList.remove('hide');
        }
        
        element.disabled = false;
    }
    
    static confirmAction(message) {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirmation-modal');
            const messageEl = document.getElementById('confirmation-message');
            const yesBtn = document.getElementById('confirm-yes');
            const noBtn = document.getElementById('confirm-no');
            
            if (!modal || !messageEl || !yesBtn || !noBtn) {
                resolve(false);
                return;
            }
            
            messageEl.textContent = message;
            modal.classList.remove('hidden');
            
            const handleYes = () => {
                modal.classList.add('hidden');
                yesBtn.removeEventListener('click', handleYes);
                noBtn.removeEventListener('click', handleNo);
                resolve(true);
            };
            
            const handleNo = () => {
                modal.classList.add('hidden');
                yesBtn.removeEventListener('click', handleYes);
                noBtn.removeEventListener('click', handleNo);
                resolve(false);
            };
            
            yesBtn.addEventListener('click', handleYes);
            noBtn.addEventListener('click', handleNo);
            
            // Close on background click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    handleNo();
                }
            });
        });
    }
}

// Table sorting functionality
class TableSorter {
    constructor(tableId) {
        this.table = document.getElementById(tableId);
        this.headers = this.table ? this.table.querySelectorAll('th[data-sort]') : [];
        this.currentSort = { column: null, direction: 'asc' };
        this.init();
    }
    
    init() {
        this.headers.forEach(header => {
            header.addEventListener('click', () => {
                const sortBy = header.getAttribute('data-sort');
                this.sort(sortBy, header);
            });
        });
    }
    
    sort(column, headerEl) {
        const tbody = this.table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determine sort direction
        let direction = 'asc';
        if (this.currentSort.column === column && this.currentSort.direction === 'asc') {
            direction = 'desc';
        }
        
        // Update visual indicators
        this.headers.forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
        });
        headerEl.classList.add(`sort-${direction}`);
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = this.getCellValue(a, column);
            const bValue = this.getCellValue(b, column);
            
            let comparison = 0;
            if (column === 'price' || column === 'id') {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            } else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return direction === 'desc' ? -comparison : comparison;
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
        
        this.currentSort = { column, direction };
    }
    
    getCellValue(row, column) {
        const columnIndex = Array.from(this.headers).findIndex(h => h.getAttribute('data-sort') === column);
        const cell = row.cells[columnIndex];
        return cell ? cell.textContent.trim() : '';
    }
}

// Form validation
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.init();
    }
    
    init() {
        if (!this.form) return;
        
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = this.form.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }
    
    validateForm() {
        let isValid = true;
        const inputs = this.form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(input) {
        const value = input.value.trim();
        const type = input.type;
        const name = input.name;
        let isValid = true;
        let errorMessage = '';
        
        // Required validation
        if (!value) {
            isValid = false;
            errorMessage = 'Este campo es requerido';
        }
        
        // Specific validations
        if (isValid && name === 'title') {
            if (value.length < 3) {
                isValid = false;
                errorMessage = 'El título debe tener al menos 3 caracteres';
            } else if (value.length > 100) {
                isValid = false;
                errorMessage = 'El título no puede exceder 100 caracteres';
            }
        }
        
        if (isValid && name === 'price') {
            const price = parseFloat(value);
            if (isNaN(price) || price <= 0) {
                isValid = false;
                errorMessage = 'El precio debe ser un número mayor a 0';
            } else if (price > 999999.99) {
                isValid = false;
                errorMessage = 'El precio no puede exceder $999,999.99';
            }
        }
        
        this.showFieldError(input, isValid ? '' : errorMessage);
        return isValid;
    }
    
    showFieldError(input, message) {
        const errorEl = document.getElementById(`${input.name}-error`);
        if (errorEl) {
            errorEl.textContent = message;
        }
        
        if (message) {
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    }
    
    clearFieldError(input) {
        input.classList.remove('error');
        const errorEl = document.getElementById(`${input.name}-error`);
        if (errorEl && !errorEl.textContent.includes('session')) {
            errorEl.textContent = '';
        }
    }
}

// Global function for product deletion
async function deleteProduct(productId) {
    const confirmed = await UIHelpers.confirmAction(
        '¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.'
    );
    
    if (!confirmed) return;
    
    try {
        const response = await fetch(`/products/${productId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        if (response.ok) {
            UIHelpers.showNotification('Producto eliminado exitosamente', 'success');
            // Redirect to products list or remove row from table
            setTimeout(() => {
                window.location.href = '/';
            }, 1500);
        } else {
            throw new Error('Error al eliminar el producto');
        }
    } catch (error) {
        UIHelpers.showNotification('Error al eliminar el producto', 'error');
        console.error('Delete error:', error);
    }
}

// Initialize UI components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize table sorting
    const productsTable = document.getElementById('products-table');
    if (productsTable) {
        new TableSorter('products-table');
    }
    
    // Initialize form validation
    const productForm = document.getElementById('product-form');
    if (productForm) {
        new FormValidator('product-form');
        
        // Handle form submission with loading states
        productForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                UIHelpers.showLoading(submitBtn);
            }
        });
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
