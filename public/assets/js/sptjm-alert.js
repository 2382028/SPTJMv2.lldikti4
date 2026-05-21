/* Reusable SweetAlert2 helper for SPTJM Online.
   Usage:
     SptjmAlert.success('Berhasil', 'Data tersimpan');
     SptjmAlert.error('Gagal', 'Terjadi kesalahan');
     SptjmAlert.warning('Peringatan', 'Lengkapi data');
     SptjmAlert.info('Info', 'Memproses...');
     const r = await SptjmAlert.question('Konfirmasi', 'Lanjutkan?', { confirmButtonText: 'Ya' });
*/
(function (global) {
  'use strict';

  const CDN_URL = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';

  const ensureSwal = () => {
    if (global.Swal && typeof global.Swal.fire === 'function') {
      return Promise.resolve(global.Swal);
    }

    // If already loading, await it
    if (global.__sptjmSwalLoadingPromise) {
      return global.__sptjmSwalLoadingPromise;
    }

    global.__sptjmSwalLoadingPromise = new Promise((resolve, reject) => {
      const existing = document.querySelector('script[data-sptjm-swal="1"]');
      if (existing) {
        existing.addEventListener('load', () => resolve(global.Swal));
        existing.addEventListener('error', reject);
        return;
      }

      const script = document.createElement('script');
      script.src = CDN_URL;
      script.async = true;
      script.defer = true;
      script.setAttribute('data-sptjm-swal', '1');
      script.onload = () => resolve(global.Swal);
      script.onerror = reject;
      document.head.appendChild(script);
    });

    return global.__sptjmSwalLoadingPromise;
  };

  const normalizeArgs = (title, textOrHtml, opts) => {
    const options = Object.assign({}, opts || {});

    if (typeof title === 'object' && title !== null) {
      return Object.assign({}, title);
    }

    if (typeof textOrHtml === 'string' && /<\w+[^>]*>/.test(textOrHtml)) {
      options.title = title || options.title;
      options.html = textOrHtml;
      return options;
    }

    options.title = title || options.title;
    if (typeof textOrHtml === 'string') {
      options.text = textOrHtml;
    }

    return options;
  };

  const fire = async (options) => {
    const Swal = await ensureSwal();
    if (!Swal) {
      // fallback
      try {
        alert((options && (options.title || options.text)) || '');
      } catch (e) {}
      return;
    }

    const base = {
      buttonsStyling: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-secondary ms-2',
      },
    };

    return Swal.fire(Object.assign(base, options || {}));
  };

  const api = {
    success: (title, textOrHtml, opts) => fire(Object.assign({ icon: 'success' }, normalizeArgs(title, textOrHtml, opts))),
    error: (title, textOrHtml, opts) => fire(Object.assign({ icon: 'error' }, normalizeArgs(title, textOrHtml, opts))),
    warning: (title, textOrHtml, opts) => fire(Object.assign({ icon: 'warning' }, normalizeArgs(title, textOrHtml, opts))),
    info: (title, textOrHtml, opts) => fire(Object.assign({ icon: 'info' }, normalizeArgs(title, textOrHtml, opts))),
    question: (title, textOrHtml, opts) => {
      const options = normalizeArgs(title, textOrHtml, opts);
      return fire(Object.assign({
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        reverseButtons: true,
      }, options));
    },
    loading: (title, textOrHtml, opts) => {
      const options = normalizeArgs(title || 'Mohon Tunggu', textOrHtml || 'Memproses...', opts);
      return fire(Object.assign({
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          try {
            if (global.Swal && typeof global.Swal.showLoading === 'function') {
              global.Swal.showLoading();
            }
          } catch (e) {}
        },
      }, options));
    },
    close: async () => {
      const Swal = await ensureSwal();
      if (Swal && typeof Swal.close === 'function') Swal.close();
    },
    fromFlash: (flash) => {
      if (!flash || typeof flash !== 'object') return;
      if (flash.success) api.success('Berhasil', String(flash.success));
      if (flash.error) api.error('Gagal', String(flash.error));
      if (flash.warning) api.warning('Peringatan', String(flash.warning));
      if (flash.info) api.info('Info', String(flash.info));
      if (flash.question) api.question('Konfirmasi', String(flash.question));
    },
  };

  global.SptjmAlert = api;
})(window);
