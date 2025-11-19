<x-filament::page>
    @if (method_exists($this, 'getHeaderWidgets'))
        @foreach ($this->getHeaderWidgets() as $widget)
            @livewire($widget)
        @endforeach
    @endif

    @if (method_exists($this, 'getWidgets'))
        @foreach ($this->getWidgets() as $widget)
            @livewire($widget)
        @endforeach
    @endif
</x-filament::page>
