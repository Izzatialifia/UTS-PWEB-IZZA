// script.js - Full JavaScript untuk validasi & interaksi

document.addEventListener('DOMContentLoaded', function () {
    // Validasi form saat submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredInputs = form.querySelectorAll('input[required]');
            let isEmpty = false;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isEmpty = true;
                    input.style.borderColor = '#e74c3c';
                } else {
                    input.style.borderColor = '#ccc';
                }
            });

            if (isEmpty) {
                e.preventDefault();
                alert('Harap isi semua field yang bertanda wajib (*)!');
            }
        });
    });

    // Konfirmasi hapus (tanpa inline onclick)
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('⚠️ Yakin ingin menghapus data ini?\nAksi ini tidak bisa dibatalkan!')) {
                e.preventDefault();
            }
        });
    });

    // Efek tombol saat hover (opsional)
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', () => btn.style.transform = 'scale(1.03)');
        btn.addEventListener('mouseleave', () => btn.style.transform = 'scale(1)');
    });
});