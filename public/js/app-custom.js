/*
 DOC: Proyecto Cisternas
 app-custom.js — Versión limpia y depurada
*/

/* ============================================================
 MODAL CONSUMO
============================================================ */

window.abrirModal = function(id, hecL1, hrcL1, hecL2, hrcL2) {
    console.log('>>> abrirModal llamado con:', {id, hecL1, hrcL1, hecL2, hrcL2});
    
    const form = document.getElementById('form-consumo');
    const l1 = document.getElementById('hrc-l1');
    const l2 = document.getElementById('hrc-l2');

    if (!form) {
        console.error('ERROR: No existe #form-consumo');
        return;
    }

    form.action = '/cisterna/' + id + '/consumo';

    const infoL1 = document.getElementById('info-hec-l1');
    const infoL2 = document.getElementById('info-hec-l2');

    if (infoL1) infoL1.value = hecL1 || '';
    if (infoL2) infoL2.value = hecL2 || '';

    if (l1) l1.value = hrcL1 || '';
    if (l2) l2.value = hrcL2 || '';

    if (l1) l1.disabled = false;
    if (l2) l2.disabled = false;

    if (hrcL1 && l2) {
        l2.disabled = true;
    } else if (hrcL2 && l1) {
        l1.disabled = true;
    }

    const modalRoot = document.getElementById('modalConsumo');
    
    if (!modalRoot) {
        console.error('ERROR: No existe #modalConsumo en el DOM');
        return;
    }

    console.log('>>> Abriendo modal...');
    
    const modal = new bootstrap.Modal(modalRoot);
    modal.show();
};

/* ============================================================
 DELEGACIÓN GLOBAL — FASE DE CAPTURA (prioridad máxima)
============================================================ */

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-consumo');
    if (!btn) return;
    
    console.log('>>> Botón consumo clickeado:', btn.dataset);
    console.log('>>> Bootstrap disponible:', typeof bootstrap !== 'undefined');
    console.log('>>> Modal en DOM:', !!document.getElementById('modalConsumo'));
    
    // Verificar que Bootstrap esté cargado
    if (typeof bootstrap === 'undefined') {
        console.error('ERROR: Bootstrap no está cargado');
        alert('Error: Bootstrap no está cargado. Recarga la página.');
        return;
    }
    
    abrirModal(
        btn.dataset.id,
        btn.dataset.hecL1 || btn.dataset.hecl1,
        btn.dataset.hrcL1 || btn.dataset.hrcl1,
        btn.dataset.hecL2 || btn.dataset.hecl2,
        btn.dataset.hrcL2 || btn.dataset.hrcl2
    );
});

/* ============================================================
 DOMContentLoaded
============================================================ */

document.addEventListener('DOMContentLoaded', function() {

    /* TEMA CLARO / OSCURO */
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-toggle-icon');
    const storageKey = 'app-theme';

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark-mode', isDark);
        document.body.classList.toggle('dark-mode', isDark);

        if (themeIcon) {
            themeIcon.classList.remove('bi-moon-stars-fill', 'bi-sun-fill');
            themeIcon.classList.add(isDark ? 'bi-sun-fill' : 'bi-moon-stars-fill');
        }

        if (themeToggle) {
            themeToggle.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            themeToggle.setAttribute('title', isDark ? 'Modo oscuro activo' : 'Modo claro activo');
        }
    }

    const savedTheme = localStorage.getItem(storageKey);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : (prefersDark ? 'dark' : 'light');
    applyTheme(initialTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const isDark = document.body.classList.contains('dark-mode');
            const nextTheme = isDark ? 'light' : 'dark';
            localStorage.setItem(storageKey, nextTheme);
            applyTheme(nextTheme);
        });
    }

    /* MUTEX CAMPOS MODAL CONSUMO */
    const l1 = document.getElementById('hrc-l1');
    const l2 = document.getElementById('hrc-l2');

    if (l1 && l2) {
        l1.addEventListener('input', function() {
            if (this.value) {
                l2.value = '';
                l2.disabled = true;
            } else {
                l2.disabled = false;
            }
        });

        l2.addEventListener('input', function() {
            if (this.value) {
                l1.value = '';
                l1.disabled = true;
            } else {
                l1.disabled = false;
            }
        });
    }

    /* TOGGLE PASSWORD LOGIN */
    window.togglePassword = function() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            if (icon) icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    };

    /* GENERADOR PASSWORD */
    window.generarPassword = function() {
        const emailEl = document.getElementById('email');
        const passEl = document.getElementById('password_generada');
        const btnEl = document.getElementById('btn-crear');

        if (!emailEl || !passEl) return;

        const parte = emailEl.value.split('@')[0].toUpperCase();
        if (!parte) {
            alert('Introduce primero el email.');
            return;
        }

        const first = parte.charCodeAt(0);
        const last = parte.charCodeAt(parte.length - 1);
        const password = parte + first + last;

        passEl.value = password;
        if (btnEl) btnEl.removeAttribute('disabled');
    };

    /* MUTEX H.E.C BULK_CONFIRM */
    document.querySelectorAll('.hec-l1').forEach(function(l1c) {
        const idx = l1c.dataset.index;
        const l2c = document.querySelector('.hec-l2[data-index="' + idx + '"]');
        if (!l2c) return;

        l1c.addEventListener('input', function() {
            if (this.value) {
                l2c.value = '';
                l2c.disabled = true;
            } else {
                l2c.disabled = false;
            }
        });

        l2c.addEventListener('input', function() {
            if (this.value) {
                l1c.value = '';
                l1c.disabled = true;
            } else {
                l1c.disabled = false;
            }
        });

        if (l1c.value) {
            l2c.disabled = true;
        } else if (l2c.value) {
            l1c.disabled = true;
        }
    });

    /* SELECT TODOS */
    window.toggleTodos = function(estado) {
        document.querySelectorAll('.check-fila').forEach(function(cb) {
            cb.checked = !!estado;
        });
    };

    /* MUTEX EDIT.BLADE */
    function setupMutex(idA, idB) {
        const a = document.getElementById(idA);
        const b = document.getElementById(idB);
        if (!a || !b) return;

        if (a.value) {
            b.disabled = true;
        } else if (b.value) {
            a.disabled = true;
        }

        a.addEventListener('input', function() {
            if (this.value) {
                b.value = '';
                b.disabled = true;
            } else {
                b.disabled = false;
            }
        });

        b.addEventListener('input', function() {
            if (this.value) {
                a.value = '';
                a.disabled = true;
            } else {
                a.disabled = false;
            }
        });
    }

    setupMutex('HoraEstimadaConsumoL1', 'HoraEstimadaConsumoL2');
    setupMutex('HoraRealConsumoL1', 'HoraRealConsumoL2');

});