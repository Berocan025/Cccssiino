            </div>
        </main>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Save state in localStorage
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
        
        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarCollapsed = localStorage.getItem('sidebar-collapsed');
            if (sidebarCollapsed === 'true') {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        });
        
        // Show user menu
        function showUserMenu() {
            // Simple logout confirmation
            if (confirm('Çıkış yapmak istediğinize emin misiniz?')) {
                window.location.href = 'logout.php';
            }
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert.classList.contains('show')) {
                        alert.classList.remove('show');
                        setTimeout(function() {
                            alert.remove();
                        }, 150);
                    }
                }, 5000);
            });
        });
        
        // CSRF token for AJAX requests
        const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        
        // Helper function for AJAX requests
        function ajaxRequest(url, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    callback(xhr.responseText);
                }
            };
            
            // Add CSRF token to data
            if (data) {
                data += '&csrf_token=' + encodeURIComponent(csrfToken);
            } else {
                data = 'csrf_token=' + encodeURIComponent(csrfToken);
            }
            
            xhr.send(data);
        }
        
        // Confirm delete actions
        function confirmDelete(message) {
            return confirm(message || 'Bu öğeyi silmek istediğinize emin misiniz?');
        }
        
        // File upload preview
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            let isValid = true;
            
            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        }
        
        // Text editor initialization (if needed)
        function initTinyMCE(selector) {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: selector,
                    height: 300,
                    plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    toolbar_mode: 'floating',
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    content_css: '//www.tiny.cloud/css/codepen.min.css'
                });
            }
        }
        
        // Auto-save functionality
        function autoSave(formId, saveUrl) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(function(input) {
                input.addEventListener('blur', function() {
                    const formData = new FormData(form);
                    formData.append('auto_save', '1');
                    
                    fetch(saveUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Otomatik kaydedildi', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Auto-save error:', error);
                    });
                });
            });
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(function() {
                notification.classList.remove('show');
                setTimeout(function() {
                    notification.remove();
                }, 150);
            }, 3000);
        }
        
        // Initialize data tables if available
        function initDataTable(tableId) {
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#' + tableId).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const saveBtn = document.querySelector('button[type="submit"]');
                if (saveBtn) {
                    saveBtn.click();
                }
            }
            
            // Escape to cancel
            if (e.key === 'Escape') {
                const cancelBtn = document.querySelector('.btn-secondary');
                if (cancelBtn) {
                    cancelBtn.click();
                }
            }
        });
        
        // Check for updates every 30 seconds
        setInterval(function() {
            if (navigator.onLine) {
                fetch('check_updates.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.new_messages > 0) {
                            const messagesLink = document.querySelector('a[href*="messages"]');
                            if (messagesLink) {
                                messagesLink.innerHTML = messagesLink.innerHTML.replace(/\d+/, data.new_messages);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Update check error:', error);
                    });
            }
        }, 30000);
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Mobile menu handling
        function handleMobileMenu() {
            if (window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        }
        
        window.addEventListener('resize', handleMobileMenu);
        document.addEventListener('DOMContentLoaded', handleMobileMenu);
        
        // Chart.js initialization helper
        function initChart(canvasId, type, data, options = {}) {
            if (typeof Chart !== 'undefined') {
                const ctx = document.getElementById(canvasId).getContext('2d');
                return new Chart(ctx, {
                    type: type,
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        ...options
                    }
                });
            }
        }
        
        // Export functionality
        function exportData(format, url) {
            const link = document.createElement('a');
            link.href = url + '?format=' + format;
            link.download = 'export_' + Date.now() + '.' + format;
            link.click();
        }
        
        // Search functionality
        function initSearch(inputId, targetClass) {
            const searchInput = document.getElementById(inputId);
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.' + targetClass);
                    
                    items.forEach(function(item) {
                        const text = item.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        }
        
        // Drag and drop file upload
        function initDragDrop(dropZoneId, fileInputId, callback) {
            const dropZone = document.getElementById(dropZoneId);
            const fileInput = document.getElementById(fileInputId);
            
            if (dropZone && fileInput) {
                dropZone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });
                
                dropZone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                });
                
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        if (callback) callback(files);
                    }
                });
                
                dropZone.addEventListener('click', function() {
                    fileInput.click();
                });
            }
        }
        
        // Status indicator
        function updateStatus(online) {
            const statusIndicator = document.getElementById('status-indicator');
            if (statusIndicator) {
                statusIndicator.className = online ? 'status-online' : 'status-offline';
                statusIndicator.title = online ? 'Online' : 'Offline';
            }
        }
        
        // Check online status
        window.addEventListener('online', () => updateStatus(true));
        window.addEventListener('offline', () => updateStatus(false));
        document.addEventListener('DOMContentLoaded', () => updateStatus(navigator.onLine));
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const perfData = performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            
            if (pageLoadTime > 3000) {
                console.warn('Page load time is slow:', pageLoadTime + 'ms');
            }
        });
        
        // Accessibility improvements
        document.addEventListener('DOMContentLoaded', function() {
            // Add skip link
            const skipLink = document.createElement('a');
            skipLink.href = '#main-content';
            skipLink.className = 'sr-only sr-only-focusable';
            skipLink.textContent = 'Ana içeriğe geç';
            document.body.insertBefore(skipLink, document.body.firstChild);
            
            // Focus management
            const focusableElements = document.querySelectorAll('a[href], button, input, textarea, select, [tabindex]');
            focusableElements.forEach(function(element) {
                element.addEventListener('focus', function() {
                    this.classList.add('focus-visible');
                });
                
                element.addEventListener('blur', function() {
                    this.classList.remove('focus-visible');
                });
            });
        });
        
        // Custom admin functions
        window.AdminPanel = {
            showSuccess: function(message) {
                showNotification(message, 'success');
            },
            
            showError: function(message) {
                showNotification(message, 'danger');
            },
            
            confirmAction: function(message, callback) {
                if (confirm(message)) {
                    callback();
                }
            },
            
            redirect: function(url) {
                window.location.href = url;
            },
            
            reload: function() {
                window.location.reload();
            }
        };
    </script>
</body>
</html>