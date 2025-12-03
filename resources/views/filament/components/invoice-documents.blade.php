<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos: {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #f3f4f6;
            overflow: hidden;
        }

        .document-transition-enter {
            opacity: 0;
            transform: translateX(20px);
        }

        .document-transition-enter-active {
            opacity: 1;
            transform: translateX(0);
            transition: all 300ms ease-in;
        }

        .document-transition-leave {
            opacity: 1;
            transform: translateX(0);
        }

        .document-transition-leave-active {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 300ms ease-in;
        }

        .pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div x-data="documentViewer()" x-init="init()" class="h-screen w-screen overflow-hidden"
        @keydown.window="handleKeydown($event)">

        <!-- Botões superiores -->
        <div class="fixed top-4 right-4 z-50 flex space-x-2">
            <button @click="showInfo = !showInfo"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                :class="showInfo ? 'bg-blue-600' : ''"
                title="Informações do documento">
                <i class="fas fa-info"></i>
            </button>

            <button @click="downloadDocument"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                title="Baixar documento">
                <i class="fas fa-download"></i>
            </button>

            <button @click="printDocument"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                title="Imprimir documento">
                <i class="fas fa-print"></i>
            </button>

            <button @click="toggleFullscreen"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                :title="isFullscreen ? 'Sair da tela cheia' : 'Tela cheia'">
                <i class="fas" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
            </button>

            <button @click="closeViewer"
                class="p-3 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors"
                title="Fechar visualizador">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Contador -->
        <div class="fixed top-4 left-4 z-50 bg-black/50 text-white px-4 py-2 rounded-full text-sm">
            <span x-text="currentIndex + 1"></span> / {{ count($documents) }}
        </div>

        <!-- Área principal do documento -->
        <div class="h-full w-full flex items-center justify-center relative pt-16 pb-20">
            @foreach ($documents as $index => $document)
                <div x-show="currentIndex === {{ $index }}"
                    x-transition:enter="document-transition-enter"
                    x-transition:enter-end="document-transition-enter-active"
                    x-transition:leave="document-transition-leave"
                    x-transition:leave-end="document-transition-leave-active"
                    class="absolute inset-0 flex items-center justify-center p-4">
                    
                    @if($document->mime_type === 'application/pdf')
                        <div class="w-full h-full bg-white rounded-lg shadow-2xl overflow-hidden">
                            <iframe src="{{ $document->getUrl() }}#toolbar=1&navpanes=1&scrollbar=1" 
                                    class="pdf-viewer"
                                    title="Visualização do PDF: {{ $document->name }}"
                                    @load="isLoading = false">
                            </iframe>
                        </div>
                    @elseif(str_starts_with($document->mime_type, 'image/'))
                        <div class="max-w-full max-h-full">
                            <img src="{{ $document->getUrl() }}" 
                                 alt="{{ $document->name }}"
                                 class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                                 @load="isLoading = false"
                                 loading="lazy">
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow-2xl p-8 max-w-md text-center">
                            <svg class="w-20 h-20 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $document->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ $document->mime_type }}</p>
                            <div class="flex justify-center space-x-4">
                                <a href="{{ $document->getUrl() }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    Abrir
                                </a>
                                <a href="{{ $document->getUrl() }}" 
                                   download
                                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-download mr-2"></i>
                                    Baixar
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Loading -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <div class="w-16 h-16 border-4 border-blue-300 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-600">Carregando documento...</p>
                </div>
            </div>
        </div>

        <!-- Botão anterior -->
        <button @click="prevDocument" x-show="currentIndex > 0"
            class="fixed left-4 top-1/2 transform -translate-y-1/2 z-50 p-4 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Botão próximo -->
        <button @click="nextDocument" x-show="currentIndex < {{ count($documents) - 1 }}"
            class="fixed right-4 top-1/2 transform -translate-y-1/2 z-50 p-4 bg-black/50 hover:bg-black/70 text-white rounded-full transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Miniaturas -->
        @if (count($documents) > 1)
            <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
                <div class="flex space-x-2 overflow-x-auto max-w-[90vw] bg-black/50 backdrop-blur-sm rounded-lg p-3">
                    @foreach ($documents as $index => $document)
                        <button @click="goToDocument({{ $index }})"
                                :class="currentIndex === {{ $index }} ? 'ring-2 ring-white scale-110' : 'opacity-70 hover:opacity-100'"
                                class="flex-shrink-0 w-20 h-20 rounded overflow-hidden transition-all relative">
                            
                            @if($document->mime_type === 'application/pdf')
                                <div class="w-full h-full bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                </div>
                            @elseif(str_starts_with($document->mime_type, 'image/'))
                                <img src="{{ $document->getUrl() }}" 
                                     alt="Thumbnail"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-file text-blue-500 text-2xl"></i>
                                </div>
                            @endif
                            
                            <div class="absolute top-1 right-1 bg-black/70 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $index + 1 }}
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Modal de informações -->
        <div x-show="showInfo" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
            <div class="bg-white rounded-xl max-w-md w-full max-h-[80vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                            Informações do Documento
                        </h2>
                        <button @click="showInfo = false"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i class="fas fa-times text-gray-500"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Documento Atual</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400">Nome</p>
                                    <p class="text-sm font-medium truncate" x-text="currentDocument.name"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Tamanho</p>
                                    <p class="text-sm font-medium" x-text="currentDocument.formattedSize"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Tipo</p>
                                    <p class="text-sm font-medium" x-text="currentDocument.type"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Data</p>
                                    <p class="text-sm font-medium" x-text="currentDocument.formattedDate"></p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Informações da Fatura</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400">Número</p>
                                    <p class="text-sm font-medium">{{ $invoice->invoice_number }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Fornecedor</p>
                                    <p class="text-sm font-medium">{{ $invoice->supplier }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Valor</p>
                                    <p class="text-sm font-medium">€ {{ number_format($invoice->amount, 2, ',', ' ') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Data</p>
                                    <p class="text-sm font-medium">{{ $invoice->invoice_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Estado</p>
                                    <p class="text-sm font-medium">
                                        @if($invoice->status === 'pago')
                                            <span class="text-green-600">Pago</span>
                                        @else
                                            <span class="text-orange-600">Pendente</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Descrição</p>
                                    <p class="text-sm font-medium">{{ $invoice->description }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t">
                            <div class="flex flex-wrap gap-3">
                                <button @click="downloadDocument"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Baixar Documento
                                </button>
                                <button @click="printDocument"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-print mr-2"></i>
                                    Imprimir
                                </button>
                                <button @click="shareDocument"
                                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center transition-colors">
                                    <i class="fas fa-share-alt mr-2"></i>
                                    Compartilhar
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
        function documentViewer() {
            return {
                currentIndex: 0,
                isLoading: true,
                showInfo: false,
                showToast: false,
                toastMessage: '',
                isFullscreen: false,
                documents: {!! json_encode($documents->map(function($doc) {
                    return [
                        'url' => $doc->getUrl(),
                        'name' => $doc->name,
                        'size' => $doc->size,
                        'type' => $doc->mime_type,
                        'created_at' => $doc->created_at->toIso8601String(),
                    ];
                })) !!},

                get currentDocument() {
                    if (!this.documents[this.currentIndex]) return {
                        url: '',
                        name: '',
                        size: 0,
                        type: '',
                        created_at: '',
                        formattedSize: '0 Bytes',
                        formattedDate: 'Data indisponível'
                    };
                    
                    const doc = this.documents[this.currentIndex];
                    
                    let url, name, size, type, created_at;
                    
                    if (Array.isArray(doc)) {
                        url = doc[0];
                        name = doc[1];
                        size = doc[2];
                        type = doc[3];
                        created_at = doc[4];
                    } else {
                        url = doc.url;
                        name = doc.name;
                        size = doc.size;
                        type = doc.type;
                        created_at = doc.created_at;
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
                            }) : 'Data indisponível'
                    };
                },

                init() {
                    this.requestFullscreen();
                },

                requestFullscreen() {
                    if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen()
                            .then(() => this.isFullscreen = true)
                            .catch(err => console.log('Fullscreen não suportado:', err));
                    }
                },

                toggleFullscreen() {
                    if (!this.isFullscreen) {
                        document.documentElement.requestFullscreen()
                            .then(() => this.isFullscreen = true)
                            .catch(err => this.showMessage('Erro ao entrar em tela cheia'));
                    } else {
                        document.exitFullscreen()
                            .then(() => this.isFullscreen = false);
                    }
                },

                handleKeydown(e) {
                    switch (e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            this.prevDocument();
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            this.nextDocument();
                            break;
                        case 'Escape':
                            if (this.showInfo) {
                                this.showInfo = false;
                            } else if (this.isFullscreen) {
                                this.toggleFullscreen();
                            } else {
                                this.closeViewer();
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
                            this.downloadDocument();
                            break;
                        case 'p':
                        case 'P':
                            e.preventDefault();
                            this.printDocument();
                            break;
                        case 'f':
                        case 'F':
                            e.preventDefault();
                            this.toggleFullscreen();
                            break;
                    }
                },

                prevDocument() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.isLoading = true;
                    }
                },

                nextDocument() {
                    if (this.currentIndex < {{ count($documents) - 1 }}) {
                        this.currentIndex++;
                        this.isLoading = true;
                    }
                },

                goToDocument(index) {
                    this.currentIndex = index;
                    this.isLoading = true;
                },

                async downloadDocument() {
                    const doc = this.currentDocument;
                    if (!doc.url) {
                        this.showMessage('Erro: URL do documento não disponível');
                        return;
                    }
                    
                    try {
                        const response = await fetch(doc.url);
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = doc.name || `documento-${this.currentIndex + 1}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        this.showMessage('Download iniciado!');
                    } catch (error) {
                        console.error('Erro ao baixar documento:', error);
                        this.showMessage('Erro ao baixar documento');
                    }
                },

                printDocument() {
                    const doc = this.currentDocument;
                    if (doc.url) {
                        const printWindow = window.open(doc.url, '_blank');
                        if (printWindow) {
                            printWindow.onload = function() {
                                printWindow.print();
                            };
                        }
                    } else {
                        this.showMessage('Não é possível imprimir este documento');
                    }
                },

                async shareDocument() {
                    const doc = this.currentDocument;
                    if (!doc.url) {
                        this.showMessage('Erro: URL do documento não disponível');
                        return;
                    }
                    
                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: 'Documento da Fatura: {{ $invoice->invoice_number }}',
                                text: 'Confira este documento da fatura',
                                url: doc.url,
                            });
                            this.showMessage('Compartilhado com sucesso!');
                        } catch (error) {
                            if (error.name !== 'AbortError') {
                                this.copyDocumentLink();
                            }
                        }
                    } else {
                        this.copyDocumentLink();
                    }
                },

                async copyDocumentLink() {
                    const doc = this.currentDocument;
                    if (!doc.url) {
                        this.showMessage('Erro: URL do documento não disponível');
                        return;
                    }
                    
                    try {
                        await navigator.clipboard.writeText(doc.url);
                        this.showMessage('Link copiado para a área de transferência!');
                    } catch (error) {
                        console.error('Erro ao copiar link:', error);
                        this.showMessage('Erro ao copiar link');
                    }
                },

                formatFileSize(bytes) {
                    if (!bytes || bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                showMessage(message) {
                    this.toastMessage = message;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                },

                closeViewer() {
                    if (this.isFullscreen && document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                    setTimeout(() => window.history.back(), 100);
                }
            }
        }

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

            if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
                if (xDiff > 0) {
                    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowRight' }));
                } else {
                    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowLeft' }));
                }
            }

            xDown = null;
            yDown = null;
        }
    </script>
</body>
</html>