<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= $title ?? 'ECOMM-APP' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">
</head>
<body>
    <header class="main-header">
        <div class="main-header-content">
            <nav class="navbar">
                <div class="navbar-brand">
                    <a href="<?= base_url('/') ?>">ECOMM-APP</a>
                </div>
                <div class="navbar-nav">
                    <a href="<?= base_url('/') ?>" class="nav-link">Productos</a>
                    <a href="<?= base_url('products/create') ?>" class="nav-link">Crear Producto</a>
                </div>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <?= $this->include('components/notifications') ?>
        <?= $this->renderSection('content') ?>
    </main>
    
    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> ECOMM-APP. Sistema de gestión de productos.</p>
    </footer>
    
    <?= $this->include('components/confirmation-modal') ?>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
