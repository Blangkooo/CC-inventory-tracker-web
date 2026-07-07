<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $needed = null,
        public readonly ?string $available = null,
    ) {
        parent::__construct($message);
    }
}
