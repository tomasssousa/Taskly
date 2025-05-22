<?php
function showWarningPopup($id, $title, $message, $confirmText = 'Yes', $cancelText = 'No') {
    ?>
    <div class="popup-overlay" id="<?= $id ?>">
        <div class="warning-popup">
            <h3 class="popup-title"><?= htmlspecialchars($title) ?></h3>
            <p><?= htmlspecialchars($message) ?></p>
            <div class="popup-buttons">
                <button class="popup-btn popup-btn-cancel" onclick="closePopup('<?= $id ?>')"><?= htmlspecialchars($cancelText) ?></button>
                <button class="popup-btn popup-btn-confirm" id="<?= $id ?>_confirm"><?= htmlspecialchars($confirmText) ?></button>
            </div>
        </div>
    </div>
    <?php
}
?>

<script>
function showPopup(popupId, onConfirm) {
    const popup = document.getElementById(popupId);
    popup.style.display = 'flex';
    
    const confirmButton = document.getElementById(`${popupId}_confirm`);
    confirmButton.onclick = () => {
        onConfirm();
        closePopup(popupId);
    };
}

function closePopup(popupId) {
    const popup = document.getElementById(popupId);
    popup.style.display = 'none';
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay')) {
        closePopup(e.target.id);
    }
});
</script>