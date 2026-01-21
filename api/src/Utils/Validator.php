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

    public function required(string $field, string $message = null): self
    {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = $message ?? "Le champ {$field} est requis";
        }
        return $this;
    }

    public function email(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Adresse email invalide";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit contenir au moins {$min} caractères";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $message = null): self
    {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "Le champ {$field} ne doit pas dépasser {$max} caractères";
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

    public function integer(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être un entier";
        }
        return $this;
    }

    public function inArray(string $field, array $allowed, string $message = null): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = $message ?? "Le champ {$field} doit être l'une des valeurs: " . implode(', ', $allowed);
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d', string $message = null): self
    {
        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être une date valide au format {$format}";
            }
        }
        return $this;
    }

    public function datetime(string $field, string $message = null): self
    {
        return $this->date($field, 'Y-m-d H:i:s', $message);
    }

    public function uuid(string $field, string $message = null): self
    {
        if (isset($this->data[$field])) {
            $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être un UUID valide";
            }
        }
        return $this;
    }

    public function phone(string $field, string $message = null): self
    {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $phone = preg_replace('/[\s\-\.]/', '', $this->data[$field]);
            if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
                $this->errors[$field] = $message ?? "Numéro de téléphone invalide. Format attendu : 06 12 34 56 78";
            }
        }
        return $this;
    }

    public function between(string $field, int|float $min, int|float $max, string $message = null): self
    {
        if (isset($this->data[$field])) {
            $value = (float)$this->data[$field];
            if ($value < $min || $value > $max) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être entre {$min} et {$max}";
            }
        }
        return $this;
    }

    public function json(string $field, string $message = null): self
    {
        if (isset($this->data[$field])) {
            if (is_string($this->data[$field])) {
                json_decode($this->data[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->errors[$field] = $message ?? "Le champ {$field} doit être un JSON valide";
                }
            } elseif (!is_array($this->data[$field])) {
                $this->errors[$field] = $message ?? "Le champ {$field} doit être un JSON valide";
            }
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function validate(): void
    {
        if (!$this->isValid()) {
            Response::validationError($this->errors);
        }
    }

    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int)$value;
    }

    /**
     * Normalise un numéro de téléphone avec indicatif pays
     * Stocke toujours au format international : +33612345678
     *
     * @param string|null $phone Le numéro de téléphone
     * @param string $countryCode L'indicatif pays (ex: "+33", "+32")
     * @return string|null Le numéro normalisé avec indicatif
     */
    public static function normalizePhone(?string $phone, string $countryCode = '+33'): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        // Retirer espaces, tirets, points, parenthèses
        $phone = preg_replace('/[\s\-\.\(\)]/', '', trim($phone));

        // Si le numéro commence déjà par +, on le garde tel quel
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Si le numéro commence par 00, remplacer par +
        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        // Si le numéro commence par 0, remplacer par l'indicatif
        if (str_starts_with($phone, '0')) {
            return $countryCode . substr($phone, 1);
        }

        // Sinon, ajouter l'indicatif
        return $countryCode . $phone;
    }

    /**
     * Formate un numéro de téléphone pour l'affichage
     * +33612345678 -> +33 6 12 34 56 78
     */
    public static function formatPhoneForDisplay(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        // Format français : +33 6 12 34 56 78
        if (str_starts_with($phone, '+33') && strlen($phone) === 12) {
            return '+33 ' . substr($phone, 3, 1) . ' ' .
                   substr($phone, 4, 2) . ' ' .
                   substr($phone, 6, 2) . ' ' .
                   substr($phone, 8, 2) . ' ' .
                   substr($phone, 10, 2);
        }

        // Format belge : +32 4 12 34 56 78
        if (str_starts_with($phone, '+32') && strlen($phone) >= 11) {
            $number = substr($phone, 3);
            return '+32 ' . implode(' ', str_split($number, 2));
        }

        // Format UK : +44 7xxx xxx xxx
        if (str_starts_with($phone, '+44') && strlen($phone) >= 12) {
            $number = substr($phone, 3);
            // UK mobile: 7xxx xxx xxx
            if (str_starts_with($number, '7') && strlen($number) === 10) {
                return '+44 ' . substr($number, 0, 4) . ' ' .
                       substr($number, 4, 3) . ' ' .
                       substr($number, 7, 3);
            }
            // Generic UK: group by 3
            return '+44 ' . implode(' ', str_split($number, 3));
        }

        // Format suisse : +41 7x xx xx xx xx
        if (str_starts_with($phone, '+41') && strlen($phone) >= 11) {
            $number = substr($phone, 3);
            return '+41 ' . implode(' ', str_split($number, 2));
        }

        // Format luxembourgeois : +352 6xx xxx xxx
        if (str_starts_with($phone, '+352') && strlen($phone) >= 12) {
            $number = substr($phone, 4);
            return '+352 ' . implode(' ', str_split($number, 3));
        }

        // Format générique : grouper par 2 après l'indicatif
        if (str_starts_with($phone, '+')) {
            // Trouver la fin de l'indicatif (2-3 chiffres après +)
            preg_match('/^\+(\d{1,3})(.*)$/', $phone, $matches);
            if ($matches) {
                $countryCode = $matches[1];
                $number = $matches[2];
                return '+' . $countryCode . ' ' . implode(' ', str_split($number, 2));
            }
        }

        return $phone;
    }
}
