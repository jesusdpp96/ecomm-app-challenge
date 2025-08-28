<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="form-container">
    <div class="form-header">
        <h1>Crear Nuevo Producto</h1>
        <a href="<?= base_url('/') ?>" class="btn btn-secondary">
            ← Volver a la lista
        </a>
    </div>
    
    <form id="product-form" class="product-form" action="<?= base_url('products') ?>" method="POST" novalidate>
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label for="title">Título del Producto *</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                required 
                minlength="3" 
                maxlength="100"
                class="form-control"
                placeholder="Ingrese el título del producto"
                value="<?= old('title') ?>"
            >
            <div class="error-message" id="title-error">
                <?= session('errors.title') ?>
            </div>
            <div class="help-text">Mínimo 3 caracteres, máximo 100</div>
        </div>
        
        <div class="form-group">
            <label for="price">Precio *</label>
            <input 
                type="number" 
                id="price" 
                name="price" 
                required 
                min="0.01" 
                max="999999.99" 
                step="0.01"
                class="form-control"
                placeholder="0.00"
                value="<?= old('price') ?>"
            >
            <div class="error-message" id="price-error">
                <?= session('errors.price') ?>
            </div>
            <div class="help-text">Precio debe ser mayor a 0</div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span class="btn-text">Crear Producto</span>
                <span class="btn-spinner hidden">Creando...</span>
            </button>
            <button type="reset" class="btn btn-secondary">
                Limpiar Formulario
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
