<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        $hasMinLength = mb_strlen($password) >= 8;
        $hasUppercase = preg_match('/[A-Z]/', $password) === 1;
        $hasNumber = preg_match('/\\d/', $password) === 1;

        if ($hasMinLength && $hasUppercase && $hasNumber) {
            return;
        }

        $fail('A senha deve ter no mínimo 8 caracteres, 1 letra maiúscula e 1 número.');
    }
}

