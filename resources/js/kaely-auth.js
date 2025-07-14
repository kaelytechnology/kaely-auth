/**
 * KaelyAuth UI Components JavaScript
 */

(function() {
    'use strict';

    // KaelyAuth namespace
    window.KaelyAuth = window.KaelyAuth || {};

    // Utility functions
    KaelyAuth.utils = {
        /**
         * Show alert message
         */
        showAlert: function(message, type = 'info', duration = 5000) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `kaely-auth-alert kaely-auth-alert-${type}`;
            alertDiv.textContent = message;
            
            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = 'float: right; background: none; border: none; font-size: 18px; cursor: pointer;';
            closeBtn.onclick = function() {
                alertDiv.remove();
            };
            
            alertDiv.appendChild(closeBtn);
            
            // Insert at top of container
            const container = document.querySelector('.kaely-auth-container') || document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, duration);
            }
        },

        /**
         * Validate form
         */
        validateForm: function(form) {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            return isValid;
        },

        /**
         * Format date
         */
        formatDate: function(date) {
            return new Date(date).toLocaleDateString();
        },

        /**
         * Format datetime
         */
        formatDateTime: function(date) {
            return new Date(date).toLocaleString();
        }
    };

    // Form handling
    KaelyAuth.forms = {
        /**
         * Initialize form handlers
         */
        init: function() {
            document.addEventListener('submit', function(e) {
                if (e.target.classList.contains('kaely-auth-form')) {
                    KaelyAuth.forms.handleSubmit(e);
                }
            });
        },

        /**
         * Handle form submission
         */
        handleSubmit: function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (!KaelyAuth.utils.validateForm(form)) {
                KaelyAuth.utils.showAlert('Please fill in all required fields.', 'error');
                return;
            }
            
            // Disable submit button
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
            
            // Submit form via AJAX
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    KaelyAuth.utils.showAlert(data.message || 'Success!', 'success');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    KaelyAuth.utils.showAlert(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(error => {
                KaelyAuth.utils.showAlert('An error occurred while processing your request.', 'error');
                console.error('Form submission error:', error);
            })
            .finally(() => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
                }
            });
        }
    };

    // Table functionality
    KaelyAuth.tables = {
        /**
         * Initialize table handlers
         */
        init: function() {
            // Add sorting functionality
            document.querySelectorAll('.kaely-auth-table th[data-sort]').forEach(th => {
                th.style.cursor = 'pointer';
                th.addEventListener('click', function() {
                    KaelyAuth.tables.sortTable(this);
                });
            });
        },

        /**
         * Sort table by column
         */
        sortTable: function(header) {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const columnIndex = Array.from(header.parentNode.children).indexOf(header);
            const isAscending = header.classList.contains('sort-asc');
            
            // Clear previous sort classes
            header.parentNode.querySelectorAll('th').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Add sort class
            header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
            
            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.children[columnIndex].textContent.trim();
                const bValue = b.children[columnIndex].textContent.trim();
                
                if (isAscending) {
                    return bValue.localeCompare(aValue);
                } else {
                    return aValue.localeCompare(bValue);
                }
            });
            
            // Reorder rows
            rows.forEach(row => tbody.appendChild(row));
        }
    };

    // Navigation functionality
    KaelyAuth.navigation = {
        /**
         * Initialize navigation
         */
        init: function() {
            // Handle active navigation links
            const currentPath = window.location.pathname;
            document.querySelectorAll('.kaely-auth-nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        KaelyAuth.forms.init();
        KaelyAuth.tables.init();
        KaelyAuth.navigation.init();
    });

})(); 