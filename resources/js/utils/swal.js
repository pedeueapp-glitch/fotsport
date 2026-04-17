import Swal from 'sweetalert2';

export const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

export const confirm = (title, text, icon = 'warning') => {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim',
        cancelButtonText: 'Cancelar'
    });
};

export const alert = (title, text, icon = 'info') => {
    return Swal.fire({
        title,
        text,
        icon,
        confirmButtonColor: '#3085d6',
    });
};

export default Swal;
