<?php
declare(strict_types=1);

namespace App\Core;

final class FormParser
{
    private array $source;

    public function __construct(array $source)
    {
        $this->source = $source;
    }

    public static function fromPost(): self
    {
        return new self($_POST);
    }

    public function parse(array $rules): array
    {
        $data = [];
        $errors = [];

        foreach ($rules as $name => $rule) {
            $type = (string)($rule['type'] ?? 'string');
            $label = (string)($rule['label'] ?? $name);
            $required = (bool)($rule['required'] ?? false);
            $raw = $this->source[$name] ?? ($rule['default'] ?? ($type === 'array' ? [] : ''));
            $value = $this->normalize($raw, $type);

            if ($required && $this->isBlank($value)) {
                $errors[] = 'Поле «' . $label . '» обязательно.';
                $data[$name] = $value;
                continue;
            }

            if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Поле «' . $label . '» должно быть email.';
            }
            if ($type === 'phone' && $value !== '' && !preg_match('/^[0-9+()\-\s]{6,25}$/u', $value)) {
                $errors[] = 'Поле «' . $label . '» должно содержать корректный телефон.';
            }
            if ($type === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = 'Поле «' . $label . '» должно быть ссылкой.';
            }
            if ($type === 'number' && !is_numeric((string)$value)) {
                $errors[] = 'Поле «' . $label . '» должно быть числом.';
            }
            if (!empty($rule['allowed']) && !in_array($value, (array)$rule['allowed'], true)) {
                $errors[] = 'Поле «' . $label . '» содержит недопустимое значение.';
            }

            $data[$name] = $value;
        }

        return [$data, $errors];
    }

    private function normalize(mixed $value, string $type): mixed
    {
        if ($type === 'array') {
            return is_array($value) ? array_values($value) : [$value];
        }
        if ($type === 'checkbox') {
            return empty($value) ? 0 : 1;
        }

        $value = trim((string)$value);
        if ($type === 'email') {
            return strtolower($value);
        }
        if ($type === 'number') {
            return str_replace(',', '.', $value === '' ? '0' : $value);
        }
        return $value;
    }

    private function isBlank(mixed $value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, fn (mixed $item): bool => trim((string)$item) !== '')) === 0;
        }
        return trim((string)$value) === '';
    }
}
