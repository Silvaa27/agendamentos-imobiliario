<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class TimeSlotPicker extends Field
{
    protected string $view = 'forms.components.time-slot-picker';

    protected array|\Closure $availableOptions = [];

    public function options(array|\Closure $options): static
    {
        $this->availableOptions = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->evaluate($this->availableOptions) ?? [];
    }
}