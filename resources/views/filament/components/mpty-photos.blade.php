<div class="py-16 px-4 text-center">
    <div class="max-w-md mx-auto">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-gray-100 to-gray-200 mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhuma foto disponível</h3>
        <p class="text-gray-600 mb-4">Esta atualização de obra não possui fotos anexadas.</p>
        <div class="space-y-2 text-sm text-gray-500">
            <p>Para adicionar fotos à atualização:</p>
            <ol class="list-decimal list-inside space-y-1">
                <li>Clique em "Editar"</li>
                <li>Vá até a seção "Galeria de Fotos"</li>
                <li>Arraste e solte suas fotos ou clique para selecionar</li>
                <li>Salve as alterações</li>
            </ol>
        </div>
        <div class="mt-6">
            <button @click="$dispatch('close-modal')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </button>
        </div>
    </div>
</div>