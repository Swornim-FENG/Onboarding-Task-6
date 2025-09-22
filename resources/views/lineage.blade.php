<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Lineage Viewer</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Inter, system-ui, Arial; background:#f6f8fb; padding:30px; }
        .card { max-width:880px; margin:0 auto; background:white; padding:20px 28px; border-radius:10px; box-shadow:0 6px 18px rgba(25,32,49,0.06); }
        h1 { font-size:20px; margin-bottom:12px; color:#1f2937; }
        .row { display:flex; gap:12px; margin-bottom:12px; }
        input[type="text"] { flex:1; padding:10px 12px; border-radius:8px; border:1px solid #d1d5db; }
        button { padding:10px 16px; border-radius:8px; border:none; background:#2563eb; color:white; cursor:pointer; }
        .switch { display:flex; align-items:center; gap:8px; margin-left:6px; }
        .results { margin-top:18px; }
        .line-item { padding:12px; border:1px solid #eef2ff; border-radius:8px; margin-bottom:8px; background: linear-gradient(180deg,#fff,#fbfdff); }
        .muted { color:#6b7280; font-size:13px; }
        pre { white-space:pre-wrap; word-break:break-word; background:#f3f4f6; padding:8px; border-radius:6px; }
        .error { color:#b91c1c; margin-top:8px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Data Lineage Viewer</h1>

        <div class="row">
            <input id="element" type="text" placeholder="Enter data element id "/>
            <div style="display:flex; align-items:center;">
                <button id="lookup">Lookup</button>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
            <label class="muted"><input type="checkbox" id="useEncrypted"/> Send encrypted</label>
            <span class="muted"> </span>
        </div>

        <div id="status" class="muted">Enter an element and click Lookup.</div>

        <div id="results" class="results"></div>
        <div id="error" class="error"></div>
    </div>

    <!-- Optional: JSEncrypt for RSA on client side (only if using encrypted lookup) -->
    <script src="https://cdn.jsdelivr.net/npm/jsencrypt/bin/jsencrypt.min.js"></script>

    <script>
    const lookupBtn = document.getElementById('lookup');
    const elementEl = document.getElementById('element');
    const resultsEl = document.getElementById('results');
    const statusEl = document.getElementById('status');
    const errorEl = document.getElementById('error');
    const useEncryptedEl = document.getElementById('useEncrypted');

    // Replace with your server's public key if you enable encryption (PEM format)
    const PUBLIC_KEY = `-----BEGIN PUBLIC KEY-----
PUBLIC_KEY_HERE
-----END PUBLIC KEY-----`;

    lookupBtn.addEventListener('click', async () => {
        resultsEl.innerHTML = '';
        errorEl.textContent = '';
        let element = elementEl.value.trim();
        if (!element) {
            errorEl.textContent = 'Please enter a data element id.';
            return;
        }

        statusEl.textContent = 'Looking up...';

        let payload = { element };

        if (useEncryptedEl.checked) {
            // Encrypt the element with public key using JSEncrypt
            const encrypt = new JSEncrypt();
            encrypt.setPublicKey(PUBLIC_KEY);
            const encrypted = encrypt.encrypt(element);
            if (!encrypted) {
                errorEl.textContent = 'Encryption failed — check public key.';
                statusEl.textContent = '';
                return;
            }
            payload = { element: encrypted, encrypted: true };
        }

        try {
            const res = await fetch('/api/lineage/lookup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.message || (data.errors ? JSON.stringify(data.errors) : 'Lookup failed');
                statusEl.textContent = 'Error';
                return;
            }

            // render results
            statusEl.textContent = `Found ${data.lineage.length} event(s) for "${data.data_element}"`;
            resultsEl.innerHTML = '';

            data.lineage.forEach(item => {
                const div = document.createElement('div');
                div.className = 'line-item';
                div.innerHTML = `
                    <div><strong>Action:</strong> ${item.action || '-'} | <strong>Source:</strong> ${item.source || '-'} | <strong>Destination:</strong> ${item.destination || '-'}</div>
                    <div class="muted">Occurred: ${item.occurred_at || item.created_at}</div>
                    <div style="margin-top:8px;"><strong>Transformation:</strong> ${item.transformation || '-'}</div>
                    <div style="margin-top:8px;"><strong>Metadata:</strong><pre>${JSON.stringify(item.metadata || {}, null, 2)}</pre></div>
                `;
                resultsEl.appendChild(div);
            });
        } catch (err) {
            console.error(err);
            errorEl.textContent = 'Network error or server error. Check console.';
            statusEl.textContent = '';
        }
    });
    </script>
</body>
</html>
