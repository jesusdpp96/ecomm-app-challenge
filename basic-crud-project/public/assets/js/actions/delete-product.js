// Global function for product deletion
async function deleteProduct(productId) {
  const confirmed = await UIHelpers.confirmAction(
      '¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.'
  );
  
  if (!confirmed) return;
  
  try {
      // Get CSRF token using the same logic as ProductFormHandler
      const csrfData = getCSRFTokenFromForm();
      
      // Prepare request body with CSRF token (like in submitProduct)
      const requestData = {};
      
      // Add CSRF token to request body if found
      if (csrfData.name && csrfData.value) {
          requestData[csrfData.name] = csrfData.value;
      }
      
      const response = await fetch(`/api/products/${productId}`, {
          method: 'DELETE',
          headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify(requestData)
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
  }
}
