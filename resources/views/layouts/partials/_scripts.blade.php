{{-- ── Scripts — dikumpul di sini supaya app.blade.php lebih bersih ── --}}
@stack('scripts')
<script nonce="{{ $cspNonce }}">
// ── Pintasan papan kekunci: tekan "/" untuk fokus carian global ──
(function () {
    const input = document.getElementById('carian-global');
    const hint  = document.getElementById('search-hint');
    if (!input) return;

    input.addEventListener('focus', () => hint && (hint.style.display = 'none'));
    input.addEventListener('blur',  () => hint && (hint.style.display = ''));

    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT'
                          && document.activeElement.tagName !== 'TEXTAREA'
                          && document.activeElement.tagName !== 'SELECT') {
            e.preventDefault();
            input.focus();
            input.select();
        }
        if (e.key === 'Escape' && document.activeElement === input) {
            input.blur();
        }
    });
})();

// ── Dropdown profil ──────────────────────────────────────
function toggleProfilMenu() {
    const menu = document.getElementById('profil-menu');
    const btn  = document.getElementById('profil-btn');
    const open = menu.classList.toggle('hidden');
    btn.setAttribute('aria-expanded', !open);
}
document.addEventListener('click', function(e) {
    if (e.target.closest('.js-dismiss-alert')) {
        e.target.closest('[role=alert]')?.remove();
    }
});

// ── Mobile Sidebar Toggle ────────────────────────────────
(function () {
    const btnHamburger = document.getElementById('btn-hamburger');
    const sidebar      = document.getElementById('sidebar-utama');
    const overlay      = document.getElementById('sidebar-overlay');
    if (!btnHamburger || !sidebar || !overlay) return;

    function bukaMenu() {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('aktif');
        btnHamburger.setAttribute('aria-expanded', 'true');
        btnHamburger.innerHTML = '<i class="fa-solid fa-xmark text-base" aria-hidden="true"></i>';
        document.body.style.overflow = 'hidden';
    }

    function tutupMenu() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('aktif');
        btnHamburger.setAttribute('aria-expanded', 'false');
        btnHamburger.innerHTML = '<i class="fa-solid fa-bars text-base" aria-hidden="true"></i>';
        document.body.style.overflow = '';
    }

    btnHamburger.addEventListener('click', function () {
        sidebar.classList.contains('mobile-open') ? tutupMenu() : bukaMenu();
    });
    overlay.addEventListener('click', tutupMenu);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) tutupMenu();
    });
    sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 1024) tutupMenu();
        });
    });
})();

// Wire profil button
document.getElementById('profil-btn')?.addEventListener('click', function(e) {
    e.stopPropagation();
    toggleProfilMenu();
});
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('profil-dropdown-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('profil-menu')?.classList.add('hidden');
        document.getElementById('profil-btn')?.setAttribute('aria-expanded', 'false');
    }
});

// ── Idle Session Timeout ─────────────────────────────────────────
(function () {
    const IDLE_WARN_MS   = 25 * 60 * 1000;
    const IDLE_LOGOUT_MS = 30 * 60 * 1000;
    const LOGOUT_URL     = '{{ route("logout") }}';
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let warnTimer   = null;
    let logoutTimer = null;
    let warnShown   = false;

    const modalHtml = `
    <div id="idle-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:16px;padding:32px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3)">
            <div style="width:56px;height:56px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                <i class="fa-solid fa-clock" style="font-size:24px;color:#f59e0b"></i>
            </div>
            <h3 style="font-weight:700;font-size:18px;color:#1f2937;margin:0 0 8px">Sesi Hampir Tamat</h3>
            <p style="color:#6b7280;font-size:14px;margin:0 0 4px">Anda tidak aktif selama <strong>25 minit</strong>.</p>
            <p id="idle-countdown" style="color:#dc2626;font-size:13px;font-weight:600;margin:0 0 24px">Log keluar dalam 5:00</p>
            <button id="idle-teruskan"
                style="background:#f59e0b;color:#1a1a2e;border:none;border-radius:8px;padding:10px 24px;font-weight:700;font-size:14px;cursor:pointer;width:100%">
                Teruskan Sesi
            </button>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal    = document.getElementById('idle-modal');
    const btnTerus = document.getElementById('idle-teruskan');
    const countEl  = document.getElementById('idle-countdown');

    let countdownInterval = null;
    let countdownSecs     = 300;

    function mulaKira() {
        countdownSecs = 300;
        clearInterval(countdownInterval);
        countdownInterval = setInterval(function () {
            countdownSecs--;
            const m = Math.floor(countdownSecs / 60);
            const s = countdownSecs % 60;
            if (countEl) countEl.textContent = 'Log keluar dalam ' + m + ':' + String(s).padStart(2, '0');
        }, 1000);
    }

    function tunjukAmaran() {
        warnShown = true;
        modal.style.display = 'flex';
        mulaKira();
    }

    function sembunyiAmaran() {
        warnShown = false;
        modal.style.display = 'none';
        clearInterval(countdownInterval);
    }

    function logKeluar() {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = LOGOUT_URL;
        const t = document.createElement('input');
        t.type = 'hidden'; t.name = '_token'; t.value = CSRF;
        f.appendChild(t);
        document.body.appendChild(f);
        f.submit();
    }

    function resetTimer() {
        if (warnShown) return;
        clearTimeout(warnTimer);
        clearTimeout(logoutTimer);
        warnTimer   = setTimeout(tunjukAmaran, IDLE_WARN_MS);
        logoutTimer = setTimeout(logKeluar,    IDLE_LOGOUT_MS);
    }

    ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'].forEach(function (ev) {
        document.addEventListener(ev, resetTimer, { passive: true });
    });

    if (btnTerus) {
        btnTerus.addEventListener('click', function () {
            sembunyiAmaran();
            resetTimer();
            fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' }).catch(() => {});
        });
    }

    resetTimer();
})();

// ── Toggle Tema: Light / Dark ─────────────────────────────────────
(function () {
    const html = document.documentElement;
    const btn  = document.getElementById('btn-toggle-tema');
    const icon = document.getElementById('icon-tema');
    const mq   = window.matchMedia('(prefers-color-scheme: dark)');

    function isDarkActive() {
        return html.classList.contains('dark') ||
            (!html.classList.contains('light') && mq.matches);
    }

    function updateIcon() {
        if (!icon) return;
        icon.className = isDarkActive()
            ? 'fa-solid fa-sun text-sm'
            : 'fa-solid fa-moon text-sm';
    }

    function toggleTema() {
        if (isDarkActive()) {
            html.classList.remove('dark');
            html.classList.add('light');
            try { localStorage.setItem('ibook-theme', 'light'); } catch(e) {}
        } else {
            html.classList.remove('light');
            html.classList.add('dark');
            try { localStorage.setItem('ibook-theme', 'dark'); } catch(e) {}
        }
        updateIcon();
    }

    if (btn) btn.addEventListener('click', toggleTema);
    updateIcon();
    mq.addEventListener('change', updateIcon);
})();
</script>
