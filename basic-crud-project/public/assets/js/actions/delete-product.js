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