/*
 * Proyecto Cisternas - JavaScript Consolidado
 * Contiene todos los scripts personalizados de la aplicación
 */

/* ============================================================
   Gestión de tema (claro/oscuro)
   ============================================================ */
(function() {
    'use strict';
    
    // Función para inicializar el tema
    function initTheme() {
        try {
            var theme = localStorage.getItem('app-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var isDark = theme === 'dark' || (!theme && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {
            console.error('Error al inicializar tema:', e);
        }
    }
    
    // Inicializar tema al cargar la página
    initTheme();
    
    // Toggle de tema
    var themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            var html = document.documentElement;
            var icon = document.getElementById('theme-toggle-icon');
            
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('app-theme', 'light');
                if (icon) {
                    icon.className = 'bi bi-moon-stars-fill';
                }
            } else {
                html.classList.add('dark');
                localStorage.setItem('app-theme', 'dark');
                if (icon) {
                    icon.className = 'bi bi-sun-fill';
                }
            }
        });
    }
})();

/* ============================================================
   Modal de consumo (cisterna/index)
   ============================================================ */
/**
 * Abre el modal de consumo y prepara sus campos para una cisterna concreta.
 * @param {number|string} id Identificador de la cisterna.
 * @param {string|null} hecL1 Hora estimada de consumo linea 1.
 * @param {string|null} hrcL1 Hora real de consumo linea 1.
 * @param {string|null} hecL2 Hora estimada de consumo linea 2.
 * @param {string|null} hrcL2 Hora real de consumo linea 2.
 */
function abrirModal(id, hecL1, hrcL1, hecL2, hrcL2) {
    'use strict';
    
    const form = document.getElementById('form-consumo');
    const l1 = document.getElementById('hrc-l1');
    const l2 = document.getElementById('hrc-l2');

    if (!form) return;

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
    if (!modalRoot) return;

    const modal = new bootstrap.Modal(modalRoot);
    modal.show();
}

// Inicializar botones de consumo cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Botones de consumo en la tabla
    document.querySelectorAll('.btn-consumo').forEach(function(btn) {
        btn.addEventListener('click', function() {
            abrirModal(
                this.dataset.id,
                this.dataset.hecL1,
                this.dataset.hrcL1,
                this.dataset.hecL2,
                this.dataset.hrcL2
            );
        });
    });
});

/* ============================================================
   Lógica de campos mutuamente excluyentes (cisterna/edit)
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Función para configurar campos mutuamente excluyentes
    function setupMutuallyExclusiveFields(field1Id, field2Id) {
        const field1 = document.getElementById(field1Id);
        const field2 = document.getElementById(field2Id);
        
        if (!field1 || !field2) return;
        
        // Estado inicial
        if (field1.value) {
            field2.disabled = true;
        } else if (field2.value) {
            field1.disabled = true;
        }
        
        // Event listeners
        field1.addEventListener('input', function() {
            if (this.value) {
                field2.value = '';
                field2.disabled = true;
            } else {
                field2.disabled = false;
            }
        });
        
        field2.addEventListener('input', function() {
            if (this.value) {
                field1.value = '';
                field1.disabled = true;
            } else {
                field1.disabled = false;
            }
        });
    }
    
    // Configurar pares de campos
    setupMutuallyExclusiveFields('HoraEstimadaConsumoL1', 'HoraEstimadaConsumoL2');
    setupMutuallyExclusiveFields('HoraRealConsumoL1', 'HoraRealConsumoL2');
});

/* ============================================================
   Funciones para edición masiva (cisterna/bulk_confirm)
   ============================================================ */
function toggleTodos(checked) {
    'use strict';
    document.querySelectorAll('.check-fila').forEach(function(checkbox) {
        checkbox.checked = !!checked;
    });
}

