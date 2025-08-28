<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="form-container">
    <div class="form-header">
        <h1>Iniciar Sesión</h1>
        <p class="text-muted">Accede a tu cuenta para gestionar productos</p>
    </div>
    
    <form id="login-form" class="login-form" action="<?= base_url('login') ?>" method="POST" novalidate>
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="username">Usuario *</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                required 
                minlength="3" 
                maxlength="50"
                class="form-control"
                placeholder="Ingrese su nombre de usuario"
                value="<?= old('username') ?>"
                autocomplete="username"
            >
            <div class="error-message" id="username-error"></div>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña *</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                minlength="3"
                class="form-control"
                placeholder="Ingrese su contraseña"
                autocomplete="current-password"
            >
            <div class="error-message" id="password-error"></div>
        </div>
        
        <div class="form-info">
            <p><strong>Credenciales de prueba:</strong></p>
            <p>Usuario: <code>admin</code></p>
            <p>Contraseña: <code>admin123</code></p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">
                <span class="btn-text">Iniciar Sesión</span>
                <span class="btn-spinner hidden">Iniciando...</span>
            </button>
        </div>
    </form>
    
    <div class="text-center mt-lg">
        <a href="<?= base_url('/') ?>" class="nav-link">← Volver al inicio</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Login form specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        // Initialize form validation for login
        new FormValidator('login-form', {
            username: {
                required: true,
                minLength: 3,
                maxLength: 50
            },
            password: {
                required: true,
                minLength: 3
            }
        });
        
        // Handle form submission with loading states
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                UIHelpers.showLoading(submitBtn);
            }
        });
    }
});
</script>
<script src="<?= base_url('assets/js/classes/form-validator.js') ?>"></script>
<?= $this->endSection() ?>
