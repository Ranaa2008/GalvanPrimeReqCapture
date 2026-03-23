<!-- Toast Container -->
<div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2" style="pointer-events: none;">
    <!-- Toasts will be inserted here dynamically -->
</div>

<script>
    // Toast notification system
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toastId = 'toast-' + Date.now();
        
        // Icon based on type
        let icon = '';
        if (type === 'success') {
            icon = `<svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>`;
        } else if (type === 'error') {
            icon = `<svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>`;
        } else if (type === 'warning') {
            icon = `<svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>`;
        } else if (type === 'info') {
            icon = `<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`;
        }
        
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'toast-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-4 flex items-center gap-3 min-w-[300px] max-w-md transform transition-all duration-300 ease-out translate-x-full opacity-0';
        toast.style.pointerEvents = 'auto';
        
        toast.innerHTML = `
            <div class="flex-shrink-0">
                ${icon}
            </div>
            <div class="flex-1 text-sm text-gray-900 dark:text-white">
                ${message}
            </div>
            <button onclick="closeToast('${toastId}')" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            closeToast(toastId);
        }, 5000);
    };
    
    window.closeToast = function(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    };
    
    // Show toasts from session on page load
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        
        @if (session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        
        @if (session('warning'))
            showToast("{{ session('warning') }}", 'warning');
        @endif
        
        @if (session('info'))
            showToast("{{ session('info') }}", 'info');
        @endif
        
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                showToast("{{ $error }}", 'error');
            @endforeach
        @endif
    });
</script>
