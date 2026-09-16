<!-- PWA Mobile Bottom Banner -->
<div id="pwa-mobile-banner" class="hidden fixed bottom-4 inset-x-3 sm:inset-x-6 z-50 md:hidden bg-gray-900/95 backdrop-blur-md border border-gray-700/80 text-white rounded-2xl p-3.5 shadow-2xl transition-all duration-300 transform translate-y-0">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('images/pwa-icon-192.png') }}" alt="Loops CRM" class="w-11 h-11 rounded-xl shadow-md border border-white/10 flex-shrink-0 object-cover">
            <div class="min-w-0">
                <p class="text-sm font-bold text-white leading-tight truncate">Install Loops CRM</p>
                <p class="text-xs text-gray-300 mt-0.5 truncate">Fast home screen access & standalone view</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button type="button" class="pwa-install-trigger px-3.5 py-2 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-xl shadow-md hover:opacity-95 active:scale-95 transition-all flex items-center gap-1.5">
                <i class="fas fa-download text-xs"></i>
                <span>Install</span>
            </button>
            <button type="button" id="pwa-dismiss-banner-btn" class="p-2 text-gray-400 hover:text-white rounded-lg transition-colors" title="Dismiss">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </div>
</div>

<!-- iOS PWA Install Guide Modal -->
<div id="pwa-ios-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 text-left relative transform transition-all">
        <button type="button" onclick="document.getElementById('pwa-ios-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('images/pwa-icon-192.png') }}" alt="Loops CRM" class="w-12 h-12 rounded-xl shadow border border-gray-100 object-cover">
            <div>
                <h3 class="text-base font-black text-gray-800">Install on iPhone / iPad</h3>
                <p class="text-xs text-gray-500">Add Loops CRM to your home screen</p>
            </div>
        </div>

        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs text-gray-700">
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">1</span>
                <div>
                    In Safari, tap the <span class="font-bold text-gray-900">Share</span> button at the bottom of the screen:
                    <div class="mt-1 inline-flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-200 rounded text-blue-600 font-semibold shadow-2xs">
                        <i class="fas fa-arrow-up-from-bracket"></i> Share Icon
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">2</span>
                <div>
                    Scroll down and tap <span class="font-bold text-gray-900">Add to Home Screen</span>:
                    <div class="mt-1 inline-flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-200 rounded text-gray-800 font-semibold shadow-2xs">
                        <i class="far fa-plus-square"></i> Add to Home Screen
                    </div>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">3</span>
                <div>
                    Tap <span class="font-bold text-brand-purple">Add</span> in the top-right corner.
                </div>
            </div>
        </div>

        <button type="button" onclick="document.getElementById('pwa-ios-modal').classList.add('hidden')"
            class="mt-5 w-full py-2.5 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-xl shadow hover:opacity-90 active:scale-95 transition-all">
            Got It
        </button>
    </div>
</div>

<!-- Android PWA Install Guide Modal (When Chrome hasn't triggered native prompt) -->
<div id="pwa-android-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 text-left relative transform transition-all">
        <button type="button" onclick="document.getElementById('pwa-android-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('images/pwa-icon-192.png') }}" alt="Loops CRM" class="w-12 h-12 rounded-xl shadow border border-gray-100 object-cover">
            <div>
                <h3 class="text-base font-black text-gray-800">Install on Android</h3>
                <p class="text-xs text-gray-500">Google Chrome / Samsung Internet</p>
            </div>
        </div>

        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs text-gray-700">
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">1</span>
                <div>
                    Tap the <span class="font-bold text-gray-900">3 vertical dots (⋮)</span> at the top-right corner of Chrome.
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">2</span>
                <div>
                    Tap <span class="font-bold text-gray-900">"Install app"</span> (or <span class="font-bold text-gray-900">"Add to Home screen"</span>).
                </div>
            </div>

            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-purple text-white flex items-center justify-center font-bold text-xs">3</span>
                <div>
                    Confirm by tapping <span class="font-bold text-brand-purple">Install</span> in the popup.
                </div>
            </div>

            <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 leading-relaxed">
                <i class="fas fa-shield-halved text-amber-600 mr-1"></i>
                <strong>Important:</strong> Android Chrome strictly requires a secure connection (<strong>HTTPS</strong>). If you are accessing via <code>http://</code> on an IP address, Chrome disables installation.
            </div>
        </div>

        <button type="button" onclick="document.getElementById('pwa-android-modal').classList.add('hidden')"
            class="mt-5 w-full py-2.5 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-xl shadow hover:opacity-90 active:scale-95 transition-all">
            Got It
        </button>
    </div>
</div>

<!-- Desktop Fallback Guide Modal -->
<div id="pwa-fallback-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 text-left relative transform transition-all">
        <button type="button" onclick="document.getElementById('pwa-fallback-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('images/pwa-icon-192.png') }}" alt="Loops CRM" class="w-12 h-12 rounded-xl shadow border border-gray-100 object-cover">
            <div>
                <h3 class="text-base font-black text-gray-800">Install Loops CRM</h3>
                <p class="text-xs text-gray-500">Run as a standalone desktop app</p>
            </div>
        </div>

        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs text-gray-700">
            <p><span class="font-bold text-gray-900">Chrome / Edge:</span> Click the <i class="fas fa-download text-brand-purple"></i> install icon in your address bar, or click Menu (⋮) &rarr; "Install Loops CRM".</p>
            <p><span class="font-bold text-gray-900">Note:</span> Ensure you are accessing via HTTPS for installation to be enabled.</p>
        </div>

        <button type="button" onclick="document.getElementById('pwa-fallback-modal').classList.add('hidden')"
            class="mt-5 w-full py-2.5 bg-gradient-to-r from-brand-purple to-brand-pink text-white text-xs font-bold rounded-xl shadow hover:opacity-90 active:scale-95 transition-all">
            Close
        </button>
    </div>
</div>

<!-- PWA Global Controller Script -->
<script>
// Global PWA Refresh App Handler (Defined globally so it always runs on iOS PWA & desktop)
window.refreshPwaApp = function(btn) {
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.add('fa-spin');
        }
    }

    // Trigger ServiceWorker background update check
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let reg of registrations) {
                reg.update();
            }
        }).catch(function(err) {});
    }

    // Bulletproof reload for iOS Safari Standalone PWA and desktop browsers
    setTimeout(function() {
        try {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('_pwa_refresh', Date.now().toString());
            window.location.replace(currentUrl.toString());
        } catch(e) {
            window.location.reload();
        }
    }, 200);
};

