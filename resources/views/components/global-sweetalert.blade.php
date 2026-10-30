<!-- SweetAlert2 Global Modal & Toast System -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Prevent sweet alert from messing with body padding/scroll when hidden */
    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow-y: hidden !important;
    }
    .swal2-popup.swal2-toast {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        border-radius: 0.75rem !important;
    }
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<script>
(function() {
    // 1. Toast Notification Helper
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    window.toast = function(message, type = 'success') {
        Toast.fire({
            icon: type,
            title: message
        });
    };

    const handleToastEvent = (e) => {
        let payload = Array.isArray(e.detail) && e.detail.length > 0 ? e.detail[0] : e.detail;
        if (!payload) return;
        Toast.fire({
            icon: payload.type || 'info',
            title: payload.message || payload.title || (typeof payload === 'string' ? payload : 'Pemberitahuan')
        });
    };

    window.addEventListener('toast', handleToastEvent);
    window.addEventListener('notify', handleToastEvent);
    window.addEventListener('alert', handleToastEvent);

    // 2. Global In-App Alert Override (Replaces native browser "mstore.mstore.id says")
    window.alert = function(message) {
        Swal.fire({
            title: 'Pemberitahuan',
            text: String(message ?? ''),
            icon: 'info',
            confirmButtonText: 'Tutup',
            customClass: {
                confirmButton: 'px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors border-0'
            },
            buttonsStyling: false
        });
    };

    // 3. Global swalConfirm helper for async / programmatic confirmations
    window.swalConfirm = function(options) {
        const config = typeof options === 'string' ? { text: options } : (options || {});
        return Swal.fire({
            title: config.title || 'Konfirmasi',
            text: config.text || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            icon: config.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: config.confirmButtonColor || '#ef4444',
            cancelButtonColor: config.cancelButtonColor || '#64748b',
            confirmButtonText: config.confirmButtonText || 'Ya, Lanjutkan!',
            cancelButtonText: config.cancelButtonText || 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors border-0',
                cancelButton: 'px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors border-0 mr-2'
            },
            buttonsStyling: false
        });
    };

    // 4. Global Click Interceptor for wire:confirm AND onclick="confirm(...)"
    window.addEventListener('click', function(e) {
        // Find nearest element with wire:confirm OR onclick containing confirm
        let el = e.target.closest('[wire\\:confirm], [onclick*="confirm("], [onclick*="confirm ("]');
        if (!el) return;

        // If bypass flag is active, allow click through to target / Livewire
        if (el.__swal_bypassed) {
            el.__swal_bypassed = false;
            return;
        }

        // Intercept and prevent the default browser action / inline handler
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        // Extract message
        let message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        if (el.hasAttribute('wire:confirm')) {
            message = el.getAttribute('wire:confirm');
        } else if (el.hasAttribute('onclick')) {
            const onclickCode = el.getAttribute('onclick');
            const match = onclickCode.match(/confirm\s*\(\s*(['"`])(.*?)\1\s*\)/);
            if (match && match[2]) {
                message = match[2];
            }
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors border-0',
                cancelButton: 'px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors border-0 mr-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                el.__swal_bypassed = true;
                
                // Temporarily mock window.confirm so that if inline onclick or Livewire calls confirm(), it passes without browser dialog
                const originalConfirm = window.confirm;
                window.confirm = function() { return true; };
                
                el.click();
                
                setTimeout(() => {
                    window.confirm = originalConfirm;
                }, 100);
            }
        });
    }, true); // Capture phase is critical!

    // 5. Global Form Submit Interceptor for onsubmit="...confirm(...)..."
    window.addEventListener('submit', function(e) {
        let form = e.target;
        if (!form || !form.getAttribute) return;
        let onsubmitAttr = form.getAttribute('onsubmit') || '';
        if (!onsubmitAttr.includes('confirm(') && !onsubmitAttr.includes('confirm (')) return;

        if (form.__swal_bypassed) {
            form.__swal_bypassed = false;
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const match = onsubmitAttr.match(/confirm\s*\(\s*(['"`])(.*?)\1\s*\)/);
        const message = match && match[2] ? match[2] : 'Apakah Anda yakin ingin memproses form ini?';

        Swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                confirmButton: 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors border-0',
                cancelButton: 'px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors border-0 mr-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.__swal_bypassed = true;
                const originalConfirm = window.confirm;
                window.confirm = function() { return true; };
                try {
                    HTMLFormElement.prototype.submit.call(form);
                } catch (err) {
                    form.submit();
                }
                setTimeout(() => {
                    window.confirm = originalConfirm;
                }, 100);
            }
        });
    }, true);

    // 6. Global Clipboard copy helper
    window.copyToClipboard = function(text, successMessage = 'Berhasil disalin ke clipboard!') {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                window.toast(successMessage, 'success');
            }).catch(() => {
                fallbackCopy(text, successMessage);
            });
        } else {
            fallbackCopy(text, successMessage);
        }
    };

    function fallbackCopy(text, successMessage) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            window.toast(successMessage, 'success');
        } catch (err) {
            console.error('Copy fallback failed:', err);
        }
        textArea.remove();
    }

    // 7. Flash Messages Display
    function checkFlashes() {
        @if(session()->has('success'))
            Toast.fire({ icon: 'success', title: '{!! addslashes(session('success')) !!}' });
        @endif
        @if(session()->has('error'))
            Toast.fire({ icon: 'error', title: '{!! addslashes(session('error')) !!}' });
        @endif
        @if(session()->has('warning'))
            Toast.fire({ icon: 'warning', title: '{!! addslashes(session('warning')) !!}' });
        @endif
        @if(session()->has('info'))
            Toast.fire({ icon: 'info', title: '{!! addslashes(session('info')) !!}' });
        @endif
    }

    document.addEventListener('DOMContentLoaded', checkFlashes);
    document.addEventListener('livewire:navigated', checkFlashes);
})();
</script>
