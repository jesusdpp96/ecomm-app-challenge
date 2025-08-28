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