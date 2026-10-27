<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis SIBIS. Pesannya aman ditampilkan ke pengguna.
 */
class AturanBisnisException extends RuntimeException
{
}
