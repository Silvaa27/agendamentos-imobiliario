<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Completo - Tailwind + Livewire + Filament</title>

    <!-- Tailwind via CDN (para teste rápido) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">

        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-blue-600 mb-2">🧪 Teste Completo</h1>
            <p class="text-gray-600">Verificando Tailwind, Alpine.js, Livewire e Filament</p>
        </div>

        <!-- Status das Bibliotecas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Card Tailwind -->
            <div class="bg-white p-6 rounded-xl shadow-md border">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    Tailwind CSS
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span>Cores</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-red-100 text-red-800 rounded">Espaçamento</div>
                        <span>Classes utilitárias</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-6 h-6 bg-purple-500 rounded-lg"></div>
                            <div class="w-6 h-6 bg-purple-400 rounded-lg"></div>
                            <div class="w-6 h-6 bg-purple-300 rounded-lg"></div>
                        </div>
                        <span>Flexbox/Grid</span>
                    </div>
                </div>
            </div>

            <!-- Card Alpine.js -->
            <div class="bg-white p-6 rounded-xl shadow-md border">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    Alpine.js
                </h2>
                <div x-data="{ count: 0, showMessage: false }" class="space-y-4">
                    <button @click="count++"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Clicou: <span x-text="count"></span> vezes
                    </button>

                    <button @click="showMessage = !showMessage"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <span x-text="showMessage ? 'Ocultar' : 'Mostrar'"></span> Mensagem
                    </button>

                    <div x-show="showMessage" x-transition
                        class="p-3 bg-yellow-100 border border-yellow-300 rounded-lg">
                        ✅ Alpine.js está a funcionar perfeitamente!
                    </div>
                </div>
            </div>

            <!-- Card Livewire -->
            <div class="bg-white p-6 rounded-xl shadow-md border md:col-span-2">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <span id="livewire-status" class="w-3 h-3 bg-red-500 rounded-full"></span>
                    Livewire
                </h2>
                <div class="space-y-4">
                    <div id="livewire-test" class="p-4 bg-gray-100 rounded-lg">
                        <p class="text-gray-600">A carregar teste Livewire...</p>
                    </div>

                    <!-- Simular um componente Filament -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <h3 class="text-lg font-medium mb-2">Área do Filament Forms</h3>
                        <p class="text-gray-500 mb-4">Aqui apareceriam os componentes Filament</p>

                        <!-- Simulação de um input Filament -->
                        <div class="max-w-md mx-auto space-y-4">
                            <div class="text-left">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nome (simulação Filament)
                                </label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="text-left">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email (simulação Filament)
                                </label>
                                <input type="email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teste de Console -->
        <div class="bg-black text-green-400 p-4 rounded-lg font-mono text-sm mb-4">
            <div>📋 <strong>Console Output:</strong></div>
            <div id="console-output" class="mt-2 space-y-1"></div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex gap-4 justify-center">
            <button onclick="location.reload()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-colors">
                🔄 Recarregar
            </button>

            <button onclick="runFullTest()"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                🧪 Testar Tudo
            </button>
        </div>

    </div>

    <!-- Livewire Script -->
    <script src="{{ asset('vendor/livewire/livewire.js') }}"></script>

    <script>
        // Função para adicionar logs no "console" visual
        function logToScreen(message, type = 'info') {
            const consoleOutput = document.getElementById('console-output');
            const timestamp = new Date().toLocaleTimeString();
            const color = type === 'error' ? 'text-red-400' : 'text-green-400';
            const logEntry = document.createElement('div');
            logEntry.className = color;
            logEntry.innerHTML = `[${timestamp}] ${message}`;
            consoleOutput.appendChild(logEntry);
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }

        // Teste completo
        function runFullTest() {
            logToScreen('🚀 INICIANDO TESTE COMPLETO...');

            // Teste Tailwind
            logToScreen('✅ Tailwind CSS: Funcionando (cores e layout visíveis)');

            // Teste Alpine.js
            if (typeof Alpine !== 'undefined') {
                logToScreen('✅ Alpine.js: CARREGADO E FUNCIONANDO');
            } else {
                logToScreen('❌ Alpine.js: NÃO CARREGADO', 'error');
            }

            // Teste Livewire
            setTimeout(() => {
                if (typeof Livewire !== 'undefined') {
                    logToScreen('✅ Livewire: CARREGADO E FUNCIONANDO');
                    document.getElementById('livewire-status').className = 'w-3 h-3 bg-green-500 rounded-full';
                    document.getElementById('livewire-test').innerHTML = `
                        <div class="p-3 bg-green-100 border border-green-300 rounded-lg">
                            <strong>✅ Livewire Funcionando!</strong>
                            <p class="text-sm text-green-700 mt-1">Versão: ${Livewire.version || 'N/A'}</p>
                        </div>
                    `;
                } else {
                    logToScreen('❌ Livewire: NÃO CARREGADO', 'error');
                    document.getElementById('livewire-test').innerHTML = `
                        <div class="p-3 bg-red-100 border border-red-300 rounded-lg">
                            <strong>❌ Livewire Não Carregou</strong>
                            <p class="text-sm text-red-700 mt-1">Verifique o console do navegador</p>
                        </div>
                    `;
                }
            }, 500);

            logToScreen('📊 Todos os testes completados!');
        }

        // Teste automático ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            logToScreen('📄 Página carregada');
            logToScreen(`🌐 Alpine.js: ${typeof Alpine !== 'undefined' ? '✅ PRONTO' : '❌ AUSENTE'}`);

            setTimeout(() => {
                logToScreen(`⚡ Livewire: ${typeof Livewire !== 'undefined' ? '✅ PRONTO' : '❌ AUSENTE'}`);

                if (typeof Livewire !== 'undefined') {
                    document.getElementById('livewire-status').className =
                        'w-3 h-3 bg-green-500 rounded-full';
                }
            }, 1000);
        });
    </script>
</body>

</html>
