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