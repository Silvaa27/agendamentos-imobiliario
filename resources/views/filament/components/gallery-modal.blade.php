<div x-data="galleryCarousel()" class="space-y-6 max-h-[90vh] overflow-y-auto p-1">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between sticky top-0 bg-white pb-4 pt-2 z-20 border-b">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Galeria de Documentos</h3>
            <p class="text-sm text-gray-600">Fatura: {{ $record->invoice_number }} • {{ $record->supplier }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                        clip-rule="evenodd" />
                </svg>
                {{ count($mediaItems) }} {{ Str::plural('arquivo', count($mediaItems)) }}
            </span>
            <button @click="$dispatch('close-modal')" type="button"
                class="text-gray-400 hover:text-gray-500 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Controles principais -->
    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
        <div class="flex items-center space-x-4">
            <button @click="prev()" :disabled="currentIndex === 0"
                :class="currentIndex === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Anterior
            </button>

            <div class="text-center min-w-[100px]">
                <span class="text-2xl font-bold text-gray-900" x-text="currentIndex + 1"></span>
                <span class="text-gray-500"> / {{ count($mediaItems) }}</span>
            </div>

            <button @click="next()" :disabled="currentIndex === {{ count($mediaItems) - 1 }}"
                :class="currentIndex === {{ count($mediaItems) - 1 }} ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700">
                Próximo
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Navegação:</span>
            <kbd
                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded">←</kbd>
            <kbd
                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded">→</kbd>
            <kbd
                class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-300 rounded">ESC</kbd>
        </div>
    </div>

    <!-- Área principal do carrossel -->
    <div
        class="relative bg-gradient-to-b from-gray-50 to-white rounded-xl p-4 min-h-[400px] flex items-center justify-center">
        <!-- Documento atual -->
        @foreach ($mediaItems as $index => $media)
            <div x-show="currentIndex === {{ $index }}" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-10"
                class="w-full">

                @if ($media->mime_type === 'application/pdf')
                    <!-- Visualização de PDF -->
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                            <div class="bg-red-50 border-b border-red-100 p-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-10 h-10 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h4 class="font-bold text-gray-900">{{ $media->name }}</h4>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ $media->getUrl() }}" target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Baixar PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="p-8">
                                    <iframe src="{{ $media->getUrl() }}#toolbar=0&navpanes=0"
                                        class="w-full h-[500px] rounded-lg border border-gray-200"
                                        title="Visualização do PDF">
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    @elseif(str_starts_with($media->mime_type, 'image/'))
                        <!-- Visualização de imagem -->
                        <div class="max-w-6xl mx-auto">
                            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                                <div
                                    class="p-4 bg-gradient-to-r from-gray-50 to-white border-b flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $media->name }}</h4>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ $media->getUrl() }}" target="_blank"
                                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Baixar
                                        </a>
                                    </div>
                                </div>
                                <div class="p-2">
                                    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}"
                                        class="max-w-full max-h-[70vh] mx-auto rounded-lg shadow-inner cursor-zoom-in"
                                        @click="zoomImage = !zoomImage"
                                        :class="zoomImage ?
                                            'fixed inset-0 z-50 w-auto h-auto max-w-none max-h-none m-0 p-4 bg-black bg-opacity-90 object-contain' :
                                            ''">
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Outros tipos de arquivo -->
                        <div class="max-w-2xl mx-auto">
                            <div class="bg-white rounded-xl shadow-2xl p-8 text-center">
                                <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $media->name }}</h4>
                                <p class="text-gray-600 mb-4">{{ $media->mime_type }} •
                                <div class="flex justify-center space-x-4">
                                    <a href="{{ $media->getUrl() }}" target="_blank"
                                        class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Visualizar
                                    </a>
                                    <a href="{{ $media->getUrl() }}" download
                                        class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Baixar
                                    </a>
                                </div>
                            </div>
                        </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Miniaturas -->
    @if (count($mediaItems) > 1)
        <div class="bg-gray-50 rounded-xl p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Miniaturas</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach ($mediaItems as $index => $media)
                    <button @click="goTo({{ $index }})"
                        :class="{
                            'ring-2 ring-blue-500 ring-offset-2': currentIndex === {{ $index }},
                            'hover:ring-1 hover:ring-blue-300': currentIndex !== {{ $index }}
                        }"
                        class="relative group rounded-lg overflow-hidden bg-white border border-gray-200 transition-all duration-200">
                        @if (str_starts_with($media->mime_type, 'image/'))
                            <img src="{{ $photo->getUrl() }}" alt="Thumbnail {{ $index + 1 }}"
                                class="w-full h-24 object-cover" loading="lazy">
                        @elseif($media->mime_type === 'application/pdf')
                            <div class="w-full h-24 bg-red-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        @else
                            <div class="w-full h-24 bg-blue-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        @endif
                        <div
                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity">
                        </div>
                        <div
                            class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                            <p class="text-xs text-white font-medium truncate">{{ $media->name }}</p>
                        </div>
                        <div
                            class="absolute top-1 right-1 bg-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-xs font-bold text-gray-700">{{ $index + 1 }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Informações do arquivo atual -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Informações do Arquivo</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($mediaItems as $index => $media)
                <div x-show="currentIndex === {{ $index }}" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Nome:</span>
                        <span class="text-sm font-medium text-gray-900 truncate"
                            title="{{ $media->name }}">{{ $media->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tipo:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $media->mime_type }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tamanho:</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Data:</span>
                        <span
                            class="text-sm font-medium text-gray-900">{{ $media->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex space-x-2">
                            <a href="{{ $media->getUrl() }}" target="_blank"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Abrir
                            </a>
                            <a href="{{ $media->getUrl() }}" download
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Baixar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function galleryCarousel() {
        return {
            currentIndex: 0,
            zoomImage: false,

            init() {
                // Navegação por teclado
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') {
                        this.prev();
                    } else if (e.key === 'ArrowRight') {
                        this.next();
                    } else if (e.key === 'Escape') {
                        if (this.zoomImage) {
                            this.zoomImage = false;
                        } else {
                            this.$dispatch('close-modal');
                        }
                    }
                });

                // Fechar zoom ao clicar fora
                if (this.zoomImage) {
                    document.addEventListener('click', (e) => {
                        if (e.target.tagName === 'IMG' && this.zoomImage) {
                            this.zoomImage = false;
                        }
                    });
                }
            },

            next() {
                if (this.currentIndex < {{ count($mediaItems) - 1 }}) {
                    this.currentIndex++;
                }
            },

            prev() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                }
            },

            goTo(index) {
                this.currentIndex = index;
                this.$el.parentElement.scrollTop = 0;
            }
        }
</script>
