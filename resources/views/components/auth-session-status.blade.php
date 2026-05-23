@props(['status'])

@if ($status)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof showToast === 'function') {
                showToast("{{ $status }}", 'success');
            }
        });
    </script>
@endif
