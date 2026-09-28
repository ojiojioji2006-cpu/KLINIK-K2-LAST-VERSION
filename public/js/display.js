(function () {
    var overlay = document.getElementById('unlock-overlay');
    var unlockBtn = document.getElementById('unlock-btn');
    var soundStatus = document.getElementById('sound-status');
    var clockEl = document.getElementById('clock');

    var nowCard = document.getElementById('now-calling');
    var ncLabel = document.getElementById('nc-label');
    var ncName = document.getElementById('nc-name');
    var ncMrn = document.getElementById('nc-mrn');
    var ncDot = document.getElementById('nc-dot');
    var ncDoctor = document.getElementById('nc-doctor');
    var ncDot2 = document.getElementById('nc-dot2');
    var ncTime = document.getElementById('nc-time');
    var recentList = document.getElementById('recent-list');

    var unlocked = false;
    var audioCtx = null;
    var seen = {}; // key -> true

    function ensureAudio() {
        if (!audioCtx) {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (AC) audioCtx = new AC();
        }
        if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume();
    }

    function chime() {
        if (!audioCtx) return;
        var t0 = audioCtx.currentTime;
        var notes = [880, 1174.66, 1567.98]; // ding-ding-ding naik
        for (var i = 0; i < notes.length; i++) {
            var o = audioCtx.createOscillator();
            var g = audioCtx.createGain();
            o.type = 'sine';
            o.frequency.value = notes[i];
            var t = t0 + i * 0.18;
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(0.3, t + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, t + 0.5);
            o.connect(g); g.connect(audioCtx.destination);
            o.start(t); o.stop(t + 0.55);
        }
    }

    function pickVoice() {
        if (!window.speechSynthesis) return null;
        var vs = window.speechSynthesis.getVoices() || [];
        for (var i = 0; i < vs.length; i++) {
            if (/^id/i.test(vs[i].lang)) return vs[i];
        }
        for (var j = 0; j < vs.length; j++) {
            if (/indonesi/i.test(vs[j].name)) return vs[j];
        }
        return null;
    }

    function speak(text) {
        if (!window.speechSynthesis) return;
        var u = new SpeechSynthesisUtterance(text);
        u.lang = 'id-ID';
        var v = pickVoice();
        if (v) u.voice = v;
        u.rate = 0.95;
        u.pitch = 1;
        window.speechSynthesis.speak(u);
    }

    function announceVoice(item) {
        chime();
        var name = item.name && item.name !== '-' ? item.name : 'Pasien';
        speak(name + '. Silakan menuju ruang periksa.');
    }

    function setText(el, val) { if (el) el.textContent = val; }

    function renderNow(item) {
        nowCard.classList.remove('idle');
        nowCard.classList.remove('flash'); void nowCard.offsetWidth; nowCard.classList.add('flash');
        setText(ncLabel, 'SEDANG DIPANGGIL');
        setText(ncName, item.name);
        setText(ncMrn, item.mrn);
        setText(ncDot, item.mrn && item.doctor ? '  ·  ' : '');
        setText(ncDoctor, item.doctor);
        setText(ncDot2, item.doctor && item.called_at_label ? '  ·  ' : '');
        setText(ncTime, item.called_at_label ? ('dipanggil ' + item.called_at_label) : '');
    }

    function renderIdle() {
        nowCard.classList.add('idle');
        setText(ncLabel, 'MENUNGGU');
        setText(ncName, 'Menunggu panggilan…');
        setText(ncMrn, ''); setText(ncDot, ''); setText(ncDoctor, ''); setText(ncDot2, ''); setText(ncTime, '');
    }

    function renderRecent(items) {
        recentList.innerHTML = '';
        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            var li = document.createElement('li');
            var b = document.createElement('b'); b.textContent = it.name;
            li.appendChild(b);
            li.appendChild(document.createTextNode('  ·  ' + it.mrn + '  ·  ' + (it.called_at_label || '')));
            recentList.appendChild(li);
        }
    }

    function tick() {
        fetch('/display/data', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                if (!list || !list.length) { renderIdle(); renderRecent([]); return; }

                renderNow(list[0]);          // yang terbaru SELALU tampil (visual live)
                renderRecent(list.slice(0, 6));

                if (unlocked) {
                    // umumkan dari yang terlama ke terbaru supaya TTS antre kronologis
                    var chrono = list.slice().reverse();
                    for (var i = 0; i < chrono.length; i++) {
                        var it = chrono[i];
                        var key = it.id + '@' + it.called_at;
                        if (!seen[key]) { seen[key] = true; announceVoice(it); }
                    }
                } else {
                    // terkunci: tandai terlihat agar tidak "catch-up" spam saat unlock
                    for (var k = 0; k < list.length; k++) { seen[list[k].id + '@' + list[k].called_at] = true; }
                }
            })
            .catch(function () { /* diem aja, coba lagi tick berikutnya */ });
    }

    function tickClock() {
        var d = new Date();
        var p = function (n) { return (n < 10 ? '0' : '') + n; };
        setText(clockEl, p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds()));
    }

    function unlock() {
        ensureAudio();
        unlocked = true;
        overlay.style.display = 'none';
        soundStatus.textContent = 'Suara: aktif';
        soundStatus.classList.remove('off'); soundStatus.classList.add('on');
        // konfirmasi langsung: chime + suara uji (sekaligus unlock TTS di dalam gesture)
        chime();
        speak('Layar panggilan aktif.');
    }

    unlockBtn.addEventListener('click', unlock);

    if (window.speechSynthesis) { window.speechSynthesis.onvoiceschanged = function () {}; }

    tickClock(); setInterval(tickClock, 1000);
    tick(); setInterval(tick, 3500);
})();