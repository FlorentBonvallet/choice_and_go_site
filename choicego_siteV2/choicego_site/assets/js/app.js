// Basic interactions for +/- counters, password toggle, swap button, birthdate fallback and terms modal
document.addEventListener('click', (e) => {
    if (e.target.matches && e.target.matches('.counter .plus')) {
        const input = e.target.parentElement.querySelector('input[type=number]');
        input.value = parseInt(input.value || 0, 10) + 1;
    }
    if (e.target.matches && e.target.matches('.counter .minus')) {
        const input = e.target.parentElement.querySelector('input[type=number]');
        const next = Math.max(1, parseInt(input.value || 1, 10) - 1);
        input.value = next;
    }
    if (e.target.matches && e.target.matches('.toggle-eye')) {
        const input = e.target.parentElement.querySelector('input');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // Swap departure/arrival values (normalize text node clicks)
    const clicked = (e.target.nodeType === Node.TEXT_NODE ? e.target.parentElement : e.target);
    const swapBtn = clicked && clicked.closest && clicked.closest('.swap');
    if (swapBtn) {
        const form = swapBtn.closest('form');
        if (!form) return;
        const from = form.querySelector('input[name="from"]');
        const to = form.querySelector('input[name="to"]');
        if (!from || !to) return;
        const tmp = from.value;
        from.value = to.value;
        to.value = tmp;
        from.focus();
    }

    // Terms modal open/close
    if (clicked && clicked.matches && clicked.matches('#show-terms')) {
        e.preventDefault();
        const modal = document.getElementById('terms-modal');
        if (!modal) return;
        modal.style.display = '';
        modal.setAttribute('aria-hidden', 'false');
        // Focus first interactive element in modal
        const btn = modal.querySelector('.modal-close');
        if (btn) btn.focus();
    }

    // If user clicks the accept button, mark the tos checkbox as checked (then let close logic run)
    if (clicked && ((clicked.matches && clicked.matches('.modal-accept')) || (clicked.closest && clicked.closest('.modal-accept')))) {
        const form = document.querySelector('.auth-form');
        if (form) {
            const tos = form.querySelector('input[name="tos"]');
            if (tos) tos.checked = true;
        }
    }

    // Close modal when clicking overlays or modal-close buttons
    if (clicked && clicked.closest && (clicked.closest('.modal-close') || clicked.closest('.modal-overlay') || clicked.matches('.modal-close'))) {
        const modal = document.getElementById('terms-modal');
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    // Ouvrir popup trajet
    if (clicked.closest('.btn-supprimer')) {
        const popup = clicked.nextElementSibling; // popup juste après le bouton
        if (popup && popup.classList.contains('popup-supr')) {
            popup.style.display = 'block';
        }
    }

    // Ouvrir popup réservation
    if (clicked.closest('.btn-annuler-reservation')) {
        const popup = clicked.nextElementSibling; // popup juste après le bouton
        if (popup && popup.classList.contains('popup-supr')) {
            popup.style.display = 'block';
        }
    }

    // Fermer popup (bouton annuler ou croix)
    if (clicked.closest('.btn-annuler') || clicked.closest('.popup-close-supr')) {
        const popup = clicked.closest('.popup-supr');
        if (popup) popup.style.display = 'none';
    }

    // Confirmer suppression trajet
    if (clicked.closest('.btn-confirmer')) {
        const popup = clicked.closest('.popup-supr');
        if (popup) popup.style.display = 'none';

        const btn = popup.previousElementSibling;

        if (btn && btn.classList.contains('btn-supprimer')) {
            console.log('Trajet supprimé');
            // ici fetch / PHP pour supprimer le trajet
        }

        if (btn && btn.classList.contains('btn-annuler-reservation')) {
            const reservationId = btn.dataset.reservationId;
            console.log('Annulation réservation ID:', reservationId);
            // ici fetch / PHP pour annuler la réservation
        }
    }



    

    // ===============================
    // POPUP SUPPRESSION VÉHICULE
    // ===============================
    if (clicked && clicked.closest && clicked.closest('.btn-delete-vehicle')) {
        const btn = clicked.closest('.btn-delete-vehicle');
        const popup = btn.nextElementSibling;

        if (!popup) return;

        const vehicleId = btn.dataset.vehicleId;
        const vehicleName = btn.dataset.vehicleName;
        const vehiclePlate = btn.dataset.vehiclePlate;

        const text = popup.querySelector('#vehicle-popup-text');
        if (text) {
            text.textContent = `Voulez-vous vraiment supprimer le véhicule ${vehicleName} (${vehiclePlate}) ?`;
        }

        popup.dataset.vehicleId = vehicleId;
        popup.style.display = 'block';
    }

    if (clicked && clicked.closest && clicked.closest('.annuler-vehicle')) {
        const popup = clicked.closest('.popup-vehicle');
        if (popup) popup.style.display = 'none';
    }

    if (clicked && clicked.closest && clicked.closest('.confirmer-vehicle')) {
        const popup = clicked.closest('.popup-vehicle');
        if (!popup) return;

        const vehicleId = popup.dataset.vehicleId;
        popup.style.display = 'none';

        console.log('Suppression véhicule ID :', vehicleId);

        
    }

    if (e.target.id === 'popup-confirm') {
        if (!deleteData.type || !deleteData.id) return;

        window.location.href =
            `delete.php?type=${deleteData.type}&id=${deleteData.id}`;
    }



});

// Close modal with Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('terms-modal');
        if (modal && modal.style.display !== 'none') {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        // Also close mobile menu on Escape
        closeMobileMenu();
    }
});

