// Product form handler for AJAX operations
class ProductFormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.submitBtn = null;
        this.isEditForm = false;
        this.productId = null;
        this.init();
    }

    init() {
        if (!this.form) return;

        this.submitBtn = this.form.querySelector('button[type="submit"]');
        
        // Determine if this is an edit form
        this.productId = this.form.dataset.productId;
        this.isEditForm = !!this.productId;
        
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

            this.handleSuccess(response);

        } catch (error) {
            console.error('Error al enviar el formulario:', error);
            // Check if it's a validation error with a JSON body
            if (error.responseJson) {
                console.error('Error al enviar el formulario:', error.responseJson);
                this.handleErrors(error.responseJson);
            } else {
                this.handleNetworkError(error);
            }
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
        return formData;
    }

    async submitProduct(formData) {
        const endpoint = this.isEditForm ? `/api/products/${this.productId}` : '/api/products';
        const method = 'POST'; // Always use POST

        // If editing, use method spoofing for PUT
        if (this.isEditForm) {
            formData.append('_method', 'PUT');
        }

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        console.log(`Submitting ${method} request to ${endpoint} (spoofing ${this.isEditForm ? 'PUT' : 'POST'})`);

        const response = await fetch(endpoint, {
            method: method,
            headers: headers,
            body: formData
        });

        const result = await response.json();
        
        // Handle HTTP status codes
        if (!response.ok) {
            const error = new Error('Server error');
            error.responseJson = result; // Attach the JSON response to the error
            throw error;
        }

        return result;
    }

    handleSuccess(response) {
        const message = this.isEditForm 
            ? (response.message || 'Producto actualizado exitosamente')
            : (response.message || 'Producto creado exitosamente');
            
        UIHelpers.showNotification(message, 'success');

        // Update CSRF token if provided in response
        if (response.csrf_token) {
            this.updateCSRFToken(response.csrf_token);
        }

        // For create forms, reset the form; for edit forms, keep the data
        if (!this.isEditForm) {
            this.form.reset();
        }
        
        this.clearErrors();
    }

    handleErrors(response) {
        // Handle new error format { success, code, errors, message, ... }
        
        //     UIHelpers.showNotification(response.message, 'error');
        UIHelpers.showNotification("Error al procesar el formulario", 'error');
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
