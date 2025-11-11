<?php

namespace App\Livewire;

use App\Forms\Components\TimeSlotPicker;
use App\Mail\FormSubmissionConfirmation;
use App\Models\Advertise;
use App\Models\AdvertiseAnswer;
use App\Models\AdvertiseField;
use App\Models\AdvertiseFieldAnswer;
use App\Models\BusinessHour;
use App\Models\Contact;
use App\Models\Schedule;
use App\Models\Unavailability;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\View\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;

class AdvertismentForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public string $formId;
    public ?Advertise $advertise;
    public bool $isSubmitting = false;

    // Controle de estado para confirmações
    public bool $showConfirmation = false;
    public ?array $pendingContactUpdate = null;

    // CORREÇÃO: Adicionar propriedades faltantes
    public bool $formAvailable = true;
    public string $unavailableMessage = '';

    // ADICIONAR: Estado de sucesso
    public bool $formSubmitted = false;
    public string $successMessage = '';

    public array $availableTimes = [];
    public array $selectedTimeSlots = [
        'field_id' => null,
        'start' => null,
        'end' => null,
    ];
    public function closePage(): void
    {
        $this->dispatch('close-page-execute');
    }

    public bool $showDetailedErrors = false;

    public function mount($id = null): void
    {
        $this->advertise = Advertise::with('advertise_fields')->where('uuid', $id)->first();

        if (!$this->advertise) {
            $this->redirectToAdvertismentList();
            return;
        }

        $this->formId = $this->advertise->id;

        if (!$this->advertise->is_active) {
            $this->redirectToAdvertismentList();
            return;
        }

        $this->form->fill();
    }

    private function checkExistingSubmission(string $email): bool
    {
        $email = strtolower(trim($email));

        return AdvertiseAnswer::where('advertise_id', $this->formId)
            ->whereHas('contact', function ($query) use ($email) {
                $query->whereRaw('LOWER(email) = ?', [$email]);
            })
            ->exists();
    }

    public function form(Schema $schema): Schema
    {
        if (!$this->formAvailable) {
            return $schema->statePath('data');
        }

        $components = [
            TextInput::make('name')
                ->label('Nome Completo')
                ->required()
                ->maxLength(255)
                ->placeholder('Ex: João Silva'),

            TextInput::make('email')
                ->label('Email')
                ->required()
                ->email()
                ->maxLength(255)
                ->placeholder('Ex: joao@empresa.pt')
                ->live()
                ->afterStateUpdated(function ($state) {
                    if ($state && $this->checkExistingSubmission($state)) {
                        $this->formAvailable = false;
                        $this->unavailableMessage = '';
                    }
                }),

            TextInput::make('phone_number')
                ->label('Telefone')
                ->required()
                ->maxLength(20)
                ->placeholder('Ex: +351 912 345 678'),
        ];

        if ($this->advertise && $this->advertise->advertise_fields) {
            $dynamicFields = $this->getDynamicFieldsSchema($this->advertise->advertise_fields);
            $components = array_merge($components, $dynamicFields);
        }

        $components = [
            Wizard::make([
                Step::make('Formulário')
                    ->schema($components)
                    ->afterValidation(function ($state) {
                        if (isset($state['email']) && $this->checkExistingSubmission($state['email'])) {
                            $this->formAvailable = false;
                            $this->unavailableMessage = 'Já existe uma resposta submetida para este anúncio com este email.';
                            $this->isSubmitting = false;
                            throw new Halt();
                        }

                        $validationErrors = $this->validateSubmission($state);
                        if (!empty($validationErrors)) {
                            if ($this->showDetailedErrors) {
                                $this->showDetailedValidationError($validationErrors);
                            } else {
                                $this->redirectToAdvertismentList();
                            }
                            $this->isSubmitting = false;
                            throw new Halt();
                        }
                    }),
                Step::make('Agendamento')
                    ->schema([
                        DatePicker::make('selected_date')
                            ->label('Selecione uma data disponível')
                            ->required()
                            ->minDate(today())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(fn($get) => $this->generateBlocks($get)),
                        TimeSlotPicker::make('time_slot')
                            ->visible(fn($get) => !empty($get('selected_date')))
                            ->label('Selecione um horário disponível')
                            ->options(fn() => $this->getAvailableTimes()),
                    ])
            ])
                ->submitAction(Action::make('submit')->label('Enviar Formulário')->action('submit')),
        ];

        return $schema
            ->components($components)
            ->statePath('data');
    }

    public function getAvailableTimes(): array
    {
        return $this->availableTimes;

    }

    public function generateBlocks($get)
    {
        $blockDuration = 10;
        $state = $get();
        $this->availableTimes = [];

        if (empty($state['selected_date'])) {
            return;
        }

        try {
            $date = Carbon::parse($state['selected_date']);
            $dayOfWeek = $date->locale('en')->dayName;

            $this->availableTimes[0] = [];

            $schedules = BusinessHour::where('advertise_id', $this->formId)
                ->where('day', $dayOfWeek)->get();
            $unavailabilities = Unavailability::whereDate('start', '<=', $date)
                ->whereDate('end', '>=', $date)
                ->get();
            $reservas = Schedule::whereDate('date', $date)->get();

            foreach ($schedules as $schedule) {
                $start = Carbon::createFromTimeString($schedule->start_time);
                $end = Carbon::createFromTimeString($schedule->end_time);

                $start = $date->copy()->setTime($start->hour, $start->minute, $start->second);
                $end = $date->copy()->setTime($end->hour, $end->minute, $end->second)->addMinute();

                while ($start <= $end->copy()->subMinutes($blockDuration)) {
                    $slotStart = $start->copy();
                    $slotEnd = $slotStart->copy()->addMinutes($blockDuration);

                    $isPast = $date->isToday() && $slotStart->lt(now());

                    $hora = $start->format('H:i');

                    $conflict = $isPast || $unavailabilities->contains(
                        fn($un) => $slotStart < Carbon::parse($un->end) && $slotEnd > Carbon::parse($un->start)
                    ) || $reservas->contains(
                                fn($res) => $slotStart < $res->end_time && $slotEnd > $res->start_time
                            );

                    $this->availableTimes[0][$hora] = !$conflict;

                    $start->addMinutes($blockDuration);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro no generateBlocks: ' . $e->getMessage());
            \Log::error('State data:', $state);
            $this->availableTimes[0] = [];
        }
    }

    public function submit(): void
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        try {
            $state = $this->form->getState();

            // CORREÇÃO: Verificar se formAvailable ainda é true
            if (!$this->formAvailable) {
                $this->isSubmitting = false;
                return;
            }

            $email = strtolower(trim($state['email'] ?? ''));
            if ($this->checkExistingSubmission($email)) {
                $this->formAvailable = false;
                $this->unavailableMessage = 'Já existe uma resposta submetida para este anúncio com este email.';
                $this->isSubmitting = false;
                return;
            }

            $validationErrors = $this->validateSubmission($state);
            if (!empty($validationErrors)) {
                if ($this->showDetailedErrors) {
                    $this->showDetailedValidationError($validationErrors);
                } else {
                    $this->redirectToAdvertismentList();
                }
                $this->isSubmitting = false;
                return;
            }

            $name = trim($state['name'] ?? '');
            $email = strtolower(trim($state['email'] ?? ''));
            $phone = trim($state['phone_number'] ?? '');

            $existingContact = Contact::whereRaw('LOWER(email) = ?', [$email])->first();

            $contactId = null;

            if ($existingContact) {
                $existingPhone = preg_replace('/[^0-9]/', '', $existingContact->phone_number);
                $newPhone = preg_replace('/[^0-9]/', '', $phone);

                if ($existingPhone !== $newPhone) {
                    $this->promptPhoneUpdateConfirmation($existingContact, $state, $phone);
                    $this->isSubmitting = false;
                    return;
                } else {
                    $existingContact->update(['name' => $name]);
                    $contactId = $existingContact->id;
                    $this->processFormSubmission($contactId, $state);
                }
            } else {
                $contact = Contact::create([
                    'name' => $name,
                    'email' => $email,
                    'phone_number' => $phone,
                ]);
                $contactId = $contact->id;
                $this->processFormSubmission($contactId, $state);
            }

        } catch (\Exception $e) {
            if ($this->showDetailedErrors) {
                Notification::make()
                    ->danger()
                    ->title('Erro de Sistema')
                    ->body('Ocorreu um erro: ' . $e->getMessage())
                    ->send();
            } else {
                $this->redirectToAdvertismentList();
            }
            $this->isSubmitting = false;
        }
    }

    private function processFormSubmission(int $contactId, array $state): void
    {
        try {
            $advertiseAnswer = AdvertiseAnswer::create([
                'contact_id' => $contactId,
                'advertise_id' => $this->formId,
            ]);

            $this->saveFieldAnswers($advertiseAnswer->id, $state);
            $this->saveSelectedTimeSlots($advertiseAnswer->id, $state);
            $this->sendConfirmationEmail($contactId, $advertiseAnswer);

            // ADICIONAR: Definir estado de sucesso
            $this->formSubmitted = true;
            $this->successMessage = 'Formulário submetido com sucesso! Obrigado pela sua participação.';

            // Limpar o formulário
            $this->form->fill();
            $this->isSubmitting = false;
            $this->pendingContactUpdate = null;

        } catch (\Exception $e) {
            \Log::error('Erro no processamento do formulário: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envia email de confirmação usando Mailtrap
     */
    private function sendConfirmationEmail(int $contactId, AdvertiseAnswer $advertiseAnswer): void
    {
        try {
            \Log::info("🔍 INICIANDO ENVIO DE EMAIL");
            \Log::info("Contact ID: " . $contactId);
            \Log::info("AdvertiseAnswer ID: " . $advertiseAnswer->id);

            $contact = Contact::find($contactId);

            if (!$contact) {
                \Log::warning('❌ Contacto não encontrado');
                return;
            }

            // Verificar se existe agendamento para esta resposta
            $schedule = Schedule::where('advertise_answer_id', $advertiseAnswer->id)->first();
            \Log::info("📅 Agendamento encontrado no componente: " . ($schedule ? 'SIM' : 'NÃO'));
            if ($schedule) {
                \Log::info("📅 Data: " . $schedule->date);
                \Log::info("📅 Start: " . $schedule->start_time);
                \Log::info("📅 End: " . $schedule->end_time);
            }

            \Log::info("📧 Enviando email para: " . $contact->email);

            Mail::to($contact->email)->send(new FormSubmissionConfirmation($advertiseAnswer, $contact));

            \Log::info('✅ Email enviado com sucesso');

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao enviar email: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    private function saveSelectedTimeSlots($advertiseAnswerId, array $state): void
    {
        try {
            if (empty($this->selectedTimeSlots['start']) || empty($state['selected_date'])) {
                \Log::info('Nenhum agendamento para salvar');
                return;
            }

            $selectedDate = Carbon::parse($state['selected_date']);
            $startTime = $this->selectedTimeSlots['start'];
            $blockDuration = 10;

            $startTimeCarbon = Carbon::createFromTimeString($startTime);
            $endTimeCarbon = $startTimeCarbon->copy()->addMinutes($blockDuration);

            $startDateTime = $selectedDate->copy()->setTime(
                $startTimeCarbon->hour,
                $startTimeCarbon->minute,
                $startTimeCarbon->second
            );

            $endDateTime = $selectedDate->copy()->setTime(
                $endTimeCarbon->hour,
                $endTimeCarbon->minute,
                $endTimeCarbon->second
            );

            Schedule::create([
                'advertise_answer_id' => $advertiseAnswerId,
                'date' => $selectedDate->toDateString(),
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
            ]);

            Unavailability::create([
                'start' => $startDateTime,
                'end' => $endDateTime,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar horários: ' . $e->getMessage());
        }
    }

    /**
     * Redireciona para a página principal de listagem de formulários
     */
    private function redirectToAdvertismentList(): void
    {
        $this->formAvailable = false;
    }



    // CORREÇÃO: Adicionar método faltante
    private function validateSubmission(array $state): array
    {
        $errors = [];
        $fieldsData = AdvertiseField::where('advertise_id', $this->formId)->get();

        if (empty(trim($state['name'] ?? ''))) {
            $errors[] = 'Nome é obrigatório';
        }

        $email = trim($state['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email válido é obrigatório';
        }

        if (empty(trim($state['phone_number'] ?? ''))) {
            $errors[] = 'Telefone é obrigatório';
        }

        foreach ($fieldsData as $field) {
            $fieldName = $field->answer;
            $value = $state[$fieldName] ?? null;

            if ($field->is_required) {
                $isEmpty = $this->isFieldEmpty($value, $field->field_type);
                if ($isEmpty) {
                    $errors[] = "Campo {$field->answer} é obrigatório";
                    continue;
                }
            }

            $validationRules = $this->normalizeValidationRules($field->validation_rules ?? []);
            if (!empty($validationRules) && !$this->shouldSkipValidation($value, $field)) {
                $fieldErrors = $this->validateFieldWithCustomRules($value, $validationRules, $field);
                $errors = array_merge($errors, $fieldErrors);
            }
        }

        return $errors;
    }

    /**
     * Verifica se um campo está vazio baseado no seu tipo
     */
    private function isFieldEmpty($value, string $fieldType): bool
    {
        switch ($fieldType) {
            case 'Toggle':
            case 'Checkbox':
                return $value === null;

            case 'CheckboxList':
                return empty($value) || (is_array($value) && count($value) === 0);

            case 'Radio':
            case 'Select':
                return empty($value) && $value !== 0 && $value !== '0';

            default:
                return empty($value) && $value !== 0 && $value !== '0';
        }
    }

    /**
     * Determina se deve saltar a validação para campos opcionais vazios
     */
    private function shouldSkipValidation($value, $field): bool
    {
        if ($this->isFieldEmpty($value, $field->field_type) && !$field->is_required) {
            return true;
        }

        if (in_array($field->field_type, ['Toggle', 'Checkbox']) && $value !== null) {
            return false;
        }

        return empty($value) && !$field->is_required;
    }

    /**
     * Normaliza as regras de validação do backoffice
     */
    private function normalizeValidationRules($rules)
    {
        if (empty($rules)) {
            return [];
        }

        // Se já está no formato normalizado
        if (
            isset($rules['min']) || isset($rules['max']) || isset($rules['email']) ||
            isset($rules['url']) || isset($rules['regex']) || isset($rules['in']) ||
            isset($rules['not_in']) || isset($rules['min_length']) || isset($rules['max_length'])
        ) {
            $normalized = [];
            foreach ($rules as $key => $value) {
                if (in_array($key, ['in', 'not_in']) && is_string($value)) {
                    $normalized[$key] = $this->parseRuleValues($value);
                } else {
                    $normalized[$key] = $value;
                }
            }
            return $normalized;
        }

        // Se está no formato de array do repeater
        if (is_array($rules) && isset($rules[0]) && is_array($rules[0])) {
            $normalized = [];
            foreach ($rules as $rule) {
                if (isset($rule['rule_type']) && $rule['rule_type']) {
                    $ruleType = $rule['rule_type'];
                    $ruleValue = $rule['rule_value'] ?? null;

                    switch ($ruleType) {
                        case 'in':
                        case 'not_in':
                            if (is_string($ruleValue)) {
                                if ($ruleValue === 'true') {
                                    $normalized[$ruleType] = [true];
                                } elseif ($ruleValue === 'false') {
                                    $normalized[$ruleType] = [false];
                                } else {
                                    $normalized[$ruleType] = $this->parseRuleValues($ruleValue);
                                }
                            } else {
                                $normalized[$ruleType] = $ruleValue;
                            }
                            break;

                        case 'min':
                        case 'max':
                        case 'min_length':
                        case 'max_length':
                            $normalized[$ruleType] = is_numeric($ruleValue) ? (float) $ruleValue : $ruleValue;
                            break;

                        case 'email':
                        case 'url':
                            $normalized[$ruleType] = true;
                            break;

                        default:
                            $normalized[$ruleType] = $ruleValue;
                            break;
                    }
                }
            }
            return $normalized;
        }

        return [];
    }

    /**
     * Aplica as regras de validação customizadas a um campo
     */
    private function validateFieldWithCustomRules($value, array $rules, $field): array
    {
        $errors = [];

        foreach ($rules as $ruleType => $ruleValue) {
            $isValid = match ($ruleType) {
                'min' => $this->validateMinRule($value, $ruleValue, $field),
                'max' => $this->validateMaxRule($value, $ruleValue, $field),
                'min_length' => $this->validateMinLengthRule($value, $ruleValue),
                'max_length' => $this->validateMaxLengthRule($value, $ruleValue),
                'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
                'regex' => $this->validateRegexRule($value, $ruleValue),
                'in' => $this->validateInRule($value, $ruleValue, $field),
                'not_in' => $this->validateNotInRule($value, $ruleValue, $field),
                default => true,
            };

            if (!$isValid) {
                if ($this->showDetailedErrors) {
                    // Mensagens detalhadas e descritivas para debugging
                    $errors[] = match ($ruleType) {
                        'min' => $this->getMinErrorMessage($value, $ruleValue, $field),
                        'max' => $this->getMaxErrorMessage($value, $ruleValue, $field),
                        'min_length' => "📏 O campo '{$field->answer}' deve ter pelo menos {$ruleValue} caracteres (atual: " . strlen((string) $value) . ")",
                        'max_length' => "📏 O campo '{$field->answer}' deve ter no máximo {$ruleValue} caracteres (atual: " . strlen((string) $value) . ")",
                        'email' => "📧 O campo '{$field->answer}' deve conter um endereço de email válido",
                        'url' => "🌐 O campo '{$field->answer}' deve conter uma URL válida",
                        'regex' => "🔍 O campo '{$field->answer}' não corresponde ao formato esperado",
                        'in' => $this->getInErrorMessage($ruleValue, $field),
                        'not_in' => $this->getNotInErrorMessage($ruleValue, $field),
                        default => "❌ O campo '{$field->answer}' contém um valor inválido"
                    };
                } else {
                    // Mensagem genérica para produção
                    $errors[] = "Campo {$field->answer} inválido";
                }
                break;
            }
        }

        return $errors;
    }

    private function getMinErrorMessage($value, $ruleValue, $field): string
    {
        $currentValue = match ($field->field_type) {
            'DatePicker', 'TimePicker' => $value,
            'NumberInput', 'Slider' => (float) $value,
            default => strlen((string) $value)
        };

        $valueType = match ($field->field_type) {
            'DatePicker' => 'data',
            'TimePicker' => 'hora',
            'NumberInput', 'Slider' => 'valor numérico',
            default => 'comprimento'
        };

        return "📊 O campo '{$field->answer}' deve ter um {$valueType} mínimo de {$ruleValue} (atual: {$currentValue})";
    }

    private function getMaxErrorMessage($value, $ruleValue, $field): string
    {
        $currentValue = match ($field->field_type) {
            'DatePicker', 'TimePicker' => $value,
            'NumberInput', 'Slider' => (float) $value,
            default => strlen((string) $value)
        };

        $valueType = match ($field->field_type) {
            'DatePicker' => 'data',
            'TimePicker' => 'hora',
            'NumberInput', 'Slider' => 'valor numérico',
            default => 'comprimento'
        };

        return "📊 O campo '{$field->answer}' deve ter um {$valueType} máximo de {$ruleValue} (atual: {$currentValue})";
    }

    private function getInErrorMessage($ruleValue, $field): string
    {
        $allowedValues = is_array($ruleValue) ? $ruleValue : [$ruleValue];
        $formattedValues = implode(', ', array_map(function ($val) {
            if ($val === true)
                return 'verdadeiro';
            if ($val === false)
                return 'falso';
            if ($val === '')
                return 'vazio';
            return "'{$val}'";
        }, $allowedValues));

        $valueType = match ($field->field_type) {
            'Toggle', 'Checkbox' => 'Valores booleanos permitidos',
            'Radio', 'Select' => 'Opções disponíveis',
            'CheckboxList' => 'Valores permitidos',
            'Slider' => 'Valores numéricos permitidos',
            default => 'Valores permitidos'
        };

        return "✅ {$valueType}: {$formattedValues}";
    }

    /**
     * Mensagem de erro personalizada para regra 'not_in'
     */
    private function getNotInErrorMessage($ruleValue, $field): string
    {
        $forbiddenValues = is_array($ruleValue) ? $ruleValue : [$ruleValue];
        $formattedValues = implode(', ', array_map(function ($val) {
            if ($val === true)
                return 'verdadeiro';
            if ($val === false)
                return 'falso';
            if ($val === '')
                return 'vazio';
            return "'{$val}'";
        }, $forbiddenValues));

        $valueType = match ($field->field_type) {
            'Toggle', 'Checkbox' => 'Valores booleanos proibidos',
            'Radio', 'Select' => 'Opções não permitidas',
            'CheckboxList' => 'Valores proibidos',
            'Slider' => 'Valores numéricos proibidos',
            default => 'Valores proibidos'
        };

        return "🚫 {$valueType}: {$formattedValues}";
    }

    /**
     * Mostra erros de validação detalhados
     */
    /**
     * Mostra erros de validação detalhados
     */
    private function showDetailedValidationError(array $errors): void
    {
        $errorCount = count($errors);

        $title = match (true) {
            $errorCount === 1 => '1 erro de validação encontrado',
            $errorCount > 1 => "{$errorCount} erros de validação encontrados",
            default => 'Erro de validação'
        };

        // Usar HTML para formatação
        $errorMessage = "<div style='text-align: left;'>";
        $errorMessage .= "<strong>🔍 Detalhes dos erros:</strong><br><br>";

        foreach ($errors as $index => $error) {
            $errorMessage .= "<strong>" . ($index + 1) . ".</strong> " . $error . "<br><br>";
        }

        $errorMessage .= "<strong>💡 Sugestão:</strong> Verifique os campos destacados e corrija os valores conforme as regras de validação.";
        $errorMessage .= "</div>";

        Notification::make()
            ->danger()
            ->title($title)
            ->body($errorMessage)
            ->send();
    }

    /**
     * Valida regra de valor mínimo
     */
    private function validateMinRule($value, $ruleValue, $field): bool
    {
        if ($field->field_type === 'DatePicker') {
            if (!$value || !$ruleValue)
                return true;
            try {
                $inputDate = Carbon::parse($value);
                $minDate = Carbon::parse($ruleValue);
                return $inputDate->greaterThanOrEqualTo($minDate);
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($field->field_type === 'TimePicker') {
            if (!$value || !$ruleValue)
                return true;
            try {
                $inputTime = Carbon::createFromTimeString($value);
                $minTime = Carbon::createFromTimeString($ruleValue);
                return $inputTime->greaterThanOrEqualTo($minTime);
            } catch (\Exception $e) {
                return false;
            }
        }

        if (is_numeric($value)) {
            return (float) $value >= (float) $ruleValue;
        }

        return strlen((string) $value) >= (int) $ruleValue;
    }

    /**
     * Valida regra de valor máximo
     */
    private function validateMaxRule($value, $ruleValue, $field): bool
    {
        if ($field->field_type === 'DatePicker') {
            if (!$value || !$ruleValue)
                return true;
            try {
                $inputDate = Carbon::parse($value);
                $maxDate = Carbon::parse($ruleValue);
                return $inputDate->lessThanOrEqualTo($maxDate);
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($field->field_type === 'TimePicker') {
            if (!$value || !$ruleValue)
                return true;
            try {
                $inputTime = Carbon::createFromTimeString($value);
                $maxTime = Carbon::createFromTimeString($ruleValue);
                return $inputTime->lessThanOrEqualTo($maxTime);
            } catch (\Exception $e) {
                return false;
            }
        }

        if (is_numeric($value)) {
            return (float) $value <= (float) $ruleValue;
        }

        return strlen((string) $value) <= (int) $ruleValue;
    }

    /**
     * Valida regra de comprimento mínimo
     */
    private function validateMinLengthRule($value, $ruleValue): bool
    {
        return strlen((string) $value) >= (int) $ruleValue;
    }

    /**
     * Valida regra de comprimento máximo
     */
    private function validateMaxLengthRule($value, $ruleValue): bool
    {
        return strlen((string) $value) <= (int) $ruleValue;
    }

    /**
     * Valida regra de expressão regular
     */
    private function validateRegexRule($value, $ruleValue): bool
    {
        if (empty($ruleValue)) {
            return true;
        }

        try {
            // Normalizar a expressão regular
            $normalizedRegex = $this->normalizeRegex($ruleValue);

            // Validar se a regex é válida
            if (@preg_match($normalizedRegex, '') === false) {
                return false;
            }

            $result = @preg_match($normalizedRegex, (string) $value);
            return $result === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Normaliza expressões regulares adicionando delimitadores se necessário
     */
    private function normalizeRegex(string $regex): string
    {
        $regex = trim($regex);

        // Se já começa e termina com delimitadores comuns, retornar como está
        if (preg_match('/^([\/~#%|]).*\1[a-zA-Z]*$/', $regex)) {
            return $regex;
        }

        // Adicionar delimitadores padrão (/)
        // Escapar barras dentro da regex
        $escapedRegex = str_replace('/', '\\/', $regex);
        return '/' . $escapedRegex . '/';
    }

    /**
     * Valida regra de valores permitidos
     */
    private function validateInRule($value, $ruleValue, $field): bool
    {
        $allowedValues = is_array($ruleValue) ? $ruleValue : [$ruleValue];

        if ($field->field_type === 'Radio') {
            if ($value === null || $value === '') {
                return in_array('', $allowedValues, true) || in_array(null, $allowedValues, true);
            }
            $stringValue = (string) $value;
            $stringAllowed = array_map('strval', $allowedValues);
            return in_array($stringValue, $stringAllowed, true);
        }

        if ($field->field_type === 'CheckboxList' && is_array($value)) {
            foreach ($value as $selectedValue) {
                if (!in_array($selectedValue, $allowedValues, true)) {
                    return false;
                }
            }
            return true;
        }

        if ($field->field_type === 'Toggle' || $field->field_type === 'Checkbox') {
            $normalizedValue = (bool) $value;
            $normalizedAllowed = array_map(function ($val) {
                if (is_bool($val))
                    return $val;
                if ($val === 'true')
                    return true;
                if ($val === 'false')
                    return false;
                if ($val === '1')
                    return true;
                if ($val === '0')
                    return false;
                return (bool) $val;
            }, $allowedValues);
            return in_array($normalizedValue, $normalizedAllowed, true);
        }

        if ($field->field_type === 'Slider') {
            $numericValue = (float) $value;
            $numericAllowed = array_map('floatval', $allowedValues);
            return in_array($numericValue, $numericAllowed, true);
        }

        return in_array($value, $allowedValues, true);
    }

    /**
     * Valida regra de valores proibidos
     */
    private function validateNotInRule($value, $ruleValue, $field): bool
    {
        $forbiddenValues = is_array($ruleValue) ? $ruleValue : [$ruleValue];

        if ($field->field_type === 'Radio') {
            if ($value === null || $value === '') {
                return !in_array('', $forbiddenValues, true) && !in_array(null, $forbiddenValues, true);
            }
            $stringValue = (string) $value;
            $stringForbidden = array_map('strval', $forbiddenValues);
            return !in_array($stringValue, $stringForbidden, true);
        }

        if ($field->field_type === 'CheckboxList' && is_array($value)) {
            foreach ($value as $selectedValue) {
                if (in_array($selectedValue, $forbiddenValues, true)) {
                    return false;
                }
            }
            return true;
        }

        if ($field->field_type === 'Toggle' || $field->field_type === 'Checkbox') {
            $normalizedValue = (bool) $value;
            $normalizedForbidden = array_map(function ($val) {
                if (is_bool($val))
                    return $val;
                if ($val === 'true')
                    return true;
                if ($val === 'false')
                    return false;
                if ($val === '1')
                    return true;
                if ($val === '0')
                    return false;
                return (bool) $val;
            }, $forbiddenValues);
            return !in_array($normalizedValue, $normalizedForbidden, true);
        }

        if ($field->field_type === 'Slider') {
            $numericValue = (float) $value;
            $numericForbidden = array_map('floatval', $forbiddenValues);
            return !in_array($numericValue, $numericForbidden, true);
        }

        return !in_array($value, $forbiddenValues, true);
    }

    /**
     * Converte valores de regra para array
     */
    private function parseRuleValues($ruleValue): array
    {
        if (is_array($ruleValue)) {
            return $ruleValue;
        }

        if (is_string($ruleValue)) {
            $ruleValue = trim($ruleValue);

            if ($ruleValue === 'true')
                return [true];
            if ($ruleValue === 'false')
                return [false];
            if (is_numeric($ruleValue) && strpos($ruleValue, ',') === false)
                return [$ruleValue];

            $decoded = json_decode($ruleValue, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                return $decoded;

            if (strpos($ruleValue, ',') !== false) {
                return array_map(function ($val) {
                    $val = trim($val);
                    if ($val === 'true')
                        return true;
                    if ($val === 'false')
                        return false;
                    return $val;
                }, explode(',', $ruleValue));
            }

            return [$ruleValue];
        }

        return [$ruleValue];
    }

    /**
     * Processa contacto existente com verificação de telefone
     */
    private function handleExistingContact($existingContact, $name, $phone, $state): void
    {
        // Normalizar números para comparação
        $existingPhone = preg_replace('/[^0-9]/', '', $existingContact->phone_number);
        $newPhone = preg_replace('/[^0-9]/', '', $phone);

        if ($existingPhone === $newPhone) {
            // Telefones iguais - atualizar nome e prosseguir
            $existingContact->update(['name' => $name]);
            $this->finalizeSubmission($existingContact->id, 'Formulário submetido com sucesso.');
        } else {
            // Telefones diferentes - pedir confirmação
            $this->promptPhoneUpdateConfirmation($existingContact, $state, $phone);
        }
    }

    /**
     * Mostra confirmação para atualizar telefone do contacto e dispara evento
     */
    private function promptPhoneUpdateConfirmation($existingContact, $state, $newPhone): void
    {
        $this->pendingContactUpdate = [
            'contact_id' => $existingContact->id,
            'state' => $state,
            'existing_phone' => $existingContact->phone_number,
            'new_phone' => $newPhone
        ];

        Notification::make()
            ->warning()
            ->title('Contacto Existente')
            ->body("Telefone diferente detectado.\n\nAtual: {$existingContact->phone_number}\nNovo: {$newPhone}")
            ->actions([
                Action::make('sim')
                    ->label('Atualizar Contacto')
                    ->button()
                    ->color('warning')
                    ->dispatch('confirm-update-phone', ['confirm' => true]),
                Action::make('nao')
                    ->label('Cancelar')
                    ->button()
                    ->color('gray')
                    ->dispatch('confirm-update-phone', ['confirm' => false]),
            ])
            ->send();

        $this->isSubmitting = false;
    }

    /**
     * Escuta o envento da notificação e reage à confirmação
     */
    #[On('confirm-update-phone')]
    public function confirmUpdatePhone(bool $confirm): void
    {
        if (!$this->pendingContactUpdate) {
            $this->isSubmitting = false;
            return;
        }

        $contactId = $this->pendingContactUpdate['contact_id'];
        $state = $this->pendingContactUpdate['state'];
        $contact = Contact::find($contactId);

        if (!$confirm) {
            if ($this->showDetailedErrors) {
                Notification::make()
                    ->info()
                    ->title('Operação cancelada')
                    ->body('Pode editar os dados e tentar novamente.')
                    ->send();
            }
            $this->isSubmitting = false;
            $this->pendingContactUpdate = null;
            return;
        }

        // Atualizar contacto
        $contact->update([
            'name' => trim($state['name']),
            'phone_number' => trim($state['phone_number']),
        ]);

        // Processar o envio
        $this->processFormSubmission($contactId, $state);
        $this->pendingContactUpdate = null;
    }

    /**
     * Finaliza o envio do formulário (usado para confirmações)
     */
    private function finalizeSubmission(int $contactId, string $message): void
    {
        $state = $this->form->getState();
        $this->processFormSubmission($contactId, $state);
    }

    // ========== CAMPOS DINÂMICOS ==========

    /**
     * Gera schema dos campos dinâmicos do formulário
     */
    private function getDynamicFieldsSchema($fieldsData): array
    {
        return collect($fieldsData)->map(function ($field) {
            $label = ucfirst($field->answer ?? 'Campo');
            $required = (bool) $field->is_required;
            $options = $this->parseFieldOptions($field);

            return $this->createFieldComponent($field, $label, $required, $options);
        })->toArray();
    }

    /**
     * Cria componente baseado no tipo de campo
     */
    private function createFieldComponent($field, $label, $required, $options)
    {
        return match ($field->field_type) {
            'TextInput' => TextInput::make($field->answer)
                ->label($label)->required($required)->maxLength(500)
                ->placeholder('Digite sua resposta...')->columnSpan('full'),

            'NumberInput' => TextInput::make($field->answer)
                ->label($label)->required($required)->numeric()
                ->minValue($field->min_value)->maxValue($field->max_value)
                ->step($field->step ?? 1)->placeholder('Ex: 100')->columnSpan('full'),

            'Textarea' => Textarea::make($field->answer)
                ->label($label)->required($required)->rows(4)
                ->placeholder('Descreva em detalhes...')->columnSpan('full'),

            'Select' => Select::make($field->answer)
                ->label($label)->options($options)->required($required)
                ->placeholder('Selecione uma opção')->columnSpan('full'),

            'Checkbox' => Checkbox::make($field->answer)
                ->label($label)->required($required)->columnSpan('full'),

            'Toggle' => Toggle::make($field->answer)
                ->label($label)->required($required)->columnSpan('full'),

            'CheckboxList' => CheckboxList::make($field->answer)
                ->label($label)->options($options)->required($required)
                ->columns(1)->columnSpan('full'),

            'Radio' => Radio::make($field->answer)
                ->label($label)->options($options)->required($required)
                ->columns(1)->columnSpan('full'),

            'DatePicker' => DatePicker::make($field->answer)
                ->label($label)->required($required)
                ->native(false)->displayFormat('d/m/Y')->columnSpan('full'),

            'TimePicker' => TimePicker::make($field->answer)
                ->label($label)->required($required)->columnSpan('full'),

            'Slider' => Slider::make($field->answer)
                ->label($label)
                ->minValue($field->min_value ?? 0)
                ->maxValue($field->max_value ?? 100)
                ->step($field->step ?? 1)
                ->tooltips($field->show_tooltip ? true : false) // MOSTRA TOOLTIP APENAS SE show_tooltip = 1
                ->required($required)
                ->columnSpan('full'),

            default => TextInput::make($field->answer)
                ->label($label)->required($required)
                ->placeholder('Campo não configurado')->columnSpan('full'),
        };
    }

    /**
     * Converte opções do campo para array formatado
     */
    private function parseFieldOptions($field): array
    {
        if (!$field->options)
            return [];

        if (is_array($field->options)) {
            $options = isset($field->options[0]['option'])
                ? collect($field->options)->pluck('option')
                : collect($field->options);

            return $options->mapWithKeys(fn($opt) => [trim($opt) => trim($opt)])->toArray();
        }

        if (is_string($field->options)) {
            return collect(explode(',', $field->options))
                ->mapWithKeys(fn($opt) => [trim($opt) => trim($opt)])
                ->toArray();
        }

        return [];
    }

    /**
     * Salva respostas dos campos dinâmicos
     */
    private function saveFieldAnswers(int $advertiseAnswerId, array $state): void
    {
        $fieldsData = AdvertiseField::where('advertise_id', $this->formId)->get();

        foreach ($fieldsData as $field) {
            $answerValue = $state[$field->answer] ?? null;
            if (!$answerValue)
                continue;

            // Serializar valor baseado no tipo
            $serializedValue = match (true) {
                is_array($answerValue) => json_encode($answerValue),
                is_bool(value: $answerValue) => (int) $answerValue,
                default => (string) $answerValue,
            };

            AdvertiseFieldAnswer::create([
                'advertise_answer_id' => $advertiseAnswerId,
                'advertise_field_id' => $field->id,
                'answer' => json_encode([
                    'type' => $field->field_type,
                    'value' => $serializedValue,
                    'answered_at' => now()->toISOString(),
                ]),
            ]);
        }
    }
    public function render()
    {
        return view('livewire.advertisment-form', [
            'formAvailable' => $this->formAvailable,
            'unavailableMessage' => $this->unavailableMessage,
        ]);
    }
}