function collectRowsData() {
    'use strict';
    const rows = {};
    const fields = document.querySelectorAll('#bulk-edit-form input[name^="filas["], #bulk-edit-form textarea[name^="filas["], #bulk-edit-form select[name^="filas["]');

    fields.forEach(function(el) {
        const match = el.name.match(/^filas\[(\d+)\]\[([^\]]+)\]$/);
        if (!match) return;

        const index = match[1];
        const key = match[2];

        if (!rows[index]) rows[index] = {};

        if (el.type === 'checkbox') {
            rows[index][key] = el.checked ? '1' : '';
        } else {
            rows[index][key] = el.value;
        }
    });

    return rows;
}

function submitImportAll() {
    'use strict';
    const hidden = document.getElementById('edited_rows_json_all');
    if (hidden) {
        hidden.value = JSON.stringify(collectRowsData());
        document.getElementById('import-all-form').submit();
    }
}

// Inicializar formulario de edición masiva
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const bulkEditForm = document.getElementById('bulk-edit-form');
    if (bulkEditForm) {
        bulkEditForm.addEventListener('submit', function() {
            const hidden = document.getElementById('edited_rows_json_selected');
            if (hidden) {
                hidden.value = JSON.stringify(collectRowsData());
            }
        });
    }
});

/* ============================================================
   Generador de contraseñas (admin/create)
   ============================================================ */
function generarPasswordDesdeEmail(email) {
    'use strict';
    const local = (email || '').split('@')[0] || '';
    if (!local) return '';

    const upper = local.toUpperCase();
    const firstAscii = upper.charCodeAt(0);
    const lastAscii = upper.charCodeAt(upper.length - 1);

    return `${upper}${firstAscii}${lastAscii}`;
}

// Inicializar formulario de creación de usuario
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.getElementById('password_generada');
    const btnGenerar = document.getElementById('btn-generar-pwd');
    const btnToggle = document.getElementById('btn-toggle-pwd');
    
    if (btnGenerar && emailInput && passwordInput) {
        btnGenerar.addEventListener('click', function() {
            const pass = generarPasswordDesdeEmail(emailInput.value.trim());
            if (!pass) {
                alert('Por favor, introduce un email válido');
                return;
            }
            passwordInput.value = pass;
            passwordInput.type = 'text';
        });
    }
    
    if (btnToggle && passwordInput) {
        btnToggle.addEventListener('click', function() {
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            btnToggle.textContent = passwordInput.type === 'password' ? 'Ver' : 'Ocultar';
        });
    }
});

/* ============================================================
   Toggle de contraseña (admin/show)
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const pwdInput = document.getElementById('generated_password');
    const btnTogglePwd = document.getElementById('btn-toggle-password');
    
    if (btnTogglePwd && pwdInput) {
        btnTogglePwd.addEventListener('click', function() {
            pwdInput.type = pwdInput.type === 'password' ? 'text' : 'password';
            btnTogglePwd.textContent = pwdInput.type === 'password' ? 'Ver' : 'Ocultar';
        });
    }
});

/* ============================================================
   Toggle de contraseña login (auth/login)
   ============================================================ */
function togglePassword() {
    'use strict';
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

/* ============================================================
   Utilidades generales
   ============================================================ */
// Función para mostrar/ocultar elementos
function toggleElement(elementId) {
    'use strict';
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

// Función para confirmar acciones
function confirmAction(message) {
    'use strict';
    return confirm(message || '¿Estás seguro de realizar esta acción?');
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
    'use strict';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            console.log('Texto copiado al portapapeles');
        }).catch(function(err) {
            console.error('Error al copiar texto:', err);
        });
    } else {
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Error al copiar texto:', err);
        }
        document.body.removeChild(textArea);
    }
}

/* ============================================================
   Auto-ocultar alertas
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
});

/* ============================================================
   Inicialización general
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('Aplicación Cisternas inicializada');
    
    // Detectar preferencia de tema inicial
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    prefersDark.addListener(function(e) {
        if (!localStorage.getItem('app-theme')) {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });
});
