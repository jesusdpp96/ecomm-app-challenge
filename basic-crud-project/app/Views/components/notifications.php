<div id="notifications" class="notifications-container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification success" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="notification-content">
                <p><?= session()->getFlashdata('success') ?></p>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification error" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="notification-content">
                <p><?= session()->getFlashdata('error') ?></p>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="notification warning" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="notification-content">
                <p><?= session()->getFlashdata('warning') ?></p>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('info')): ?>
        <div class="notification info" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="notification-content">
                <p><?= session()->getFlashdata('info') ?></p>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
</div>
