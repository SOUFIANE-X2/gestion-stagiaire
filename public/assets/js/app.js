(function () {
    'use strict';

    var root = document.documentElement;
    var body = document.body;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------------------------------------------------------
       Thème clair / sombre
       --------------------------------------------------------------- */
    var themeToggle = document.querySelector('[data-toggle-theme]');

    function applyTheme(theme) {
        root.dataset.theme = theme;

        if (themeToggle) {
            var isDark = theme === 'dark';
            themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            themeToggle.setAttribute('aria-label', isDark ? 'Passer au thème clair' : 'Passer au thème sombre');
            themeToggle.setAttribute('title', isDark ? 'Thème clair' : 'Thème sombre');
        }
    }

    function storedTheme() {
        try {
            var value = localStorage.getItem('inarain.theme');
            return value === 'light' || value === 'dark' ? value : null;
        } catch (error) {
            return null;
        }
    }

    applyTheme(storedTheme() || 'light');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try {
                localStorage.setItem('inarain.theme', next);
            } catch (error) {
                /* stockage indisponible : le thème reste actif pour la page */
            }
        });
    }

    /* ---------------------------------------------------------------
       Navigation mobile
       --------------------------------------------------------------- */
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.querySelector('[data-toggle-sidebar]');
    var sidebarBackdrop = document.querySelector('[data-close-sidebar]');

    function setSidebar(open) {
        if (!sidebar || !sidebarToggle) {
            return;
        }

        body.classList.toggle('sidebar-open', open);
        sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open) {
            var firstLink = sidebar.querySelector('a, button');
            if (firstLink) {
                window.setTimeout(function () {
                    firstLink.focus({ preventScroll: true });
                }, 120);
            }
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            setSidebar(!body.classList.contains('sidebar-open'));
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            setSidebar(false);
        });
    }

    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 980px)').matches) {
                    setSidebar(false);
                }
            });
        });
    }

    /* ---------------------------------------------------------------
       Dialogues natifs
       --------------------------------------------------------------- */
    var activeDialog = null;
    var dialogOpener = null;

    function closeDialog(dialog) {
        if (dialog && typeof dialog.close === 'function' && dialog.open) {
            dialog.close();
        }
    }

    document.querySelectorAll('[data-open-dialog]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var dialog = document.getElementById(trigger.getAttribute('data-open-dialog'));
            if (!dialog || typeof dialog.showModal !== 'function') {
                return;
            }

            dialogOpener = trigger;

            var form = dialog.querySelector('[data-justify-form]');
            if (form && trigger.hasAttribute('data-justify-url')) {
                form.setAttribute('action', trigger.getAttribute('data-justify-url'));
                var name = dialog.querySelector('[data-justify-name]');
                var date = dialog.querySelector('[data-justify-date]');
                if (name) {
                    name.textContent = trigger.getAttribute('data-justify-name') || '';
                }
                if (date) {
                    date.textContent = trigger.getAttribute('data-justify-date') || '';
                }
                var textarea = dialog.querySelector('textarea');
                if (textarea) {
                    textarea.value = '';
                }
            }

            activeDialog = dialog;
            dialog.showModal();
            body.classList.add('dialog-open');

            var focusTarget = dialog.querySelector('select, input, textarea, button');
            if (focusTarget) {
                window.setTimeout(function () {
                    focusTarget.focus({ preventScroll: true });
                }, 60);
            }
        });
    });

    document.querySelectorAll('dialog').forEach(function (dialog) {
        dialog.querySelectorAll('[data-close-dialog]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeDialog(dialog);
            });
        });

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                closeDialog(dialog);
            }
        });

        dialog.addEventListener('close', function () {
            body.classList.remove('dialog-open');

            if (activeDialog === dialog) {
                activeDialog = null;
            }

            if (dialogOpener) {
                dialogOpener.focus({ preventScroll: true });
                dialogOpener = null;
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (activeDialog && activeDialog.open) {
            closeDialog(activeDialog);
            return;
        }

        if (body.classList.contains('sidebar-open')) {
            setSidebar(false);
        }
    });

    /* ---------------------------------------------------------------
       Confirmations de suppression
       --------------------------------------------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm') || 'Confirmer cette action ?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    /* ---------------------------------------------------------------
       Afficher / masquer le mot de passe
       --------------------------------------------------------------- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('aria-controls');
            var input = targetId ? document.getElementById(targetId) : button.parentElement.querySelector('input[data-password-input]');

            if (!input) {
                return;
            }

            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-label', reveal ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    });

    /* ---------------------------------------------------------------
       Filtre groupe dépendant de la filière
       --------------------------------------------------------------- */
    var filiereFilter = document.querySelector('[data-filter-filiere]');
    var groupFilter = document.querySelector('[data-filter-group]');

    function syncGroupOptions() {
        if (!filiereFilter || !groupFilter) {
            return;
        }

        var filiere = filiereFilter.value;
        var selectedOption = groupFilter.options[groupFilter.selectedIndex];
        var selectedVisible = false;

        Array.prototype.forEach.call(groupFilter.options, function (option) {
            if (!option.value) {
                return;
            }

            var matches = !filiere || option.getAttribute('data-filiere') === filiere;
            option.hidden = !matches;
            option.disabled = !matches;

            if (option.selected && matches) {
                selectedVisible = true;
            }
        });

        if (selectedOption && selectedOption.value && !selectedVisible) {
            groupFilter.value = '';
        }
    }

    if (filiereFilter) {
        filiereFilter.addEventListener('change', syncGroupOptions);
        syncGroupOptions();
    }

    /* ---------------------------------------------------------------
       Messages flash
       --------------------------------------------------------------- */
    document.querySelectorAll('[data-dismiss]').forEach(function (button) {
        button.addEventListener('click', function () {
            var alert = button.closest('.alert');
            if (alert) {
                alert.remove();
            }
        });
    });

    document.querySelectorAll('.flash-stack .alert').forEach(function (alert) {
        window.setTimeout(function () {
            if (!alert.isConnected) {
                return;
            }

            alert.style.transition = reduceMotion ? 'none' : 'opacity .25s ease, transform .25s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';

            window.setTimeout(function () {
                alert.remove();
            }, reduceMotion ? 0 : 250);
        }, 7000);
    });

    /* ---------------------------------------------------------------
       Impression
       --------------------------------------------------------------- */
    document.querySelectorAll('[data-print]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.print();
        });
    });
})();
