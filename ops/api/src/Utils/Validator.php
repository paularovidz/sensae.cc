<?php

declare(strict_types=1);

namespace App\Utils;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $message = null): self
    {
        if (!isset($this->data[$field]) || $this->data[$field] === '' || $this->data[$field] === null) {
            $this->errors[$field] = $message ?? "Le champ {$field} est requis";
        }
        return $this;
    }

    public function email(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être un email valide";
        }
        return $this;
    }

    public function numeric(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être numérique";
        }
        return $this;
    }

    public function min(string $field, int $min, string $message = null): self
    {
        if (isset($this->data[$field])) {
            $value = is_string($this->data[$field]) ? strlen($this->data[$field]) : $this->data[$field];
            if ($value < $min) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être au minimum {$min}";
            }
        }
        return $this;
    }

    public function max(string $field, int $max, string $message = null): self
    {
        if (isset($this->data[$field])) {
            $value = is_string($this->data[$field]) ? strlen($this->data[$field]) : $this->data[$field];
            if ($value > $max) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être au maximum {$max}";
            }
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d', string $message = null): self
    {
        if (isset($this->data[$field])) {
            $d = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être une date valide";
            }
        }
        return $this;
    }

    public function in(string $field, array $values, string $message = null): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être une valeur parmi: " . implode(', ', $values);
        }
        return $this;
    }

    public function uuid(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !UUID::isValid($this->data[$field])) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être un UUID valide";
        }
        return $this;
    }

    public function positive(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && (float) $this->data[$field] <= 0) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être positif";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->data;
    }
}
