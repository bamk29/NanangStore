import './bootstrap';
import Swal from 'sweetalert2';

window.Swal = Swal;

// Global listener for Livewire->SweetAlert events
window.addEventListener('swal:alert', event => {
    Swal.fire({
        icon: event.detail.type,
        title: event.detail.title,
        html: event.detail.text,
        confirmButtonColor: '#3b82f6'
    });
});
