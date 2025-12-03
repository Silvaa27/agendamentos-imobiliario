{{-- resources/views/construction-update/photos.blade.php --}}
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotos: {{ $constructionUpdate->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
            overflow: hidden;
        }
        
        .fullscreen-gallery {
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        
        .fullscreen-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .nav-button {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 1000;
        }
        
        .nav-button:hover {
            background: rgba(0,0,0,0.8);
        }
        
        .nav-button.prev {
            left: 20px;
        }
        
        .nav-button.next {
            right: 20px;
        }
        
        .nav-button svg {
            width: 30px;
            height: 30px;
        }
        
        .counter {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 1000;
        }
        
        .close-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
        }
        
        .close-button:hover {
            background: rgba(0,0,0,0.9);
        }
    </style>
</head>
<body>
    <div x-data="fullscreenGallery()" class="fullscreen-gallery">
        <!-- Botão fechar -->
        <button class="close-button" @click="closeFullscreen">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <!-- Contador -->
        <div class="counter">
            <span x-text="currentIndex + 1"></span> / {{ count($photos) }}
        </div>
        
        <!-- Botão anterior -->
        <button class="nav-button prev" 
                x-show="currentIndex > 0"
                @click="prevPhoto">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        
        <!-- Foto atual -->
        @foreach($photos as $index => $photo)
            <div x-show="currentIndex === {{ $index }}" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="photo-container">
                <img src="{{ $photo->getUrl() }}" 
                     alt="Foto {{ $index + 1 }}"
                     class="fullscreen-image"
                     @load="imageLoaded = true">
            </div>
        @endforeach
        
        <!-- Botão próximo -->
        <button class="nav-button next" 
                x-show="currentIndex < {{ count($photos) - 1 }}"
                @click="nextPhoto">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    
    <script>
    function fullscreenGallery() {
        return {
            currentIndex: 0,
            imageLoaded: false,
            
            init() {
                // Entrar em full screen automaticamente
                this.requestFullscreen();
                
                // Navegação por teclado
                document.addEventListener('keydown', (e) => {
                    switch(e.key) {
                        case 'ArrowLeft':
                            this.prevPhoto();
                            break;
                        case 'ArrowRight':
                            this.nextPhoto();
                            break;
                        case 'Escape':
                            this.closeFullscreen();
                            break;
                    }
                });
                
                // Swipe em dispositivos móveis
                let startX = 0;
                document.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                });
                
                document.addEventListener('touchend', (e) => {
                    const endX = e.changedTouches[0].clientX;
                    const diff = startX - endX;
                    
                    if (Math.abs(diff) > 50) { // Limite mínimo para swipe
                        if (diff > 0) {
                            this.nextPhoto(); // Swipe para esquerda
                        } else {
                            this.prevPhoto(); // Swipe para direita
                        }
                    }
                });
            },
            
            requestFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.log('Fullscreen não suportado:', err);
                    });
                }
            },
            
            closeFullscreen() {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                // Voltar para a página anterior
                setTimeout(() => window.history.back(), 100);
            },
            
            prevPhoto() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.imageLoaded = false;
                }
            },
            
            nextPhoto() {
                if (this.currentIndex < {{ count($photos) - 1 }}) {
                    this.currentIndex++;
                    this.imageLoaded = false;
                }
            }
        }
    }
    </script>
</body>
</html>