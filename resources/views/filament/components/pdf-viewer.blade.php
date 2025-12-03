<div x-data="pdfViewer()" class="space-y-4">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Visualizador de PDF</h3>
            <p class="text-sm text-gray-600">
                Fatura: {{ $record->invoice_number }} • 
                {{ $record->supplier }} • 
                {{ count($mediaItems) }} {{ Str::plural('documento', count($mediaItems)) }}
            </p>
        </div>
        <button @click="$dispatch('close-modal')" 
                class="text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navegação entre documentos -->
    @if(count($mediaItems) > 1)
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Documentos disponíveis:</span>
                <span class="text-sm text-gray-500">
                    <span x-text="currentIndex + 1"></span>/{{ count($mediaItems) }}
                </span>
            </div>
            <div class="flex space-x-2 overflow-x-auto">
                @foreach($mediaItems as $index => $media)
                    <button @click="goToDocument({{ $index }})"
                            :class="currentIndex === {{ $index }} ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                            class="px-3 py-2 border rounded text-sm font-medium transition-colors">
                        {{ $index + 1 }}. {{ Str::limit($media->name, 20) }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Visualizador de PDF -->
    <div class="bg-gray-100 rounded-lg p-4 min-h-[500px]">
        @foreach($mediaItems as $index => $media)
            <div x-show="currentIndex === {{ $index }}" 
                 x-transition
                 class="h-full">
                @if($media->mime_type === 'application/pdf')
                    <!-- Embed do PDF -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $media->name }}</h4>
                                    <p class="text-sm text-gray-500">PDF • {{ formatFileSize($media->size) }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ $media->getUrl() }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700">
                                    Abrir em nova aba
                                </a>
                                <a href="{{ $media->getUrl() }}" 
                                   download="{{ $media->name }}"
                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    Baixar
                                </a>
                            </div>
                        </div>
                        
                        <!-- Embed do PDF -->
                        <iframe src="{{ $media->getUrl() }}#toolbar=0&navpanes=0&scrollbar=0" 
                                class="w-full h-[500px] border border-gray-300 rounded"
                                title="Visualização do PDF">
                        </iframe>
                    </div>
                @else
                    <!-- Para outros tipos de arquivo (imagens) -->
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                @if(str_starts_with($media->mime_type, 'image/'))
                                    <svg class="w-8 h-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                @endif
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $media->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $media->mime_type }} • {{ formatFileSize($media->size) }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ $media->getUrl() }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    Visualizar
                                </a>
                                <a href="{{ $media->getUrl() }}" 
                                   download="{{ $media->name }}"
                                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    Baixar
                                </a>
                            </div>
                        </div>
                        
                        @if(str_starts_with($media->mime_type, 'image/'))
                            <img src="{{ $media->getUrl() }}" 
                                 alt="{{ $media->name }}"
                                 class="max-w-full max-h-[500px] mx-auto rounded border border-gray-300">
                        @else
                            <div class="text-center py-20 bg-gray-50 rounded border border-gray-300">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500">Pré-visualização não disponível</p>
                                <p class="text-sm text-gray-400 mt-1">Faça download para visualizar o arquivo</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Controles de navegação -->
    @if(count($mediaItems) > 1)
        <div class="flex items-center justify-between">
            <button @click="prevDocument" 
                    :disabled="currentIndex === 0"
                    :class="currentIndex === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Anterior
            </button>

            <div class="text-center">
                <span class="text-sm font-medium text-gray-700">
                    Documento <span x-text="currentIndex + 1"></span> de {{ count($mediaItems) }}
                </span>
            </div>

            <button @click="nextDocument" 
                    :disabled="currentIndex === {{ count($mediaItems) - 1 }}"
                    :class="currentIndex === {{ count($mediaItems) - 1 }} ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white">
                Próximo
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Informações da fatura -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Informações da Fatura</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500">Número:</span>
                <p class="text-sm font-medium">{{ $record->invoice_number }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Fornecedor:</span>
                <p class="text-sm font-medium">{{ $record->supplier }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Valor:</span>
                <p class="text-sm font-medium">€ {{ number_format($record->amount, 2, ',', ' ') }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Data:</span>
                <p class="text-sm font-medium">{{ $record->invoice_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Estado:</span>
                <p class="text-sm font-medium">
                    @if($record->status === 'pago')
                        <span class="text-green-600">Pago</span>
                    @else
                        <span class="text-orange-600">Pendente</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-xs text-gray-500">Descrição:</span>
                <p class="text-sm font-medium truncate">{{ $record->description }}</p>
            </div>
        </div>
    </div>
</div>

<script>
function pdfViewer() {
    return {
        currentIndex: 0,
        
        prevDocument() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },
        
        nextDocument() {
            if (this.currentIndex < {{ count($mediaItems) - 1 }}) {
                this.currentIndex++;
            }
        },
        
        goToDocument(index) {
            this.currentIndex = index;
        }
    }
}

// Helper function para formatar tamanho de arquivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>