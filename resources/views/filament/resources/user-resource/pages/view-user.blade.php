<x-filament-panels::page>
    {{-- Confetti Logic --}}
    @if (session()->has('user_created_confetti'))
        <div id="confettiCanvasContainer"
             style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; pointer-events: none;">
            <canvas id="user-created-confetti-canvas" style="width:100%; height:100%;"></canvas>
        </div>

        <div id="celebrationOverlay"
              style="position: fixed; top: 0; left: 0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; z-index: 9998; pointer-events: none;">
              <div style="text-align: center; background-color: rgba(255,255,255,0.9); padding: 40px; border-radius: 20px; box-shadow: 0 0 30px rgba(0,0,0,0.2);">
                  <div style="font-size: 120px; margin-bottom: 20px;">🎉</div> <!-- Large birthday cake icon -->
                  <h1 style="font-size: 32px; font-weight: bold; color: #D7263D;">Hongera!</h1>
                  <p style="font-size: 20px; color: #333;">
                      Ujumbe umetumwa kwa wakala <strong>{{ session('wakala_name') }}</strong><br>
                      Asante kwa kujaza taarifa zako. Endelea kufurahia huduma zetu!
                  </p>
              </div>
          </div>

        @pushOnce('scripts')
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('user-created-confetti-canvas');
                if (canvas) {
                    const myConfetti = confetti.create(canvas, {
                        resize: true,
                        useWorker: true
                    });

                    // More abundant, slower falling confetti
                    function fireConfetti() {
                        const defaults = {
                            spread: 180,
                            ticks: 200,
                            gravity: 0.4, // Slower fall
                            decay: 0.94,
                            startVelocity: 20, // Slower initial speed
                            shapes: ['square', 'circle', 'star'],
                            colors: ['#D7263D', '#F46036', '#FFFFFF', '#8D99AE', '#FFD166', '#06D6A0'],
                            scalar: 1.2,
                            particleCount: 150 // More particles
                        };

                        // Multiple bursts from different positions
                        myConfetti({
                            ...defaults,
                            origin: { x: 0.5, y: 0.1 }, // Top center
                            angle: 90, // Straight down
                            spread: 100
                        });

                        myConfetti({
                            ...defaults,
                            origin: { x: 0.2, y: 0.1 }, // Left side
                            angle: 75, // Diagonal right
                        });

                        myConfetti({
                            ...defaults,
                            origin: { x: 0.8, y: 0.1 }, // Right side
                            angle: 105, // Diagonal left
                        });

                        // Continuous falling for 5 seconds
                        let duration = 5000;
                        let end = Date.now() + duration;
                        let interval = setInterval(function() {
                            if (Date.now() > end) {
                                return clearInterval(interval);
                            }

                            myConfetti({
                                ...defaults,
                                particleCount: 30,
                                origin: { x: Math.random(), y: 0 }, // From top
                                angle: 90 + (Math.random() * 20 - 10), // Slightly varied
                                drift: Math.random() * 0.5 - 0.25, // Minimal horizontal drift
                            });
                        }, 200);
                    }

                    fireConfetti();
                }
            });
        </script>
        @endPushOnce
        {{ session()->forget('user_created_confetti') }} {{-- Clear session flash --}}
    @endif
    {{-- END Confetti Logic --}}

    {{ $this->infolist }}
</x-filament-panels::page>
