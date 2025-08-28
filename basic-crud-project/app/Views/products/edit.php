<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="form-container">
    <div class="form-header">
        <h1>Editar Producto</h1>
        <div class="header-actions">
            <a href="<?= base_url('/') ?>" class="btn btn-secondary">
                ← Volver a la lista
            </a>
            <a href="<?= base_url('products/' . $product->id) ?>" class="btn btn-info">
                Ver Producto
            </a>
        </div>
    </div>
    
    <form id="product-form" class="product-form" action="<?= base_url('products/' . $product->id) ?>" method="POST" data-product-id="<?= $product->id ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <div class="form-group">
            <label for="title">Título del Producto *</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                value="<?= esc($product->title) ?>"
                required 
                minlength="3" 
                maxlength="100"
                class="form-control"
            >
            <div class="error-message" id="title-error">
                <?= session('errors.title') ?>
            </div>
        </div>
        
        <div class="form-group">
            <label for="price">Precio *</label>
            <input 
                type="number" 
                id="price" 
                name="price" 
                value="<?= $product->price ?>"
                required 
                min="0.01" 
                max="999999.99" 
                step="0.01"
                class="form-control"
            >
            <div class="error-message" id="price-error">
                <?= session('errors.price') ?>
            </div>
        </div>
        
        <div class="form-info">
            <p><strong>Creado:</strong> <?= $product->getFormattedDate('d/m/Y H:i') ?></p>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span class="btn-text">Actualizar Producto</span>
                <span class="btn-spinner hidden">Actualizando...</span>
            </button>
            <button type="button" class="btn btn-danger" onclick="deleteProduct(<?= $product->id ?>)">
                Eliminar Producto
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
