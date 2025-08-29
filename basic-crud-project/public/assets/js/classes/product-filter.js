/**
 * ProductFilter - Handles AJAX filtering for products table
 * Manages form submission, URL updates, and table content updates
 */
class ProductFilter {
    constructor(formSelector, tableSelector, paginationSelector = null) {
        this.form = document.querySelector(formSelector);
        this.table = document.querySelector(tableSelector);
        this.pagination = paginationSelector ? document.querySelector(paginationSelector) : null;
        this.tbody = this.table ? this.table.querySelector('tbody') : null;
        
        if (!this.form || !this.table || !this.tbody) {
            console.error('ProductFilter: Required elements not found');
            return;
        }
        
        this.apiEndpoint = '/api/products';
        this.currentPage = 1;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadFiltersFromURL();
    }
      
    bindEvents() {
        // Handle form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.currentPage = 1; // Reset to first page on new search
            this.applyFilters();
        });
        
        // Handle reset button
        const resetBtn = this.form.querySelector('button[type="reset"]');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.resetFilters();
            });
        }
        
        // Handle pagination clicks
        if (this.pagination) {
            this.pagination.addEventListener('click', (e) => {
                if (e.target.tagName === 'A' && e.target.hasAttribute('data-page')) {
                    e.preventDefault();
                    const page = parseInt(e.target.getAttribute('data-page'));
                    this.currentPage = page;
                    this.applyFilters();
                }
            });
        }
        
        // Handle table sorting
        const sortHeaders = this.table.querySelectorAll('th[data-sort]');
        sortHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const sortBy = header.getAttribute('data-sort');
                const currentOrder = header.classList.contains('sort-asc') ? 'desc' : 'asc';
                
                // Remove sort classes from all headers
                sortHeaders.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                
                // Add sort class to clicked header
                header.classList.add(`sort-${currentOrder}`);
                
                this.currentPage = 1;
                this.applyFilters(sortBy, currentOrder);
            });
        });
    }
    
    async applyFilters(sortBy = null, order = null) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        UIHelpers.showTableLoading();
        
        try {
            const formData = new FormData(this.form);
            const params = new URLSearchParams();
            
            // Add form data to params
            for (const [key, value] of formData.entries()) {
                if (value.trim()) {
                    params.append(key, value);
                }
            }
            
            // Add pagination
            params.append('page', this.currentPage);
            params.append('per_page', '7');
            
            // Add sorting if provided
            if (sortBy) {
                params.append('sort_by', sortBy);
                params.append('order', order);
            }
            
            // Update URL without page reload
            this.updateURL(params);
            
            // Make AJAX request
            const response = await fetch(`${this.apiEndpoint}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.updateTable(data.data);
                this.updatePagination(data.pagination);
                UIHelpers.showNotification('Filtros aplicados correctamente', 'success', 3000);
            } else {
                throw new Error(data.message || 'Error applying filters');
            }
            
        } catch (error) {
            console.error('Filter error:', error);
            UIHelpers.showNotification('Error al aplicar filtros: ' + error.message, 'error');
        } finally {
            this.isLoading = false;
            UIHelpers.hideTableLoading();
        }
    }
    
    updateTable(products) {
        if (!products || !Array.isArray(products)) {
            this.tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay productos disponibles</td></tr>';
            return;
        }
        
        const container = document.querySelector('.products-container');
        const isLoggedIn = container && container.dataset.loggedIn === 'true';
        
        this.tbody.innerHTML = products.map(product => `
            <tr>
                <td>${product.id}</td>
                <td>${this.escapeHtml(product.title)}</td>
                <td>$${parseFloat(product.price).toFixed(2)}</td>
                <td>${this.formatDate(product.created_at)}</td>
                <td>
                    <a href="/products/${product.id}" class="btn btn-info btn-sm">Ver</a>
                    ${isLoggedIn ? `<a href="/products/${product.id}/edit" class="btn btn-primary btn-sm">Editar</a>` : ''}
                </td>
            </tr>
        `).join('');
    }
    
    updatePagination(paginationData) {
        if (!this.pagination || !paginationData) return;
        
        const { current_page, total_pages, has_prev, has_next } = paginationData;
        
        if (total_pages <= 1) {
            this.pagination.innerHTML = '';
            return;
        }
        
        let paginationHTML = '';
        
        // Previous button
        if (has_prev) {
            paginationHTML += `<a href="#" data-page="${current_page - 1}">« Anterior</a>`;
        }
        
        // Page numbers
        for (let i = 1; i <= total_pages; i++) {
            if (i === current_page) {
                paginationHTML += `<span class="active">${i}</span>`;
            } else {
                paginationHTML += `<a href="#" data-page="${i}">${i}</a>`;
            }
        }
        
        // Next button
        if (has_next) {
            paginationHTML += `<a href="#" data-page="${current_page + 1}">Siguiente »</a>`;
        }
        
        this.pagination.innerHTML = paginationHTML;
    }
    
    updateURL(params) {
        const url = new URL(window.location);
        
        // Clear existing search params
        url.search = '';
        
        // Add new params
        for (const [key, value] of params.entries()) {
            if (value && value.trim()) {
                url.searchParams.set(key, value);
            }
        }
        
        // Update URL without page reload
        window.history.pushState({}, '', url.toString());
    }
    
    loadFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);
        
        // Load form values from URL
        for (const [key, value] of params.entries()) {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input && key !== 'page') {
                input.value = value;
            }
        }
        
        // Load current page
        this.currentPage = parseInt(params.get('page')) || 1;
    }
    
    resetFilters() {
        // Clear form
        this.form.reset();
        
        // Reset page
        this.currentPage = 1;
        
        // Clear URL params
        const url = new URL(window.location);
        url.search = '';
        window.history.pushState({}, '', url.toString());
        
        // Apply empty filters (reload all products)
        this.applyFilters();
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}
