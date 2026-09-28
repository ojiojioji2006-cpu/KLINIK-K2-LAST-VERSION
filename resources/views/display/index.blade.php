<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layar Panggilan &mdash; Klinik Sederhana</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; background:#0b1220; color:#e8eefc; font-family: system-ui, "Segoe UI", Roboto, sans-serif; min-height:100vh; display:flex; flex-direction:column; }
        #unlock-overlay { position:fixed; inset:0; background:rgba(8,12,22,.96); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:50; text-align:center; padding:24px; }
        #unlock-overlay h1 { font-size: clamp(22px, 4vw, 40px); margin:0 0 12px; }
        #unlock-overlay p { color:#9fb0d0; max-width:520px; margin:0 0 24px; }
        #unlock-btn { font-size: clamp(18px, 3vw, 26px); padding:18px 34px; border:0; border-radius:14px; background:#2563eb; color:#fff; cursor:pointer; box-shadow:0 8px 30px rgba(37,99,235,.4); }
        #unlock-btn:hover { background:#1d4ed8; }
        header { display:flex; justify-content:space-between; align-items:center; padding:14px 22px; background:#0f1830; border-bottom:1px solid #1e2a4a; }
        header .brand { font-weight:800; letter-spacing:.5px; font-size: clamp(16px, 2.4vw, 24px); }
        header .right { display:flex; gap:14px; align-items:center; color:#9fb0d0; font-size:14px; }
        #sound-status.on { color:#34d399; } #sound-status.off { color:#f59e0b; }
        main { flex:1; display:flex; flex-direction:column; }
        #now-calling { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:24px; gap:10px; }
        #now-calling .nc-label { color:#60a5fa; font-weight:700; letter-spacing:2px; font-size: clamp(14px, 2vw, 20px); }
        #now-calling .nc-name { font-size: clamp(40px, 9vw, 120px); font-weight:900; line-height:1.05; }
        #now-calling .nc-meta { color:#9fb0d0; font-size: clamp(16px, 2.6vw, 28px); }
        #now-calling .nc-room { margin-top:8px; color:#fbbf24; font-weight:700; font-size: clamp(18px, 3vw, 34px); }
        #now-calling.idle .nc-name { color:#3b4a6b; font-size: clamp(28px, 5vw, 60px); }
        #now-calling.idle .nc-meta, #now-calling.idle .nc-room { display:none; }
        #now-calling.flash { animation: flash .6s ease; }
        @keyframes flash { 0%{ background:#10203f; } 100%{ background:transparent; } }
        footer { background:#0f1830; border-top:1px solid #1e2a4a; padding:10px 22px; }
        footer .ttl { color:#60a5fa; font-size:13px; font-weight:700; letter-spacing:1px; margin-bottom:6px; }
        #recent-list { list-style:none; margin:0; padding:0; display:flex; gap:10px; flex-wrap:wrap; }
        #recent-list li { background:#16213e; border:1px solid #243357; border-radius:10px; padding:8px 12px; font-size: clamp(13px, 1.6vw, 18px); color:#cdd9f5; }
        #recent-list li b { color:#fff; }
    </style>
</head>
<body>
    <div id="unlock-overlay">
        <h1>Layar Panggilan Pasien</h1>
        <p>Klik tombol di bawah <strong>satu kali</strong> untuk mengaktifkan suara. Ini wajib karena browser memblokir audio sampai ada interaksi pengguna.</p>
        <button id="unlock-btn">🔊 Aktifkan Suara</button>
    </div>

    <header>
        <div class="brand">KLINIK SEDERHANA &middot; RUANG TUNGGU</div>
        <div class="right">
            <span id="clock">--:--:--</span>
            <span id="sound-status" class="off">Suara: nonaktif</span>
        </div>
    </header>

    <main>
        <section id="now-calling" class="idle">
            <div class="nc-label" id="nc-label">SEDANG DIPANGGIL</div>
            <div class="nc-name" id="nc-name">Menunggu panggilan&hellip;</div>
            <div class="nc-meta"><span id="nc-mrn"></span><span id="nc-dot"></span><span id="nc-doctor"></span><span id="nc-dot2"></span><span id="nc-time"></span></div>
            <div class="nc-room">Silakan menuju ruang periksa</div>
        </section>
    </main>

    <footer>
        <div class="ttl">PANGGILAN TERAKHIR</div>
        <ul id="recent-list"></ul>
    </footer>

    <script src="{{ asset('js/display.js') }}"></script>
</body>
</html>