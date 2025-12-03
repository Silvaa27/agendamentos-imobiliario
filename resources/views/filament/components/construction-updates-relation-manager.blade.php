<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Atualizações de Construção</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $record->constructionUpdates->count() }} atualização(ões) registadas
                </p>
            </div>
            @if ($record->constructionUpdates->isNotEmpty())
                <div class="flex items-center space-x-2">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Progresso total:</div>
                    @php
                        $totalProgress = $record->constructionUpdates->max('progress_percentage') ?? 0;
                        $color = match (true) {
                            $totalProgress >= 80
                                => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
                            $totalProgress >= 50 => 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30',
                            default => 'text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30',
                        };
                    @endphp
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $color }}">
                        {{ number_format($totalProgress, 0) }}%
                    </span>
                </div>
            @endif
        </div>

        @if ($record->constructionUpdates->isEmpty())
            <div
                class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <p class="text-gray-900 dark:text-gray-100 font-medium mb-1">Nenhuma atualização de construção</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Ainda não foram registadas atualizações para esta
                    oportunidade.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($record->constructionUpdates->sortByDesc('date') as $update)
                    <div
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 shadow-sm dark:shadow-gray-900/30">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $update->title }}</h4>
                                    @if ($update->user)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                            {{ $update->user->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($update->date)->format('d/m/Y') }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($update->created_at)->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4">
                                @php
                                    $progressColor = match (true) {
                                        $update->progress_percentage >= 80
                                            => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30',
                                        $update->progress_percentage >= 50
                                            => 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30',
                                        $update->progress_percentage >= 20
                                            => 'text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/30',
                                        default => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30',
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $progressColor }}">
                                    {{ number_format($update->progress_percentage, 0) }}%
                                </span>
                            </div>
                        </div>

                        <!-- Seção de Imagens - Thumbnails pequenos -->
                        @php
                            // Verifica se há imagens usando Spatie Media Library
                            $hasImages = false;
                            $images = [];

                            if (method_exists($update, 'getMedia')) {
                                $images = $update->getMedia('construction_photos');
                                $hasImages = $images->isNotEmpty();
                            }
                        @endphp

                        @if ($hasImages)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Fotos ({{ $images->count() }})
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($images as $image)
                                        <div class="relative group">
                                            <!-- Thumbnail pequeno -->
                                            @if ($image->hasGeneratedConversion('thumb'))
                                                <!-- Se tiver conversão 'thumb' -->
                                                <img src="{{ $image->getUrl('thumb') }}"
                                                    alt="{{ $image->getCustomProperty('alt', 'Foto da construção') }}"
                                                    class="w-16 h-16 object-cover rounded border border-gray-300 dark:border-gray-600 shadow-sm cursor-pointer hover:opacity-90 transition-opacity"
                                                    loading="lazy" onclick="openImageModal('{{ $image->getUrl() }}')"
                                                    title="Clique para ampliar">
                                            @else
                                                <!-- Se não tiver conversão, usa a imagem original redimensionada com CSS -->
                                                <img src="{{ $image->getUrl() }}"
                                                    alt="{{ $image->getCustomProperty('alt', 'Foto da construção') }}"
                                                    class="w-16 h-16 object-cover rounded border border-gray-300 dark:border-gray-600 shadow-sm cursor-pointer hover:opacity-90 transition-opacity"
                                                    loading="lazy" onclick="openImageModal('{{ $image->getUrl() }}')"
                                                    title="Clique para ampliar">
                                            @endif

                                            <!-- Badge com número se houver muitas imagens -->
                                            @if ($images->count() > 6 && $loop->iteration === 6)
                                                <div
                                                    class="absolute inset-0 bg-black bg-opacity-60 rounded flex items-center justify-center text-white text-xs font-bold">
                                                    +{{ $images->count() - 5 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($update->report)
                            <div
                                class="mt-4 {{ $hasImages ? 'pt-4 border-t border-gray-200 dark:border-gray-700' : '' }}">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Relatório
                                </p>
                                <div
                                    class="text-gray-700 dark:text-gray-300 whitespace-pre-line bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg text-sm">
                                    {!! nl2br(e($update->report)) !!}
                                </div>
                            </div>
                        @endif

                        @if ($update->created_at != $update->updated_at)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Atualizado: {{ \Carbon\Carbon::parse($update->updated_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Modal simples para visualizar imagem -->
<div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75 p-4">
    <div class="relative max-w-4xl max-h-[90vh] w-full">
        <button type="button" onclick="closeImageModal()"
            class="absolute -top-10 right-0 text-white hover:text-gray-300 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="modalImage" src="" alt=""
            class="w-full h-auto max-h-[80vh] object-contain rounded-lg">
    </div>
</div>

<script>
    function openImageModal(imageUrl) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        modalImage.src = imageUrl;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Fechar modal com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Fechar modal ao clicar fora
    document.getElementById('imageModal').addEventListener('click', (e) => {
        if (e.target.id === 'imageModal') {
            closeImageModal();
        }
    });
</script>