// ========================================
// MOBILE MENU FUNCTIONALITY
// ========================================
function openMobileMenu() {
    const menu = document.getElementById('top-actions');
    const toggle = document.getElementById('mobile-menu-toggle');

    if (menu) {
        menu.classList.add('mobile-open');
    }
    if (toggle) {
        toggle.setAttribute('aria-expanded', 'true');
    }
    // Prevent body scroll when menu is open
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    const menu = document.getElementById('top-actions');
    const toggle = document.getElementById('mobile-menu-toggle');

    if (menu) {
        menu.classList.remove('mobile-open');
    }
    if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
    }
    // Restore body scroll
    document.body.style.overflow = '';
}

// Mobile menu event listeners
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const menuClose = document.getElementById('mobile-menu-close');
    const menu = document.getElementById('top-actions');

    // Open menu on hamburger click
    if (menuToggle) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            openMobileMenu();
        });
    }

    // Close menu on X button click
    if (menuClose) {
        menuClose.addEventListener('click', (e) => {
            e.stopPropagation();
            closeMobileMenu();
        });
    }

    // Close menu when clicking a menu link (for better UX)
    if (menu) {
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                closeMobileMenu();
            });
        });
    }
});

// Close mobile menu on window resize (when returning to desktop)
window.addEventListener('resize', () => {
    if (window.innerWidth > 680) {
        closeMobileMenu();
    }
});

// Birthdate support + fallback mask & submit conversion
(function () {
    const native = document.getElementById('birthdate-native');
    const text = document.getElementById('birthdate-text');
    const hidden = document.getElementById('birthdate-hidden');
    const form = document.querySelector('.auth-form');

    if (!native || !text || !hidden || !form) return;

    // Detect native date support
    const inputTest = document.createElement('input');
    inputTest.setAttribute('type', 'date');
    const supportsDate = inputTest.type === 'date';

    if (supportsDate) {
        native.style.display = '';
        text.style.display = 'none';
        native.addEventListener('change', () => {
            hidden.value = native.value; // native already ISO YYYY-MM-DD
        });
    } else {
        native.style.display = 'none';
        text.style.display = '';
        text.autocomplete = 'bday';
        text.title = 'Format JJ/MM/AAAA';

        // Masking: auto-insert slashes and restrict to digits
        text.addEventListener('input', () => {
            const v = text.value.replace(/\D/g, '').slice(0, 8);
            let out = v;
            if (v.length >= 5) {
                out = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
            } else if (v.length >= 3) {
                out = v.slice(0, 2) + '/' + v.slice(2);
            }
            text.value = out;
        });

        text.addEventListener('blur', () => {
            const parts = (text.value || '').split('/');
            if (parts.length === 3) {
                const dd = parseInt(parts[0], 10);
                const mm = parseInt(parts[1], 10);
                const yyyy = parseInt(parts[2], 10);
                if (isNaN(dd) || isNaN(mm) || isNaN(yyyy) || dd < 1 || dd > 31 || mm < 1 || mm > 12 || yyyy < 1900 || yyyy > 2100) {
                    text.classList.add('invalid');
                } else {
                    text.classList.remove('invalid');
                }
            } else {
                text.classList.add('invalid');
            }
        });
    }

    // On submit, ensure hidden birthdate is set to ISO YYYY-MM-DD if possible
    form.addEventListener('submit', (ev) => {
        if (supportsDate) {
            hidden.value = native.value || '';
            return;
        }

        const v = (text.value || '').trim();
        const m = v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!m) {
            if (v.length === 0) return;
            ev.preventDefault();
            alert('Veuillez saisir la date de naissance au format JJ/MM/AAAA.');
            text.focus();
            return;
        }
        const dd = parseInt(m[1], 10), mm = parseInt(m[2], 10), yyyy = parseInt(m[3], 10);
        if (mm < 1 || mm > 12 || dd < 1 || dd > 31) {
            ev.preventDefault();
            alert('Date de naissance invalide.');
            text.focus();
            return;
        }
        const iso = yyyy.toString().padStart(4, '0') + '-' + String(mm).padStart(2, '0') + '-' + String(dd).padStart(2, '0');
        hidden.value = iso;
    });
})();