(function() {
    let deferredPrompt = null;
    const isIOS = (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const isAndroid = /Android/.test(navigator.userAgent);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
        || window.navigator.standalone === true 
        || document.referrer.includes('android-app://');

    const mobileBanner = document.getElementById('pwa-mobile-banner');
    const iosModal = document.getElementById('pwa-ios-modal');
    const androidModal = document.getElementById('pwa-android-modal');
    const fallbackModal = document.getElementById('pwa-fallback-modal');

    function hideAllInstallUi() {
        document.querySelectorAll('.pwa-install-btn').forEach(function(el) {
            el.classList.add('hidden');
        });
        if (mobileBanner) mobileBanner.classList.add('hidden');
        if (iosModal) iosModal.classList.add('hidden');
        if (androidModal) androidModal.classList.add('hidden');
        if (fallbackModal) fallbackModal.classList.add('hidden');
    }

    // If running in standalone mode (already installed), hide install triggers only
    if (isStandalone) {
        hideAllInstallUi();
    } else {
        function initMobileBanner() {
            if (!mobileBanner) return;
            const isMobileScreen = window.innerWidth <= 768;
            const dismissedAt = localStorage.getItem('pwa_banner_dismissed_at');
            const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);

            if (isMobileScreen && (!dismissedAt || parseInt(dismissedAt, 10) < oneDayAgo)) {
                mobileBanner.classList.remove('hidden');
            }
        }

        // Android & Chromium install prompt capture
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            initMobileBanner();
        });

        // Initialize banner on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMobileBanner);
        } else {
            initMobileBanner();
        }

        // Click handler for any install trigger
        function triggerInstall(e) {
            if (e) e.preventDefault();

            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted PWA installation');
                        hideAllInstallUi();
                    }
                    deferredPrompt = null;
                });
            } else if (isIOS) {
                if (iosModal) iosModal.classList.remove('hidden');
            } else if (isAndroid) {
                if (androidModal) androidModal.classList.remove('hidden');
            } else {
                if (fallbackModal) fallbackModal.classList.remove('hidden');
            }
        }

        // Bind triggers across the DOM
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.pwa-install-trigger, .pwa-install-btn');
            if (trigger) {
                triggerInstall(e);
            }
        });

        // Dismiss banner
        const dismissBtn = document.getElementById('pwa-dismiss-banner-btn');
        if (dismissBtn && mobileBanner) {
            dismissBtn.addEventListener('click', function() {
                mobileBanner.classList.add('hidden');
                localStorage.setItem('pwa_banner_dismissed_at', Date.now().toString());
            });
        }

        // App installed event
        window.addEventListener('appinstalled', function() {
            console.log('Loops CRM PWA was successfully installed');
            hideAllInstallUi();
        });
    }

    // Service Worker Registration (always register even if standalone)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ asset("serviceworker.js") }}', { scope: '{{ asset("") }}' }).then(function(reg) {
                console.log('PWA ServiceWorker registered with scope:', reg.scope);
            }).catch(function(err) {
                console.error('PWA ServiceWorker registration failed:', err);
            });
        });
    }
})();
</script>

