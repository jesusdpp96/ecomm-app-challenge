/**
 * Confirm logout action
 */
function confirmLogout() {
  console.log("assaaaaaa")
  const modal = document.getElementById('confirmation-modal');
  const modalTitle = modal.querySelector('h3');
  const modalMessage = document.getElementById('confirmation-message');
  const confirmBtn = document.getElementById('confirm-yes');
  const cancelBtn = document.getElementById('confirm-no');
  
  modalTitle.textContent = 'Confirmar Cierre de Sesión';
  modalMessage.textContent = '¿Está seguro que desea cerrar sesión?';
  confirmBtn.textContent = 'Cerrar Sesión';
  
  confirmBtn.onclick = function() {
      window.location.href = '/logout';
  };
  
  cancelBtn.onclick = function() {
      modal.classList.add('hidden');
  };
  
  modal.classList.remove('hidden');
}