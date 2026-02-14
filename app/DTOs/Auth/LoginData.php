<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

final class LoginData extends Data
{
    public function __construct(
        #[Required, Email]
        public string $email,

        #[Required, Min(8)]
        public string $password,
    ) {}
}