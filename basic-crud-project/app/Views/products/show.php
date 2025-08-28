<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="product-detail">
    <div class="detail-header">
        <h1>Detalle del Producto</h1>
        <div class="header-actions">
            <a href="<?= base_url('/') ?>" class="btn btn-secondary">
                ← Volver a la lista
            </a>
            <?php if (session()->get('is_logged_in')): ?>
                <a href="<?= base_url('products/' . $product->id . '/edit') ?>" class="btn btn-primary">
                    Editar Producto
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="product-card">
        <div class="product-info">
            <div class="info-group">
                <label>ID:</label>
                <span><?= $product->id ?></span>
            </div>
            
            <div class="info-group">
                <label>Título:</label>
                <span><?= esc($product->title) ?></span>
            </div>
            
            <div class="info-group">
                <label>Precio:</label>
                <span class="price">$<?= number_format($product->price, 2) ?></span>
            </div>
            
            <div class="info-group">
                <label>Fecha de Creación:</label>
                <span><?= $product->getFormattedDate('d/m/Y H:i:s') ?></span>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
