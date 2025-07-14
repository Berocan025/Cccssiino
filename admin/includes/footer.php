        </div> <!-- End container-fluid -->
    </div> <!-- End main-content -->

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle Function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Mobile Sidebar Toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Restore sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
            if (sidebarCollapsed === 'true') {
                document.getElementById('sidebar').classList.add('collapsed');
            }

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in-up');
                }, index * 100);
            });
        });

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isToggleButton = event.target.closest('.mobile-toggle');
            
            if (!isClickInsideSidebar && !isToggleButton && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
        });

        // DataTable initialization function
        function initDataTable(tableId, options = {}) {
            if (typeof $.fn.DataTable !== 'undefined') {
                const defaultOptions = {
                    pageLength: 10,
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
                    },
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    ...options
                };
                
                return $(tableId).DataTable(defaultOptions);
            }
        }

        // Confirm delete function
        function confirmDelete(title = 'Silme Onayı', message = 'Bu işlem geri alınamaz!') {
            return confirm(`${title}\n\n${message}\n\nDevam etmek istediğinizden emin misiniz?`);
        }

        // Show loading overlay
        function showLoading() {
            if (!document.querySelector('.loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                `;
                overlay.innerHTML = `
                    <div class="spinner-border text-warning" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
        }

        // Hide loading overlay
        function hideLoading() {
            const overlay = document.querySelector('.loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            
            const toastId = 'toast-' + Date.now();
            const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-warning';
            
            const toastHTML = `
                <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        }

        // AJAX form submit function
        function submitAjaxForm(formId, successCallback, errorCallback) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                showLoading();

                fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showToast(data.message || 'İşlem başarılı!', 'success');
                        if (successCallback) successCallback(data);
                    } else {
                        showToast(data.message || 'Bir hata oluştu!', 'error');
                        if (errorCallback) errorCallback(data);
                    }
                })
                .catch(error => {
                    hideLoading();
                    showToast('Bir hata oluştu!', 'error');
                    console.error('Error:', error);
                    if (errorCallback) errorCallback(error);
                });
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + Shift + S = Toggle sidebar
            if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                e.preventDefault();
                toggleSidebar();
            }
            
            // ESC = Close modals and dropdowns
            if (e.key === 'Escape') {
                // Close all modals
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                });
                
                // Close all dropdowns
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(dropdown => {
                    const toggle = dropdown.previousElementSibling;
                    if (toggle) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(toggle);
                        if (bsDropdown) bsDropdown.hide();
                    }
                });
            }
        });

        // Auto-save draft functionality
        function enableAutoSave(formId, saveUrl, interval = 30000) {
            const form = document.getElementById(formId);
            if (!form) return;

            let autoSaveTimer;
            
            function saveDraft() {
                const formData = new FormData(form);
                formData.append('action', 'save_draft');
                
                fetch(saveUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const indicator = document.querySelector('.auto-save-indicator');
                        if (indicator) {
                            indicator.textContent = 'Taslak kaydedildi: ' + new Date().toLocaleTimeString('tr-TR');
                            indicator.className = 'auto-save-indicator text-success small';
                        }
                    }
                })
                .catch(error => console.error('Auto-save error:', error));
            }

            form.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveDraft, interval);
                
                const indicator = document.querySelector('.auto-save-indicator');
                if (indicator) {
                    indicator.textContent = 'Değişiklikler kaydediliyor...';
                    indicator.className = 'auto-save-indicator text-warning small';
                }
            });
        }

        // Dark/Light theme toggle (for future enhancement)
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            showToast(`${newTheme === 'dark' ? 'Karanlık' : 'Aydınlık'} tema aktif!`, 'success');
        }

        // Initialize theme from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
            }
        });
    </script>
</body>
</html>