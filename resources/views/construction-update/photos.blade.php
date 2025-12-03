<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotos: {{ $constructionUpdate->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
            overflow: hidden;
        }

        .photo-transition-enter {
            opacity: 0;
        }

        .photo-transition-enter-active {
            opacity: 1;
            transition: opacity 300ms ease-in;
        }

        .photo-transition-leave {
            opacity: 1;
        }

        .photo-transition-leave-active {
            opacity: 0;
            transition: opacity 300ms ease-in;
        }

        .modal-enter-active,
        .modal-leave-active {
            transition: opacity 0.3s ease;
        }

        .modal-enter-from,
        .modal-leave-to {
            opacity: 0;
        }
    </style>
</head>

<body class="bg-black text-white">
    <div x-data="fullscreenGallery()" x-init="init()" class="h-screen w-screen overflow-hidden"
        @keydown.window="handleKeydown($event)">

        <!-- Botões superiores -->
        <div class="fixed top-4 right-4 z-50 flex space-x-2">
            <!-- Botão informações -->
            <button @click="showInfo = !showInfo"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                :class="showInfo ? 'bg-blue-600' : ''" title="Informações da foto">
                <i class="fas fa-info"></i>
            </button>

            <!-- Botão download -->
            <button @click="downloadPhoto"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors" title="Baixar foto">
                <i class="fas fa-download"></i>
            </button>

            <!-- Botão fechar -->
            <button @click="closeGallery"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                title="Fechar galeria">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Contador -->
        <div class="fixed top-4 left-4 z-50 bg-black/50 text-white px-4 py-2 rounded-full text-sm">
            <span x-text="currentIndex + 1"></span> / {{ count($photos) }}
        </div>

        <!-- Área da imagem -->
        <div class="h-full w-full flex items-center justify-center relative">
            @foreach ($photos as $index => $photo)
                <div x-show="currentIndex === {{ $index }}" x-transition:enter="photo-transition-enter"
                    x-transition:enter-end="photo-transition-enter-active" x-transition:leave="photo-transition-leave"
                    x-transition:leave-end="photo-transition-leave-active"
                    class="absolute inset-0 flex items-center justify-center">
                    <img src="{{ $photo->getUrl() }}" alt="Foto {{ $index + 1 }}"
                        class="max-w-full max-h-full object-contain" @load="isLoading = false" loading="lazy"
                        id="photo-{{ $index }}">
                </div>
            @endforeach

            <!-- Loading -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center">
                <div class="w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
            </div>
        </div>

        <!-- Botão anterior -->
        <button @click="prevPhoto" x-show="currentIndex > 0"
            class="fixed left-4 top-1/2 transform -translate-y-1/2 z-50 p-4 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Botão próximo -->
        <button @click="nextPhoto" x-show="currentIndex < {{ count($photos) - 1 }}"
            class="fixed right-4 top-1/2 transform -translate-y-1/2 z-50 p-4 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Miniaturas (opcional) -->
        @if (count($photos) > 1)
            <div
                class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 bg-black/50 backdrop-blur-sm rounded-lg p-2">
                <div class="flex space-x-2 overflow-x-auto max-w-[90vw]">
                    @foreach ($photos as $index => $photo)
                        <button @click="goToPhoto({{ $index }})"
                            :class="currentIndex === {{ $index }} ? 'ring-2 ring-white' : 'opacity-60 hover:opacity-100'"
                            class="flex-shrink-0 w-16 h-16 rounded overflow-hidden transition-all">
                            <img src="{{ $photo->getUrl() }}" alt="Thumb {{ $index + 1 }}"
                                class="w-full h-full object-cover" loading="lazy">
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Modal de informações -->
        <div x-show="showInfo" x-transition:enter="modal-enter-active" x-transition:enter-start="modal-enter-from"
            x-transition:leave="modal-leave-active" x-transition:leave-end="modal-leave-to"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
            <div class="bg-gray-900 rounded-xl max-w-md w-full max-h-[80vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informações da Foto
                        </h2>
                        <button @click="showInfo = false" class="p-2 hover:bg-gray-800 rounded-full transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Informações básicas -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm text-gray-400 mb-1">Nome do arquivo</h3>
                                <p class="font-medium truncate" x-text="currentPhoto.name"></p>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-400 mb-1">Tamanho</h3>
                                <p class="font-medium" x-text="currentPhoto.formattedSize"></p>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-400 mb-1">Tipo</h3>
                                <p class="font-medium" x-text="currentPhoto.type"></p>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-400 mb-1">Data de upload</h3>
                                <p class="font-medium" x-text="currentPhoto.formattedDate"></p>
                            </div>
                        </div>

                        <!-- Dimensões da imagem -->
                        <div x-show="currentPhoto.dimensions" class="pt-4 border-t border-gray-700">
                            <h3 class="text-sm text-gray-400 mb-1">Dimensões</h3>
                            <p class="font-medium" x-text="currentPhoto.dimensions"></p>
                        </div>

                        <!-- Informações do projeto -->
                        <div class="pt-4 border-t border-gray-700">
                            <h3 class="text-sm text-gray-400 mb-1">Projeto</h3>
                            <p class="font-medium">{{ $constructionUpdate->title }}</p>
                            <p class="text-sm text-gray-400">
                                {{ $constructionUpdate->date->format('d/m/Y') }} •
                                {{ $constructionUpdate->user->name ?? 'N/A' }}
                            </p>
                        </div>

                        <!-- Ações -->
                        <div class="pt-6 border-t border-gray-700">
                            <div class="flex flex-wrap gap-3">
                                <button @click="downloadPhoto"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Baixar Foto
                                </button>
                                <button @click="sharePhoto"
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-share-alt mr-2"></i>
                                    Compartilhar
                                </button>
                                <button @click="copyPhotoLink"
                                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-link mr-2"></i>
                                    Copiar Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast para mensagens -->
        <div x-show="showToast" x-transition
            class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span x-text="toastMessage"></span>
            </div>
        </div>
    </div>

    <script>
        function fullscreenGallery() {
            return {
                currentIndex: 0,
                isLoading: true,
                showInfo: false,
                showToast: false,
                toastMessage: '',
                photos: {!! json_encode(
                    $photos->map(function ($photo) {
                        return [
                            'url' => $photo->getUrl(),
                            'name' => $photo->name,
                            'size' => $photo->size,
                            'type' => $photo->mime_type,
                            'created_at' => $photo->created_at->toIso8601String(),
                        ];
                    }),
                ) !!},

                get currentPhoto() {
                    if (!this.photos[this.currentIndex]) return {
                        url: '',
                        name: '',
                        size: 0,
                        type: '',
                        created_at: '',
                        formattedSize: '0 Bytes',
                        formattedDate: 'Data indisponível',
                        dimensions: null
                    };

                    const photo = this.photos[this.currentIndex];

                    // Verifica se é array ou objeto
                    let url, name, size, type, created_at;

                    if (Array.isArray(photo)) {
                        // Se for array [url, name, size, type, created_at]
                        url = photo[0];
                        name = photo[1];
                        size = photo[2];
                        type = photo[3];
                        created_at = photo[4];
                    } else {
                        // Se for objeto {url, name, size, type, created_at}
                        url = photo.url;
                        name = photo.name;
                        size = photo.size;
                        type = photo.type;
                        created_at = photo.created_at;
                    }

                    return {
                        url: url,
                        name: name || 'Sem nome',
                        size: size || 0,
                        type: type || 'Desconhecido',
                        created_at: created_at,
                        formattedSize: this.formatFileSize(size || 0),
                        formattedDate: created_at ?
                            new Date(created_at).toLocaleDateString('pt-PT', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : 'Data indisponível',
                        dimensions: this.getImageDimensions(this.currentIndex)
                    };
                },

                init() {
                    // Entrar em fullscreen
                    this.requestFullscreen();

                    // Carregar dimensões da imagem atual
                    this.loadImageDimensions();

                    // Preload imagens adjacentes
                    this.preloadImages();
                },

                requestFullscreen() {
                    if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.log('Fullscreen não suportado:', err);
                        });
                    }
                },

                handleKeydown(e) {
                    switch (e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            this.prevPhoto();
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            this.nextPhoto();
                            break;
                        case 'Escape':
                            if (this.showInfo) {
                                this.showInfo = false;
                            } else {
                                this.closeGallery();
                            }
                            break;
                        case 'i':
                        case 'I':
                            e.preventDefault();
                            this.showInfo = !this.showInfo;
                            break;
                        case 'd':
                        case 'D':
                            e.preventDefault();
                            this.downloadPhoto();
                            break;
                    }
                },

                prevPhoto() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.isLoading = true;
                        this.loadImageDimensions();
                    }
                },

                nextPhoto() {
                    if (this.currentIndex < {{ count($photos) - 1 }}) {
                        this.currentIndex++;
                        this.isLoading = true;
                        this.loadImageDimensions();
                    }
                },

                goToPhoto(index) {
                    this.currentIndex = index;
                    this.isLoading = true;
                    this.loadImageDimensions();
                },

                async downloadPhoto() {
                    const photo = this.currentPhoto;
                    if (!photo.url) {
                        this.showMessage('Erro: URL da foto não disponível');
                        return;
                    }

                    try {
                        const response = await fetch(photo.url);
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = photo.name || `foto-${this.currentIndex + 1}.jpg`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        this.showMessage('Download iniciado!');
                    } catch (error) {
                        console.error('Erro ao baixar foto:', error);
                        this.showMessage('Erro ao baixar foto');
                    }
                },

                async sharePhoto() {
                    const photo = this.currentPhoto;
                    if (!photo.url) {
                        this.showMessage('Erro: URL da foto não disponível');
                        return;
                    }

                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: 'Foto do Projeto: {{ $constructionUpdate->title }}',
                                text: 'Confira esta foto do projeto',
                                url: photo.url,
                            });
                            this.showMessage('Compartilhado com sucesso!');
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                this.copyPhotoLink();
                            }
                        }
                    } else {
                        this.copyPhotoLink();
                    }
                },

                async copyPhotoLink() {
                    const photo = this.currentPhoto;
                    if (!photo.url) {
                        this.showMessage('Erro: URL da foto não disponível');
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(photo.url);
                        this.showMessage('Link copiado para a área de transferência!');
                    } catch (error) {
                        console.error('Erro ao copiar link:', error);
                        this.showMessage('Erro ao copiar link');
                    }
                },

                loadImageDimensions() {
                    // Carrega as dimensões da imagem atual
                    const img = document.getElementById(`photo-${this.currentIndex}`);
                    if (img && img.complete) {
                        this.getImageDimensions(this.currentIndex);
                    }
                },

                getImageDimensions(index) {
                    const img = document.getElementById(`photo-${index}`);
                    if (img && img.naturalWidth && img.naturalHeight) {
                        return `${img.naturalWidth} × ${img.naturalHeight} pixels`;
                    }
                    return null;
                },

                formatFileSize(bytes) {
                    if (!bytes || bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                preloadImages() {
                    // Preload imagens próximas
                    const indices = [
                        this.currentIndex - 1,
                        this.currentIndex + 1,
                        this.currentIndex - 2,
                        this.currentIndex + 2,
                    ].filter(i => i >= 0 && i < {{ count($photos) }});

                    indices.forEach(index => {
                        const photo = this.photos[index];
                        const url = Array.isArray(photo) ? photo[0] : photo.url;
                        if (url) {
                            const img = new Image();
                            img.src = url;
                        }
                    });
                },

                showMessage(message) {
                    this.toastMessage = message;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                },

                closeGallery() {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                    setTimeout(() => window.history.back(), 100);
                }
            }
        }

        // Suporte para swipe em dispositivos móveis
        document.addEventListener('touchstart', handleTouchStart, false);
        document.addEventListener('touchend', handleTouchEnd, false);

        let xDown = null;
        let yDown = null;

        function handleTouchStart(evt) {
            xDown = evt.touches[0].clientX;
            yDown = evt.touches[0].clientY;
        }

        function handleTouchEnd(evt) {
            if (!xDown || !yDown) return;

            const xUp = evt.changedTouches[0].clientX;
            const yUp = evt.changedTouches[0].clientY;

            const xDiff = xDown - xUp;
            const yDiff = yDown - yUp;

            // Detecta swipe horizontal
            if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
                if (xDiff > 0) {
                    // Swipe para esquerda - próxima foto
                    window.dispatchEvent(new KeyboardEvent('keydown', {
                        key: 'ArrowRight'
                    }));
                } else {
                    // Swipe para direita - foto anterior
                    window.dispatchEvent(new KeyboardEvent('keydown', {
                        key: 'ArrowLeft'
                    }));
                }
            }

            xDown = null;
            yDown = null;
        }
    </script>
</body>

</html>
