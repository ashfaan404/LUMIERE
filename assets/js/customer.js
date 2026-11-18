// Customer Account JS - Phase 7

// Profile picture preview before upload
function initProfilePicPreview() {
    const input = document.getElementById('profile_pic_input');
    const preview = document.getElementById('profile_pic_preview');
    if (!input || !preview) return;

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('Image must be less than 2MB');
            this.value = '';
            return;
        }

        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Only JPG, PNG, GIF, WEBP images allowed');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // div fallback
                preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            }
        };
        reader.readAsDataURL(file);
    });
}

// Star rating interaction
function initStarRating() {
    const stars = document.querySelectorAll('.star-rating label');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            // handled by CSS + input:checked
        });
    });
}

// Address modal open/close
function openAddressModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeAddressModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
function initModalOverlays() {
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });
}

// Confirm before delete
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

// Auto-close flash messages after 4 seconds
function autoCloseAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, 4000);
    });
}

// Profile dropdown toggle (click based fallback)
function initProfileDropdown() {
    const dropdownTrigger = document.querySelector('.profile-avatar-btn');
    const dropdown = document.querySelector('.profile-dropdown');

    if (!dropdownTrigger || !dropdown) return;

    // hover is handled by CSS, but click for mobile
    dropdownTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
    });

    // Close if clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdownTrigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = '';
        }
    });
}

// Phone number basic format validation
function initPhoneValidation() {
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    });
}

// On DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initProfilePicPreview();
    initStarRating();
    initModalOverlays();
    autoCloseAlerts();
    initProfileDropdown();
    initPhoneValidation();
});
