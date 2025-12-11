<div x-data="cameraScanner(@entangle($attributes->wire('model')))"
     x-show="isOpen"
     {{ $attributes }}
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    
    <div @click.away="stopScanner()" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Scan Barcode
            </h3>
            <button @click="stopScanner()" class="p-1 rounded-full hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Scanner Viewport -->
        <div class="relative bg-black h-64 sm:h-80">
            <div id="reader" style="width: 100%; height: 100%;"></div>
            
            <!-- Loading Indicator -->
            <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center text-white bg-black/50 z-10">
                <svg class="animate-spin h-8 w-8 text-white mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium">Mengaktifkan Kamera...</span>
            </div>
            
            <!-- Instructions Overlay -->
            <div x-show="!isLoading" class="absolute bottom-4 left-0 right-0 text-center pointer-events-none">
                <span class="bg-black/60 text-white text-xs px-3 py-1 rounded-full backdrop-blur-sm">
                    Arahkan kamera ke barcode produk
                </span>
            </div>
        </div>

        <!-- Manual Footer (Optional) -->
        <div class="px-4 py-3 bg-gray-50 border-t text-center">
             <p class="text-xs text-gray-500">Pastikan cahaya cukup terang untuk hasil terbaik.</p>
        </div>
    </div>
</div>

<script>
    function cameraScanner(wireModel) {
        <x-camera-scanner @scan-completed="$wire.set('search', $event.detail)" />{
        return {
            isOpen: false,
            isLoading: false,
            html5QrcodeScanner: null,
            
            init() {
                // Listen for event to open scanner
                window.addEventListener('open-camera-scanner', () => {
                    this.startScanner();
                });
            },

            async startScanner() {
                this.isOpen = true;
                this.isLoading = true;
                
                // Check for Secure Context (HTTPS or Localhost)
                if (!window.isSecureContext) {
                    alert("Peringatan Keamanan Browser!\n\nKamera tidak dapat diakses melalui koneksi HTTP biasa (selain localhost).\n\nSolusi:\n1. Gunakan HTTPS (Ngrok/Expose)\n2. Atau atur 'Insecure origins' di chrome://flags");
                    this.isOpen = false;
                    this.isLoading = false;
                    return;
                }

                // Wait for modal transition
                await new Promise(r => setTimeout(r, 300));

                if (!this.html5QrcodeScanner) {
                    this.html5QrcodeScanner = new Html5Qrcode("reader");
                }

                const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };
                
                try {
                    await this.html5QrcodeScanner.start(
                        { facingMode: "environment" }, 
                        config, 
                        (decodedText, decodedResult) => {
                            this.handleScanSuccess(decodedText);
                        },
                        (errorMessage) => {
                            // parse error, ignore it.
                        }
                    );
                } catch (err) {
                    console.error("Error starting scanner", err);
                    let msg = "Gagal mengaktifkan kamera.";
                    if (err.name === 'NotAllowedError') {
                        msg += "\nIzin kamera ditolak. Silakan izinkan akses di pengaturan browser.";
                    } else if (err.name === 'NotFoundError') {
                        msg += "\nPerangkat kamera tidak ditemukan.";
                    } else if (err.name === 'NotReadableError') {
                        msg += "\nKamera sedang digunakan aplikasi lain atau error hardware.";
                    }
                    alert(msg);
                    this.isOpen = false;
                } finally {
                    this.isLoading = false;
                }
            },

            stopScanner() {
                if (this.html5QrcodeScanner && this.html5QrcodeScanner.isScanning) {
                    this.html5QrcodeScanner.stop().then(() => {
                        this.isOpen = false;
                    }).catch(err => {
                        console.error("Failed to stop scanning", err);
                        this.isOpen = false;
                    });
                } else {
                    this.isOpen = false;
                }
            },

            handleScanSuccess(decodedText) {
                // Beep sound
                const audio = new Audio('/sounds/success.mp3');
                audio.play().catch(e => console.log('Audio play failed', e));

                // Update wire model directly
                // If wireModel is entangled, this updates the Livewire property
                if (wireModel !== undefined) {
                    wireModel = decodedText; // This might not work with entangle directly if it's a proxy 
                    // Better to dispatch event or use $wire
                }
                
                // Dispatch event explicitly to update the parent component
                this.$dispatch('scan-completed', decodedText);
                
                // Stop scanner and close modal
                this.stopScanner();
            }
        }
    }
</script>
