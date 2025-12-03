<div>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Faturas e Custos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $record->invoices->count() }} fatura(s) registada(s)
                </p>
            </div>
            @if ($record->invoices->isNotEmpty())
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Total:
                        <span class="font-semibold text-green-600 dark:text-green-400">
                            € {{ number_format($record->invoices->sum('amount'), 2, ',', ' ') }}
                        </span>
                    </div>
                    @php
                        $paidCount = $record->invoices->where('status', 'pago')->count();
                        $pendingCount = $record->invoices->where('status', 'pendente')->count();
                        $overdueCount = $record->invoices->where('status', 'vencido')->count();
                    @endphp
                    @if ($paidCount > 0)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                            {{ $paidCount }} pago(s)
                        </span>
                    @endif
                    @if ($pendingCount > 0)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300">
                            {{ $pendingCount }} pendente(s)
                        </span>
                    @endif
                    @if ($overdueCount > 0)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300">
                            {{ $overdueCount }} vencido(s)
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if ($record->invoices->isEmpty())
            <div
                class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-4">
                    <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-gray-900 dark:text-gray-100 font-medium mb-1">Nenhuma fatura registada</p>
                <p class="text-gray-500 dark:text-gray-400 text-sm">Ainda não foram registadas faturas para esta
                    oportunidade.</p>
            </div>
        @else
            <!-- Filtros rápidos -->
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="filterInvoices('all')"
                    class="px-3 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Todas ({{ $record->invoices->count() }})
                </button>
                <button type="button" onclick="filterInvoices('pago')"
                    class="px-3 py-1.5 text-sm rounded-full border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
                    Pagas ({{ $paidCount }})
                </button>
                <button type="button" onclick="filterInvoices('pendente')"
                    class="px-3 py-1.5 text-sm rounded-full border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 transition-colors">
                    Pendentes ({{ $pendingCount }})
                </button>
            </div>

            <!-- Tabela de faturas -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fatura
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Fornecedor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Valor
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Datas
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($record->invoices->sortByDesc('invoice_date') as $invoice)
                            <tr class="invoice-row" data-status="{{ $invoice->status }}">
                                <!-- Número da Fatura -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $invoice->invoice_number }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                    </div>
                                </td>

                                <!-- Fornecedor -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $invoice->supplier }}
                                    </div>
                                    @if ($invoice->description)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate max-w-xs">
                                            {{ Str::limit($invoice->description, 60) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Valor -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        € {{ number_format($invoice->amount, 2, ',', ' ') }}
                                    </div>
                                </td>

                                <!-- Tipo -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColor = match ($invoice->type) {
                                            'obra'
                                                => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300',
                                            'servico'
                                                => 'bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300',
                                            'material'
                                                => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300',
                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                        };
                                        $typeLabel = match ($invoice->type) {
                                            'obra' => 'Obra',
                                            'servico' => 'Serviço',
                                            'material' => 'Material',
                                            default => ucfirst($invoice->type),
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <!-- Datas -->
                                <td class="px-6 py-4">
                                    <div class="text-xs space-y-1">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span
                                                class="{{ $invoice->status === 'vencido' ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-400' }}">
                                                Venc: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        @if ($invoice->payment_date)
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 mr-1 text-green-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span class="text-green-600 dark:text-green-400">
                                                    Pago:
                                                    {{ \Carbon\Carbon::parse($invoice->payment_date)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColor = match ($invoice->status) {
                                            'pago'
                                                => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300',
                                            'pendente'
                                                => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300',
                                            'vencido' => 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300',
                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                        };
                                        $statusLabel = match ($invoice->status) {
                                            'pago' => 'Pago',
                                            'pendente' => 'Pendente',
                                            'vencido' => 'Vencido',
                                            default => ucfirst($invoice->status),
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <!-- Ações -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @if ($invoice->hasMedia('invoices'))
                                            <a href="{{ $invoice->getFirstMediaUrl('invoices') }}" target="_blank"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                title="Ver/Abrir documento">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ $invoice->getFirstMediaUrl('invoices') }}" download
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-1 rounded hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                                title="Baixar documento">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600 p-1"
                                                title="Sem documento anexado">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- Footer com total -->
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                        <tr>
                            <td colspan="7" class="px-6 py-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Total de faturas:
                                        {{ $record->invoices->count() }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                        Total: € {{ number_format($record->invoices->sum('amount'), 2, ',', ' ') }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    function filterInvoices(status) {
        const rows = document.querySelectorAll('.invoice-row');
        rows.forEach(row => {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function closeNotesModal() {
        const modal = document.getElementById('notesModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeNotesModal();
        }
    });

    document.getElementById('notesModal').addEventListener('click', (e) => {
        if (e.target.id === 'notesModal') {
            closeNotesModal();
        }
    });
</script>
