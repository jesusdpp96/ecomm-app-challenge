// Util function to get CSRF token from any form on the page
function getCSRFTokenFromForm() {
  // Try to find CSRF token from any form on the page
  const forms = document.querySelectorAll('form');
  
  for (let form of forms) {
      const csrfInputs = form.querySelectorAll('input[type="hidden"]');
      
      for (let input of csrfInputs) {
          if (input.name.includes('csrf') && input.name.includes('name')) {
              return {
                  name: input.name,
                  value: input.value
              };
          }
      }
  }
  
  // If no form CSRF found, try meta tag as fallback
  const metaToken = document.querySelector('meta[name="csrf-token"]');
  if (metaToken) {
      return {
          name: 'X-CSRF-TOKEN',
          value: metaToken.getAttribute('content')
      };
  }
  
  console.error('CSRF token not found');
  return { name: null, value: null };
}