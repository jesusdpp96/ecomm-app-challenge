<div id="notifications" class="notifications-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification success">
            <div class="notification-content">
                <p><?= session()->getFlashdata('success') ?></p>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification error">
            <div class="notification-content">
                <p><?= session()->getFlashdata('error') ?></p>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="notification warning">
            <div class="notification-content">
                <p><?= session()->getFlashdata('warning') ?></p>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('info')): ?>
        <div class="notification info">
            <div class="notification-content">
                <p><?= session()->getFlashdata('info') ?></p>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        </div>
    <?php endif; ?>
</div>
