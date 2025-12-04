@php
    $baseImagePath = asset('images/feet_template.png');
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div
        wire:ignore
        x-data="{ 
            state: $wire.$entangle('{{ $getStatePath() }}'),
            mode: 'pen', 
            currentColor: '#FF0000', 
            currentThickness: 4, 
            isDrawing: false,
            history: [],
            
            bgCanvas: null,
            drawCanvas: null,
            bgCtx: null,
            drawCtx: null,

            // HIER PAS JE DE AFMETINGEN AAN
            // Als je foto uitgerekt is, speel dan met deze 'canvasHeight'
            canvasWidth: 800,  
            canvasHeight: 500, 

            init() {
                this.bgCanvas = this.$refs.bgCanvas;
                this.drawCanvas = this.$refs.drawCanvas;
                
                this.bgCtx = this.bgCanvas.getContext('2d');
                this.drawCtx = this.drawCanvas.getContext('2d');
                
                // Resolutie instellen
                [this.bgCanvas, this.drawCanvas].forEach(c => {
                    c.width = this.canvasWidth;
                    c.height = this.canvasHeight;
                });

                this.loadBackground();

                if (this.state) {
                    const savedImg = new Image();
                    savedImg.onload = () => this.drawCtx.drawImage(savedImg, 0, 0);
                    savedImg.src = this.state;
                }

                this.setupListeners(this.drawCanvas);
            },

            loadBackground() {
                const img = new Image();
                img.onload = () => {
                    // Teken de afbeelding over de volledige grootte
                    this.bgCtx.drawImage(img, 0, 0, this.canvasWidth, this.canvasHeight);
                };
                img.src = @js($baseImagePath);
            },

            setupListeners(canvas) {
                canvas.addEventListener('mousedown', (e) => this.start(e));
                canvas.addEventListener('mousemove', (e) => this.move(e));
                canvas.addEventListener('mouseup', () => this.stop());
                canvas.addEventListener('mouseout', () => this.stop());

                canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.start(e.touches[0]);
                });
                canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    this.move(e.touches[0]);
                });
                canvas.addEventListener('touchend', () => this.stop());
            },

            // ... (De rest van de logica blijft hetzelfde, maar gebruikt nu de variabelen) ...

            getMousePos(e) {
                const rect = this.drawCanvas.getBoundingClientRect();
                
                // We moeten rekening houden met schaling (als het scherm kleiner is dan de canvas)
                const scaleX = this.canvasWidth / rect.width;
                const scaleY = this.canvasHeight / rect.height;

                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            },

            start(e) {
                const pos = this.getMousePos(e);
                const x = pos.x;
                const y = pos.y;

                if (this.mode === 'text') {
                    const text = prompt('Typ uw notitie:');
                    if (text) {
                        this.saveToHistory();
                        this.drawCtx.globalCompositeOperation = 'source-over';
                        this.drawCtx.fillStyle = this.currentColor;
                        this.drawCtx.font = 'bold 16px sans-serif';
                        this.drawCtx.fillText(text, x, y);
                        this.save();
                    }
                    return; 
                }

                this.saveToHistory();
                this.isDrawing = true;
                this.drawCtx.beginPath();
                this.drawCtx.moveTo(x, y);
            },

            move(e) {
                if (!this.isDrawing || this.mode === 'text') return;
                
                const pos = this.getMousePos(e);
                const x = pos.x;
                const y = pos.y;

                this.drawCtx.lineJoin = 'round';
                this.drawCtx.lineCap = 'round';
                this.drawCtx.lineWidth = this.currentThickness;

                if (this.mode === 'eraser') {
                    this.drawCtx.globalCompositeOperation = 'destination-out'; 
                } else {
                    this.drawCtx.globalCompositeOperation = 'source-over';
                    this.drawCtx.strokeStyle = this.currentColor;
                }

                this.drawCtx.lineTo(x, y);
                this.drawCtx.stroke();
            },

            stop() {
                if (this.isDrawing) {
                    this.isDrawing = false;
                    this.save();
                }
            },

            save() {
                this.state = this.drawCanvas.toDataURL();
            },

            saveToHistory() {
                this.history.push(this.drawCanvas.toDataURL());
                if (this.history.length > 20) this.history.shift();
            },

            undo() {
                if (this.history.length > 0) {
                    const previousState = this.history.pop();
                    this.drawCtx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
                    const img = new Image();
                    img.onload = () => {
                        this.drawCtx.globalCompositeOperation = 'source-over';
                        this.drawCtx.drawImage(img, 0, 0);
                        this.save(); 
                    };
                    img.src = previousState;
                }
            },

            reset() {
                this.saveToHistory(); 
                this.drawCtx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
                this.state = this.drawCanvas.toDataURL(); // Lege string opslaan
            },

            setPen(color) {
                this.mode = 'pen';
                this.currentColor = color;
            },

            setEraser() {
                this.mode = 'eraser';
            },
            
            setTextMode() {
                this.mode = 'text';
            },

            setThickness(size) {
                this.currentThickness = size;
            }
        }"
        class="border border-gray-300 rounded-lg overflow-hidden shadow-sm bg-white dark:bg-gray-900 w-full"
    >
        <div class="flex flex-wrap items-center gap-3 p-2 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            
            <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg shadow-sm p-1">
                <button type="button" @click="mode = 'pen'" :class="mode === 'pen' ? 'bg-blue-100 text-blue-600' : 'text-gray-500'" class="p-2 rounded transition hover:bg-gray-100" title="Pen">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" /></svg>
                </button>
                <button type="button" @click="setTextMode()" :class="mode === 'text' ? 'bg-blue-100 text-blue-600' : 'text-gray-500'" class="p-2 rounded transition hover:bg-gray-100 font-bold font-serif" title="Tekst">T</button>
                <button type="button" @click="setEraser()" :class="mode === 'eraser' ? 'bg-blue-100 text-blue-600' : 'text-gray-500'" class="p-2 rounded transition hover:bg-gray-100" title="Gom">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg shadow-sm p-1 gap-2 px-3">
                <button type="button" @click="setThickness(2)" :class="currentThickness === 2 ? 'bg-gray-200 ring-1 ring-gray-400' : 'hover:bg-gray-100'" class="w-6 h-6 flex items-center justify-center rounded transition"><svg width="6" height="6"><circle cx="3" cy="3" r="2" fill="currentColor"/></svg></button>
                <button type="button" @click="setThickness(6)" :class="currentThickness === 6 ? 'bg-gray-200 ring-1 ring-gray-400' : 'hover:bg-gray-100'" class="w-6 h-6 flex items-center justify-center rounded transition"><svg width="10" height="10"><circle cx="5" cy="5" r="4" fill="currentColor"/></svg></button>
                <button type="button" @click="setThickness(12)" :class="currentThickness === 12 ? 'bg-gray-200 ring-1 ring-gray-400' : 'hover:bg-gray-100'" class="w-6 h-6 flex items-center justify-center rounded transition"><svg width="14" height="14"><circle cx="7" cy="7" r="6" fill="currentColor"/></svg></button>
            </div>

            <div class="flex items-center gap-1 bg-white dark:bg-gray-700 rounded-lg shadow-sm p-1 px-2">
                <template x-for="color in ['#FF0000', '#000000', '#0000FF', '#008000', '#FFA500']" :key="color">
                    <button type="button" @click="setPen(color)" class="w-6 h-6 rounded-full border border-gray-300 shadow-sm transition-transform hover:scale-110" :class="mode === 'pen' && currentColor === color ? 'ring-2 ring-offset-2 ring-blue-500' : ''" :style="{ backgroundColor: color }"></button>
                </template>
            </div>

            <div class="flex-grow"></div>

            <button type="button" @click="undo()" :disabled="history.length === 0" :class="history.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'" class="text-gray-700 bg-white p-2 rounded-lg transition shadow-sm border border-gray-300 font-bold flex items-center gap-1" title="Stap terug">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            </button>

            <button type="button" @click="reset()" class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition bg-white dark:bg-gray-700 shadow-sm border border-red-100" title="Wis alles">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            </button>
        </div>

        <div class="relative bg-white w-full" style="max-width: 100%; aspect-ratio: 800/500;">
            <canvas x-ref="bgCanvas" class="absolute top-0 left-0 w-full h-full pointer-events-none"></canvas>
            <canvas x-ref="drawCanvas" class="absolute top-0 left-0 w-full h-full cursor-crosshair touch-none"></canvas>
        </div>
    </div>
</x-dynamic-component>