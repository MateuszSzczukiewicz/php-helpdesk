<?php

declare(strict_types=1);

readonly class ValidationResult
{
    public function __construct(
        public bool $valid,
        public string $message = ''
    ) {}

    public static function success(): self
    {
        return new self(valid: true, message: '');
    }

    public static function failure(string $message): self
    {
        return new self(valid: false, message: $message);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrorMessage(): string
    {
        return $this->message;
    }
}
