/**
 * Global AJAX Helper for SIPTP
 * Mengelola request AJAX, CSRF Token, SweetAlert2, dan File Upload
 */

// Setup AJAX CSRF Token secara global
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * Helper untuk request AJAX standar (JSON payload)
 * Konfirmasi SweetAlert SELALU muncul kecuali confirmBefore diset false secara eksplisit.
 *
 * @param {Object} options
 * @param {string} options.url               - Endpoint URL
 * @param {string} [options.method=POST]     - HTTP Method
 * @param {Object} [options.data={}]         - Payload data (JSON)
 * @param {Function} options.successCallback - Callback saat sukses (menerima response)
 * @param {Function} [options.errorCallback] - Callback saat error (opsional)
 * @param {boolean} [options.confirmBefore=true]  - Tampilkan konfirmasi sebelum request
 * @param {string}  [options.confirmMessage]      - Pesan konfirmasi
 * @param {string}  [options.confirmTitle]        - Judul konfirmasi
 */
function ajaxRequest(options) {
    const showConfirm = options.confirmBefore !== false; // default: true

    const executeAjax = () => {
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: options.url,
            type: options.method || 'POST',
            data: options.data || {},
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Operasi berhasil dilakukan',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof options.successCallback === 'function') {
                            options.successCallback(response);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function (xhr) {
                _handleAjaxError(xhr, options.errorCallback);
            }
        });
    };

    if (showConfirm) {
        Swal.fire({
            title: options.confirmTitle || 'Apakah Anda Yakin?',
            text: options.confirmMessage || 'Tindakan ini tidak dapat dibatalkan!',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#435ebe',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                executeAjax();
            }
        });
    } else {
        executeAjax();
    }
}

/**
 * Helper untuk submit FORM dengan file upload via AJAX (menggunakan FormData)
 * Mendukung upload file (multipart/form-data).
 * Konfirmasi SweetAlert SELALU muncul kecuali confirmBefore diset false secara eksplisit.
 *
 * @param {Object} options
 * @param {string}   options.url               - Endpoint URL
 * @param {FormData} options.formData          - FormData object dari form element
 * @param {Function} options.successCallback   - Callback saat sukses
 * @param {Function} [options.errorCallback]   - Callback saat error (opsional)
 * @param {boolean}  [options.confirmBefore=true]  - Tampilkan konfirmasi (default: true)
 * @param {string}   [options.confirmMessage]  - Pesan konfirmasi
 * @param {string}   [options.confirmTitle]    - Judul konfirmasi
 * @param {string}   [options.loadingText]     - Teks loading (opsional)
 */
function ajaxFormSubmit(options) {
    const showConfirm = options.confirmBefore !== false; // default: true

    const executeUpload = () => {
        Swal.fire({
            title: 'Memproses...',
            text: options.loadingText || 'Mohon tunggu, data sedang dikirim...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: options.url,
            type: 'POST',
            data: options.formData,
            processData: false,   // Jangan diproses jQuery
            contentType: false,   // Biarkan browser set boundary multipart
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Data berhasil disimpan',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        if (typeof options.successCallback === 'function') {
                            options.successCallback(response);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function (xhr) {
                _handleAjaxError(xhr, options.errorCallback);
            }
        });
    };

    if (showConfirm) {
        Swal.fire({
            title: options.confirmTitle || 'Apakah Anda Yakin?',
            text: options.confirmMessage || 'Data akan disimpan ke sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#435ebe',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                executeUpload();
            }
        });
    } else {
        executeUpload();
    }
}

/**
 * Internal: Handle AJAX error response
 */
function _handleAjaxError(xhr, errorCallback) {
    let errorMessage = 'Terjadi kesalahan pada server';

    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
        let errors = xhr.responseJSON.errors;
        let errorHtml = '<ul style="text-align: left; margin-top: 10px;">';
        for (let key in errors) {
            errorHtml += `<li>${errors[key][0]}</li>`;
        }
        errorHtml += '</ul>';

        Swal.fire({
            icon: 'warning',
            title: 'Validasi Gagal',
            html: (xhr.responseJSON.message || 'Mohon periksa isian Anda.') + errorHtml
        });

        if (typeof errorCallback === 'function') errorCallback(xhr);
        return;
    }

    if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
    } else if (xhr.status === 401) {
        errorMessage = 'Sesi Anda telah habis, silakan login kembali.';
        setTimeout(() => { window.location.href = '/login'; }, 2000);
    } else if (xhr.status === 403) {
        errorMessage = 'Anda tidak memiliki akses untuk tindakan ini.';
    } else if (xhr.status === 500) {
        errorMessage = 'Terjadi kesalahan internal pada server.';
    }

    Swal.fire({
        icon: 'error',
        title: 'Error ' + xhr.status,
        text: errorMessage
    });

    if (typeof errorCallback === 'function') errorCallback(xhr);
}
