<div class="mb-8">
    <h3 class="text-2xl font-bold mb-6 text-gray-900">Selecionar Horário</h3>

    @if (empty($getOptions()) || !collect($getOptions())->flatten()->contains(true))
        {{-- AVISO: Nenhum horário disponível --}}
        <div class="bg-warning-50 border border-warning-200 rounded-xl p-8 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <svg class="w-10 h-10 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h4 class="text-xl font-bold text-warning-800 mb-3">
                ⚠️ Nenhum Horário Disponível
            </h4>
            <p class="text-warning-700 mb-4 text-lg">
                Não existem horários disponíveis para a data selecionada.
            </p>
            <p class="text-warning-600 text-base">
                Por favor, selecione outra data ou entre em contacto connosco.
            </p>
        </div>
    @else
        {{-- HORÁRIOS DISPONÍVEIS --}}
        <div class="space-y-6">
            @foreach ($getOptions() as $classroomId => $slots)
                @if (collect($slots)->contains(true))
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                        <h4 class="text-xl font-bold mb-4 text-gray-900">{{-- \App\Models\Classroom::find($classroomId)?->description --}}</h4>

                        <div x-data="{
                            options: {{ str_replace('"', "'", json_encode($slots)) }},
                            values: $wire.entangle('selectedTimeSlots'),
                            init() {
                                window.addEventListener('clear-other-selections', (e) => {
                                    if (e.detail.classroomId !== '{{ $classroomId }}') {
                                        this.values.start = null;
                                        this.values.end = null;
                                    }
                                });
                            },
                            toggle: function(key, fieldID) {
                                if (!this.options[key]) return;
                        
                                if (this.values.field_id && this.values.field_id !== fieldID) {
                                    this.values.start = null;
                                    this.values.end = null;
                                }
                        
                                this.values.field_id = fieldID;
                        
                                window.dispatchEvent(new CustomEvent('clear-other-selections', {
                                    detail: { classroomId: '{{ $classroomId }}' }
                                }));
                        
                                if (this.values.start === key && this.values.end === null) {
                                    return;
                                }
                        
                                this.values.start = key;
                                this.values.end = null;
                            },
                            getEndSlotLabel(time) {
                                const [h, m] = time.split(':').map(Number);
                                const d = new Date(0, 0, 0, h, m + 29);
                                return d.toTimeString().substring(0, 5);
                            },
                            hasUnavailableInRange(start, end) {
                                const keys = Object.keys(this.options);
                                const iStart = keys.indexOf(start);
                                const iEnd = keys.indexOf(end);
                                if (iStart === -1 || iEnd === -1) return false;
                        
                                return keys.slice(iStart, iEnd + 1).some(k => !this.options[k]);
                            }
                        }" class="grid grid-cols-1 sm:grid-cols-4 md:grid-cols-6 gap-3"
                            wire:key="{{ uniqId() }}" wire:ignore>
                            @foreach ($slots as $hora => $disponivel)
                                <button type="button" @click="toggle('{{ $hora }}', '{{ $classroomId }}')"
                                    x-text="'{{ $hora }}'" wire:loading.attr="disabled"
                                    wire:loading.class="animate-pulse"
                                    :class="(() => {
                                        const key = '{{ $hora }}';
                                        const fieldID = '{{ $classroomId }}';
                                        const isSelected = values.start && (key >= values.start && key <= (values
                                            .end ?? values.start));
                                        const hasInvalid = values.start && values.end && hasUnavailableInRange(
                                            values.start, values.end);
                                    
                                        if (fieldID == values.field_id && isSelected && hasInvalid)
                                            return 'border-2 border-danger-300 bg-danger-500 text-white transition-all';
                                        if (!options[key])
                                            return 'border-2 border-gray-300 bg-gray-200 text-gray-500 cursor-not-allowed line-through';
                                    
                                        if (fieldID == values.field_id && values.start && (values.start === key ||
                                                values.end === key))
                                            return 'border-2 border-primary-500 bg-primary-600 text-white transition-all';
                                        if (fieldID == values.field_id && isSelected)
                                            return 'border-2 border-primary-500 bg-primary-600 text-white transition-all';
                                    
                                        return 'border-2 border-gray-300 bg-white hover:border-primary-500 hover:bg-primary-50 transition-all';
                                    })()"
                                    class="p-3 rounded-lg text-center font-medium text-sm"
                                    :disabled="!options['{{ $hora }}']"></button>
                            @endforeach
                            <input type="hidden" x-model="state">
                        </div>
                    </div>
                @else
                    {{-- AVISO: Este campo específico não tem horários --}}
                    <div class="bg-gray-100 border border-gray-300 rounded-xl p-4">
                        <div class="flex items-center gap-3 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Este campo não tem horários disponíveis para a data
                                selecionada.</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>