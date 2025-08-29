<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="products-container" data-logged-in="<?= session()->get('is_logged_in') ? 'true' : 'false' ?>">
    <!-- Header con botón de crear -->
    <div class="products-header">
        <h1>Gestión de Productos</h1>
        <?php if (session()->get('is_logged_in')): ?>
            <a href="<?= base_url('products/create') ?>" class="btn btn-primary">
                Crear Producto
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Filtros de búsqueda -->
    <div class="search-filters">
        <form id="search-form" method="GET">
            <div class="filter-group">
                <input type="text" name="search" id="search" placeholder="Buscar por título..." value="<?= esc($filters['search'] ?? '') ?>" class="form-control">
                <input type="number" name="min_price" placeholder="Precio mín." value="<?= esc($filters['min_price'] ?? '') ?>" class="form-control">
                <input type="number" name="max_price" placeholder="Precio máx." value="<?= esc($filters['max_price'] ?? '') ?>" class="form-control">
                <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-control">
                <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-control">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                <button type="reset" class="btn btn-secondary">Limpiar</button>
            </div>
        </form>
    </div>
    
    <!-- Tabla de productos -->
    <div class="table-container">
        <table id="products-table" class="products-table">
            <thead>
                <tr>
                    <th data-sort="id">ID</th>
                    <th data-sort="title">Título</th>
                    <th data-sort="price">Precio</th>
                    <th data-sort="created_at">Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product->id ?></td>
                            <td><?= esc($product->title) ?></td>
                            <td>$<?= number_format($product->price, 2) ?></td>
                            <td><?= $product->getFormattedDate('d/m/Y H:i') ?></td>
                            <td>
                                <a href="<?= base_url('products/' . $product->id) ?>" class="btn btn-info btn-sm">Ver</a>
                                <?php if (session()->get('is_logged_in')): ?>
                                    <a href="<?= base_url('products/' . $product->id . '/edit') ?>" class="btn btn-primary btn-sm">Editar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay productos disponibles</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
        <div class="pagination-container">
            <nav id="pagination" class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?page=<?= $pagination['current_page'] - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">« Anterior</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                    <a href="?page=<?= $pagination['current_page'] + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">Siguiente »</a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
    
    <!-- Loading state -->
    <div id="loading" class="loading hidden">
        <div class="spinner"></div>
        <p>Cargando productos...</p>
    </div>
</div>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/classes/product-filter.js') ?>"></script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
