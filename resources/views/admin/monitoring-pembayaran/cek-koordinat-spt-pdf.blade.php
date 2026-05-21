<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cek Koordinat SPT (PDF)</title>
  <style>
    body{margin:0;background:#f5f5f5;font:14px/1.4 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial,sans-serif}
    #coord-panel{position:fixed;top:12px;right:12px;z-index:999999;background:rgba(0,0,0,.85);color:#fff;padding:10px 12px;border-radius:8px;max-width:340px}
    #coord-panel .row{margin:2px 0;word-break:break-all}
    #coord-panel button{margin-top:6px;padding:4px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.25);background:transparent;color:#fff;cursor:pointer}
    #viewer{padding:16px 0}
    .page{display:flex;justify-content:center;margin:0 0 16px 0}
    canvas{background:#fff;box-shadow:0 0 0 1px rgba(0,0,0,.12)}
  </style>
</head>
<body>
  <div id="coord-panel">
    <div class="row"><strong>Cek Koordinat Overlay (PDF)</strong></div>
    <div class="row"><small>Klik di PDF untuk ambil koordinat (pt).</small></div>
    <div class="row">jenis: <span id="cp-jenis">{{ $isPns ? 'PNS' : 'NON PNS' }}</span></div>
    <div class="row">template: <span id="cp-template">{{ $templateName }}</span></div>
    <div class="row">page: <span id="cp-page">-</span></div>
    <div class="row">pt (x from left, y from bottom): <span id="cp-pt">-</span></div>
    <div class="row">snippet: <span id="cp-php">-</span></div>
    <button type="button" id="cp-copy">Copy snippet</button>
  </div>

  <div id="viewer"></div>

  <script type="module">
    import * as pdfjsLib from @json(asset('vendor/pdfjs/pdf.min.js'));

    (function(){
      if (!pdfjsLib) {
        document.getElementById('cp-pt').textContent = 'Gagal load pdf.js (offline asset tidak ada)';
        return;
      }

      const pdfBase64 = @json($pdfBase64 ?? null);
      const ptPerPx = Number(@json($ptPerPx ?? null));
      const xMap = @json($xMap ?? []);
      const yMap = @json($yMap ?? []);
      const viewer = document.getElementById('viewer');
      const dpr = window.devicePixelRatio || 1;
      const scale = 1.5; // render scale only (does not affect PDF pt coordinates)

      // worker src local
      pdfjsLib.GlobalWorkerOptions.workerSrc = @json(asset('vendor/pdfjs/pdf.worker.min.js'));

      let lastSnippet = '';

      function nearestKey(map, value){
        let bestKey = null;
        let bestDiff = Infinity;
        for (const k in map) {
          if (!Object.prototype.hasOwnProperty.call(map, k)) continue;
          const v = Number(map[k]);
          if (Number.isNaN(v)) continue;
          const d = Math.abs(v - value);
          if (d < bestDiff) { bestDiff = d; bestKey = k; }
        }
        return bestKey;
      }

      function setPanel(pageNo, xPt, yPt) {
        const x = Number(xPt);
        const y = Number(yPt);
        let snippet = "['x' => " + x.toFixed(3) + ", 'y' => " + y.toFixed(3) + "]";
        if (ptPerPx && !Number.isNaN(ptPerPx)) {
          const xPx = x / ptPerPx;
          const yPx = y / ptPerPx;
          const xKey = nearestKey(xMap, xPx);
          const yKey = nearestKey(yMap, yPx);
          if (xKey && yKey) {
            snippet = "['x' => $css->xPt('" + xKey + "'), 'y' => $css->yPt('" + yKey + "')]";
          }
        }

        document.getElementById('cp-page').textContent = String(pageNo);
        document.getElementById('cp-pt').textContent = x.toFixed(3) + ', ' + y.toFixed(3);
        document.getElementById('cp-php').textContent = snippet;
        lastSnippet = snippet;
      }

      document.getElementById('cp-copy').addEventListener('click', function(){
        if (!lastSnippet) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(lastSnippet);
        }
      });

      function base64ToUint8Array(b64) {
        const binary = atob(b64);
        const len = binary.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) bytes[i] = binary.charCodeAt(i);
        return bytes;
      }

      if (!pdfBase64) {
        document.getElementById('cp-pt').textContent = 'Gagal membuka PDF: data PDF kosong';
        return;
      }

      const docTask = pdfjsLib.getDocument({ data: base64ToUint8Array(pdfBase64) });

      docTask.promise.then(async function(pdf){
        for (let pageNo = 1; pageNo <= pdf.numPages; pageNo++) {
          const page = await pdf.getPage(pageNo);
          const viewport = page.getViewport({ scale });

          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.style.width = viewport.width + 'px';
          canvas.style.height = viewport.height + 'px';
          canvas.width = Math.floor(viewport.width * dpr);
          canvas.height = Math.floor(viewport.height * dpr);

          const wrap = document.createElement('div');
          wrap.className = 'page';
          wrap.appendChild(canvas);
          viewer.appendChild(wrap);

          await page.render({
            canvasContext: ctx,
            viewport: viewport,
            transform: [dpr, 0, 0, dpr, 0, 0]
          }).promise;

          canvas.addEventListener('click', function(ev){
            const rect = canvas.getBoundingClientRect();
            const x = ev.clientX - rect.left;
            const y = ev.clientY - rect.top;

            // Convert canvas CSS pixel coords -> PDF points (origin bottom-left)
            const pt = viewport.convertToPdfPoint(x, y);
            setPanel(pageNo, pt[0], pt[1]);
          });
        }
      }).catch(function(err){
        document.getElementById('cp-pt').textContent = 'Gagal membuka PDF: ' + (err && err.message ? err.message : String(err));
      });
    })();
  </script>
</body>
</html>
