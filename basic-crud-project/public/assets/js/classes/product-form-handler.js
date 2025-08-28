// Product form handler for AJAX operations
class ProductFormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.submitBtn = null;
        this.init();
    }

    init() {
        if (!this.form) return;

        this.submitBtn = this.form.querySelector('button[type="submit"]');
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(event) {
        event.preventDefault();

        // Validate form first
        const formValidator = new FormValidator(this.form.id);
        if (!formValidator.validateForm()) {
            return;
        }

        try {
            this.setLoadingState(true);
            this.clearErrors();

            const formData = this.getFormData();
            const response = await this.submitProduct(formData);

            if (response.success) {
                this.handleSuccess(response);
            } else {
                this.handleErrors(response.errors || []);
            }
        } catch (error) {
            this.handleNetworkError(error);
        } finally {
            this.setLoadingState(false);
        }
    }

    getFormData() {
        const formData = new FormData(this.form);
        
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Extract CSRF token fields - CodeIgniter uses dynamic field names
        let csrfTokenName = null;
        let csrfTokenValue = null;
        
        // Find the actual CSRF field name (it's dynamic in CodeIgniter)
        for (let [key, value] of formData.entries()) {
            if (key.includes('csrf') && key.includes('name')) {
                // This is likely the CSRF token field name
                csrfTokenName = key;
                csrfTokenValue = value;
                break;
            }
        }
        
        // If not found in FormData, try DOM directly
        if (!csrfTokenName || !csrfTokenValue) {
            const csrfInputs = this.form.querySelectorAll('input[type="hidden"]');
            csrfInputs.forEach(input => {
                if (input.name.includes('csrf') && input.name.includes('name')) {
                    csrfTokenName = input.name;
                    csrfTokenValue = input.value;
                }
            });
        }
                
        const data = {
            title: formData.get('title')?.trim(),
            price: parseFloat(formData.get('price')) || 0
        };
        
        // Add CSRF tokens if they exist
        if (csrfTokenName && csrfTokenValue) {
            data[csrfTokenName] = csrfTokenValue;
        } else {
            console.error('CSRF tokens not found in form data');
        }
        
        return data;
    }

    async submitProduct(data) {
        // Try sending as FormData instead of JSON for better CSRF compatibility
        const formData = new FormData();
        
        // Add all data fields to FormData
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        console.log('FormData entries:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        const response = await fetch('/api/products', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
                // Don't set Content-Type for FormData - let browser set it with boundary
            },
            body: formData
        });

        const result = await response.json();
        
        // Handle HTTP status codes
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('No autorizado. Por favor, inicie sesión nuevamente.');
            } else if (response.status === 403) {
                throw new Error('No tiene permisos para realizar esta acción.');
            } else if (response.status === 422 || response.status === 400) {
                // Validation errors - return as is
                return result;
            } else {
                throw new Error(result.message || 'Error del servidor');
            }
        }

        return result;
    }

    handleSuccess(response) {
        UIHelpers.showNotification(
            response.message || 'Producto creado exitosamente',
            'success'
        );

        // Update CSRF token if provided in response
        if (response.csrf_token) {
            this.updateCSRFToken(response.csrf_token);
        }

        // Reset form
        this.form.reset();
        this.clearErrors();
    }

    handleErrors(errors) {
        if (!Array.isArray(errors)) {
            UIHelpers.showNotification('Error de validación', 'error');
            return;
        }

        errors.forEach(error => {
            if (error.field && error.message) {
                this.showFieldError(error.field, error.message);
            } else {
                UIHelpers.showNotification(
                    error.message || 'Error de validación',
                    'error'
                );
            }
        });
    }

    handleNetworkError(error) {
        console.error('Network error:', error);
        
        let message = 'Error de conexión. Verifique su conexión a internet.';
        
        if (error.message.includes('autorizado')) {
            message = error.message;
            // Redirect to login after showing error
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else if (error.message.includes('permisos')) {
            message = error.message;
        } else if (error.message.includes('servidor')) {
            message = error.message;
        }

        UIHelpers.showNotification(message, 'error');
    }

    showFieldError(fieldName, message) {
        const errorEl = document.getElementById(`${fieldName}-error`);
        const inputEl = document.querySelector(`[name="${fieldName}"]`);
        
        if (errorEl) {
            errorEl.textContent = message;
        }
        
        if (inputEl) {
            inputEl.classList.add('error');
        }
    }

    clearErrors() {
        // Remove all error messages
        const errorElements = this.form.querySelectorAll('.error-message');
        errorElements.forEach(element => element.remove());

        // Remove error classes from inputs
        const inputs = this.form.querySelectorAll('.error');
        inputs.forEach(input => input.classList.remove('error'));
    }

    /**
     * Update CSRF token in the form
     * @param {string} newToken - The new CSRF token
     */
    updateCSRFToken(newToken) {
        const csrfInput = this.form.querySelector('input[name="csrf_test_name"]');
        if (csrfInput) {
            csrfInput.value = newToken;
            console.log('CSRF token updated successfully');
        } else {
            console.warn('CSRF token input not found in form');
        }
    }

    setLoadingState(isLoading) {
        if (!this.submitBtn) return;

        if (isLoading) {
            UIHelpers.showLoading(this.submitBtn);
        } else {
            UIHelpers.hideLoading(this.submitBtn);
        }
    }
}
