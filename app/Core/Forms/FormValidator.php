<?php

namespace App\Core\Forms;

use App\Core\Forms\Fields\CheckboxInput;
use App\Core\Forms\Fields\DateRangeInput;
use App\Core\Forms\Fields\EmailInput;
use App\Core\Forms\Fields\FileInput;
use App\Core\Forms\Fields\MapInput;
use App\Core\Forms\Fields\NumberInput;
use App\Core\Forms\Fields\SelectInput;
use App\Core\Forms\Fields\SectionHeader;
use App\Core\Forms\Fields\TextAreaInput;
use Illuminate\Validation\Rule;
use Psr\Log\LoggerInterface;

/**
 * Gera regras de validação Laravel a partir de um FormSchema.
 * 
 * Extrai regras de:
 * 1. FormField::getRules() - regras customizadas definidas manualmente
 * 2. Tipo do campo - infere tipo de validação baseado em type (email, number, etc)
 * 3. Condições - aplica required/nullable conforme FieldCondition
 * 4. Classes especializadas - regras específicas por tipo (SelectInput, FileInput, etc)
 */
class FormValidator
{
    private bool $debug = false;
    private ?LoggerInterface $logger = null;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Habilita logging de debug.
     * 
     * @param bool $enabled
     * @return $this
     */
    public function debug(bool $enabled = true): static
    {
        $this->debug = $enabled;
        return $this;
    }

    /**
     * Gera regras de validação Laravel a partir de um FormSchema.
     * 
     * @param FormSchema $schema
     * @param array<string, mixed> $data Dados atuais (usado para condicionalidade)
     * @return array<string, array<string>> Laravel validation rules
     * 
     * @throws \InvalidArgumentException se schema é inválido
     */
    public function fromSchema(FormSchema $schema, array $data = []): array
    {
        $errors = $schema->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                'Schema inválido: ' . implode(', ', $errors)
            );
        }

        $rules = [];

        foreach ($schema->getInputs() as $field) {
            // DateRangeInput submits as two flat keys (startName / endName), not the composite key
            if ($field instanceof DateRangeInput) {
                $meta      = $field->getMetadata();
                $startKey  = $meta['startName'] ?? ($field->getKey() . '_start');
                $endKey    = $meta['endName']   ?? ($field->getKey() . '_end');
                $baseRules = $field->getRules() ? explode('|', $field->getRules()) : [];
                // Remove 'sometimes' from start — only applies to the overall field
                $startRules = array_values(array_filter($baseRules));
                $endRules   = array_merge($startRules, ["after_or_equal:{$startKey}"]);
                $rules[$startKey] = $startRules;
                $rules[$endKey]   = $endRules;
                $this->log("DateRange '{$field->getKey()}' → '{$startKey}': [" . implode(', ', $startRules) . "], '{$endKey}': [" . implode(', ', $endRules) . ']');
                continue;
            }

            $fieldRules = $this->rulesForField($field, $data);
            $rules[$field->getKey()] = $fieldRules;

            $this->log("Campo '{$field->getKey()}': [" . implode(', ', $fieldRules) . ']');
        }

        return $rules;
    }

    /**
     * Gera regras de validação para um campo específico.
     * 
     * @param FormField $field
     * @param array<string, mixed> $data Dados atuais
     * @return array<string>
     */
    private function rulesForField(FormField $field, array $data = []): array
    {
        $rules = [];

        // 1. Regras customizadas definidas no FormField
        if ($field->getRules()) {
            $customRules = explode('|', $field->getRules());
            $rules = array_merge($rules, array_filter($customRules));
        }

        // 2. Regras baseadas em tipo do campo
        $typeRules = $this->rulesForType($field);
        $rules = array_merge($rules, $typeRules);

        // 3. Required ou nullable (conforme condição)
        $requiredRule = $this->requiredRuleForField($field, $data);
        if ($requiredRule) {
            $rules[] = $requiredRule;
        }

        // Filtra e remove duplicatas
        return array_values(array_unique(array_filter($rules)));
    }

    /**
     * Retorna regras baseadas no tipo do campo.
     * 
     * @param FormField $field
     * @return array<string>
     */
    private function rulesForType(FormField $field): array
    {
        if ($field instanceof EmailInput) {
            return ['email'];
        }

        if ($field instanceof NumberInput) {
            return ['numeric'];
        }

        if ($field instanceof SelectInput) {
            return [];
        }

        if ($field instanceof FileInput) {
            return ['file', 'max:5120']; // 5MB default
        }

        if ($field instanceof CheckboxInput) {
            return ['boolean'];
        }

        if ($field instanceof TextAreaInput) {
            return [];
        }

        if ($field instanceof MapInput) {
            return [];
        }

        if ($field instanceof SectionHeader) {
            return []; // Sem validação para headers
        }

        // Default para TextInput e outros
        return ['string'];
    }

    /**
     * Retorna regra de required/nullable para um campo.
     * 
     * Considera:
     * - required: true/false do campo
     * - condition: se campo tem condição, retorna RequiredIf condicional
     * 
     * @param FormField $field
     * @param array<string, mixed> $data
     * @return string|null
     */
    private function requiredRuleForField(FormField $field, array $data = []): ?string
    {
        $condition = $field->getCondition();

        if ($condition === null) {
            // Sem condição - simply required ou nullable
            return $field->getRequired() ? 'required' : 'nullable';
        }

        // Com condição - usa Rule::requiredIf
        if ($field->getRequired()) {
            return Rule::requiredIf(function () use ($condition, $data) {
                $targetValue = $data[$condition->field] ?? null;
                return $condition->evaluate($targetValue);
            });
        }

        return 'nullable';
    }

    /**
     * Log de debug.
     *
     * @param string $message
     * @return void
     */
    private function log(string $message): void
    {
        if ($this->debug && $this->logger) {
            $this->logger->debug("[FormValidator] $message");
        }
    }
}