<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Elimina puntos y guiones, pasa todo a mayúsculas
        $rut = strtoupper(preg_replace('/[^0-9Kk]/', '', (string)$value));

        // Verifica formato: números + dígito verificador
        if (!preg_match('/^(\d+)([K0-9])$/', $rut, $m)) {
            $fail('El :attribute no es válido.');
            return;
        }

        $num = $m[1];
        $dv  = $m[2];

        // Cálculo del dígito verificador
        $s = 0;
        $f = 2;
        for ($i = strlen($num)-1; $i >= 0; $i--) {
            $s += intval($num[$i]) * $f;
            $f = $f === 7 ? 2 : $f + 1;
        }

        $dvr = 11 - ($s % 11);
        $dvr = $dvr === 11 ? '0' : ($dvr === 10 ? 'K' : (string)$dvr);

        if ($dvr !== $dv) {
            $fail('El :attribute no es válido.');
        }
    }
}
