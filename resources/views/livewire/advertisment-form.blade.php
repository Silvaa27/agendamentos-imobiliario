<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        {{-- Formulário Submetido com Sucesso --}}
        <script>
            console.log('Alpine está definido?', typeof Alpine !== 'undefined');
            console.log('Livewire está definido?', typeof Livewire !== 'undefined');
        </script>
        @if ($formSubmitted)
            <div class="bg-success-50 border border-success-200 rounded-lg p-8 text-center shadow-sm">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-12 h-12 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-success-800 mb-3">Formulário Submetido!</h3>
                <p class="text-success-700 text-lg mb-6">{{ $successMessage }}</p>
                <div class="bg-success-100 border border-success-300 rounded-lg p-4 inline-block mb-6">
                    <p class="text-success-800 font-medium">✅ A sua submissão foi recebida com sucesso</p>
                </div>
                <div class="mt-6">
                    <button wire:click="closePage" type="button"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Fechar
                    </button>
                </div>
            </div>

            {{-- Formulário Desativado --}}
        @elseif (!$formAvailable)
            <div class="bg-danger-50 border border-danger-200 rounded-lg p-8 text-center shadow-sm">
                <div class="flex items-center justify-center gap-3 mb-4">
                    <svg class="w-12 h-12 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-danger-800 mb-3">Formulário Indisponível</h3>
                <p class="text-danger-700 text-lg mb-6">
                    {{ $unavailableMessage ?? 'Não é possível aceder a este formulário no momento.' }}</p>
                <div class="bg-danger-100 border border-danger-300 rounded-lg p-4 inline-block">
                    <p class="text-danger-800 font-medium">❌ Não preenche os requisitos para a marcação da visita.</p>
                </div>
                <div class="mt-6">
                    <button wire:click="closePage" type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-danger-600 hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500">
                        Fechar
                    </button>
                </div>
            </div>

            {{-- Formulário Ativo --}}
        @else
            {{-- Container do Formulário --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-primary-50 to-primary-50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary-100 rounded-lg">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $advertise->title ?? 'Formulário' }}</h2>
                            @if ($advertise->description)
                                <p class="text-gray-600 mt-1">{{ $advertise->description }}</p>
                            @else
                                <p class="text-gray-600 mt-1">Preencha todos os campos obrigatórios para submeter o formulário</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Form Content --}}
                <div class="p-8">
                    <form wire:submit="submit" class="space-y-6">
                        {{ $this->form }}

                        {{-- Loading State --}}
                        @if ($isSubmitting)
                            <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-primary-800 font-medium">A processar o formulário...</span>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Script para fechar a página -->
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('close-page-execute', () => {
            window.close();
        });
    });
</script>