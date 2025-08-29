<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= $title ?? 'ECOMM-APP Challenge Técnico' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">
</head>
<body>
    <header class="main-header">
        <div class="main-header-content">
            <nav class="navbar">
                <div class="navbar-brand">
                    <a href="<?= base_url('/') ?>">ECOMM-APP Challenge Técnico</a>
                </div>
                <div class="navbar-nav">
                    <a href="<?= base_url('/') ?>" class="nav-link">Productos</a>
                    <?php if (session()->get('is_logged_in')): ?>
                        <div class="user-menu">
                            <span class="nav-link user-info">Hola, <?= esc(session()->get('username')) ?></span>
                            <button onclick="confirmLogout()" class="btn btn-secondary btn-sm">Cerrar Sesión</button>
                        </div>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="nav-link">Iniciar Sesión</a>
                    <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <?= $this->include('components/notifications') ?>
        <?= $this->renderSection('content') ?>
    </main>
    
    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> ECOMM-APP Challenge Técnico. Sistema de gestión de productos.</p>
    </footer>
    
    <?= $this->include('components/confirmation-modal') ?>
    <script src="<?= base_url('assets/js/classes/ui-helpers.js') ?>"></script>
    <script src="<?= base_url('assets/js/classes/form-validator.js') ?>"></script>
    <script src="<?= base_url('assets/js/classes/table-sorter.js') ?>"></script>
    <script src="<?= base_url('assets/js/classes/product-form-handler.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script src="<?= base_url('assets/js/actions/delete-product.js') ?>"></script>
    <script src="<?= base_url('assets/js/actions/confirm-logout.js') ?>"></script>
    <script src="<?= base_url('assets/js/utils/get-csrf-token-from-form.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
