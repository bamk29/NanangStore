<div>
    <div class="mb-4">
        <button wire:click="startScanning" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Scan Barcode
        </button>
    </div>

    @if($isScanning)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
            <div class="bg-white p-4 rounded-lg max-w-lg w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Scan Barcode</h3>
                    <button wire:click="stopScanning" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="relative aspect-[4/3] mb-4">
                    <video id="scanner" class="w-full h-full rounded-lg"></video>
                </div>
                <p class="text-sm text-gray-500 text-center">Position the barcode within the camera view</p>
            </div>
        </div>

        <script>
            let scanner = null;

            function initScanner() {
                if (!scanner) {
                    scanner = new Html5Qrcode("scanner");
                }

                scanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 150 }
                    },
                    (decodedText) => {
                        @this.barcodeDetected(decodedText);
                        stopScanner();
                    },
                    (error) => {
                        // console.error(error);
                    }
                );
            }

            function stopScanner() {
                if (scanner) {
                    scanner.stop().then(() => {
                        scanner = null;
                    });
                }
            }

            // Initialize scanner when component is mounted
            document.addEventListener('livewire:initialized', () => {
                initScanner();
            });

            // Clean up when component is removed
            document.addEventListener('livewire:navigated', () => {
                stopScanner();
            });
        </script>
    @endif
</div>
