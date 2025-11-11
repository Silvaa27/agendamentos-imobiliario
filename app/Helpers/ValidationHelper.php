<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Processa as regras de validação do formulário para o formato correto
     */
    public static function processValidationRules(array $rules): array
    {
        $processedRules = [];

        foreach ($rules as $rule) {
            if (!isset($rule['rule_type'])) {
                continue;
            }

            $ruleType = $rule['rule_type'];
            $ruleValue = $rule['rule_value'] ?? null;

            switch ($ruleType) {
                // Regras numéricas
                case 'min':
                case 'max':
                    if (is_numeric($ruleValue)) {
                        $processedRules[$ruleType] = (float) $ruleValue;
                    }
                    break;

                // Regras de comprimento
                case 'min_length':
                case 'max_length':
                    if (is_numeric($ruleValue)) {
                        $processedRules[$ruleType] = (int) $ruleValue;
                    }
                    break;

                // Regras de lista
                case 'in':
                case 'not_in':
                    if ($ruleValue) {
                        // Converte "valor1,valor2,valor3" para array
                        $values = array_map('trim', explode(',', $ruleValue));
                        $processedRules[$ruleType] = $values;
                    }
                    break;

                // Regras booleanas (só precisam de existir)
                case 'email':
                case 'url':
                case 'alpha':
                case 'alpha_dash':
                case 'alpha_num':
                    $processedRules[$ruleType] = true;
                    break;

                // Regras de regex
                case 'regex':
                    if ($ruleValue) {
                        $processedRules[$ruleType] = $ruleValue;
                    }
                    break;
            }
        }

        return $processedRules;
    }

    /**
     * Exemplos de uso das regras (para mostrar na UI)
     */
    public static function getRuleExamples(): array
    {
        return [
            'min' => [
                'label' => 'Valor Mínimo',
                'example' => '18 (idade mínima)',
                'placeholder' => 'Ex: 18'
            ],
            'max' => [
                'label' => 'Valor Máximo',
                'example' => '65 (idade máxima)',
                'placeholder' => 'Ex: 65'
            ],
            'min_length' => [
                'label' => 'Comprimento Mínimo',
                'example' => '2 (nome com pelo menos 2 letras)',
                'placeholder' => 'Ex: 2'
            ],
            'max_length' => [
                'label' => 'Comprimento Máximo',
                'example' => '50 (máximo 50 caracteres)',
                'placeholder' => 'Ex: 50'
            ],
            'email' => [
                'label' => 'Email Válido',
                'example' => 'Verifica se é um email válido',
                'placeholder' => 'Deixe vazio'
            ],
            'in' => [
                'label' => 'Valores Permitidos',
                'example' => 'Portugal,Espanha,França (apenas estes países)',
                'placeholder' => 'Ex: valor1,valor2,valor3'
            ],
            'not_in' => [
                'label' => 'Valores Proibidos',
                'example' => 'admin,root (estes valores não são permitidos)',
                'placeholder' => 'Ex: valor1,valor2,valor3'
            ],
        ];
    }

    /**
     * Gera mensagens de erro automáticas
     */
    public static function generateErrorMessage(string $rule, $value, string $fieldName): string
    {
        return match ($rule) {
            'min' => "O campo {$fieldName} deve ser no mínimo {$value}.",
            'max' => "O campo {$fieldName} deve ser no máximo {$value}.",
            'min_length' => "O campo {$fieldName} deve ter pelo menos {$value} caracteres.",
            'max_length' => "O campo {$fieldName} deve ter no máximo {$value} caracteres.",
            'email' => "O campo {$fieldName} deve ser um email válido.",
            'url' => "O campo {$fieldName} deve ser uma URL válida.",
            'in' => "O campo {$fieldName} contém um valor não permitido.",
            'not_in' => "O campo {$fieldName} contém um valor proibido.",
            'alpha' => "O campo {$fieldName} deve conter apenas letras.",
            'alpha_dash' => "O campo {$fieldName} deve conter apenas letras, hífens e underscores.",
            'alpha_num' => "O campo {$fieldName} deve conter apenas letras e números.",
            default => "O valor do campo {$fieldName} é inválido.",
        };
    }
}