<!-- SweetAlert2 Global Confirm Interceptor -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Prevent sweet alert from messing with body padding/scroll when hidden */
    body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        overflow-y: hidden !important;
    }
</style>
<script>
    // We attach to 'window' in the capture phase (true) so this ALWAYS runs before Livewire's listeners
    window.addEventListener('click', function(e) {
        let el = e.target.closest('[wire\\:confirm]');
        if (!el) return;

        // If we already confirmed via SweetAlert, let it pass to Livewire
        if (el.__swal_bypassed) {
            el.__swal_bypassed = false;
            return;
        }

        // STOP the event dead in its tracks. Livewire will never see this click!
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let message = el.getAttribute('wire:confirm');
        
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
                // Set bypass flag
                el.__swal_bypassed = true;
                
                // MAGIC TRICK: Livewire 3 calls window.confirm synchronously inside its own handler.
                // By mocking it temporarily, when we re-trigger the click, Livewire will proceed without the browser popup!
                let originalConfirm = window.confirm;
                window.confirm = function() { return true; };
                
                // Fire the click again (this time it bypasses our interceptor and hits Livewire)
                el.click();
                
                // Restore original confirm after a tiny delay (to ensure Livewire's synchronous/microtask queue finished)
                setTimeout(() => {
                    window.confirm = originalConfirm;
                }, 50);
            }
        });
    }, true); // Use capture phase!
</script>
