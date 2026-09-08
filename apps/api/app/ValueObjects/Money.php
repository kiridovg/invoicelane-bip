<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * An exact decimal amount tied to a currency.
 *
 * Amounts are kept as strings and combined with bcmath: floats cannot
 * represent 0.1 exactly, and a single lost cent breaks the
 * invoices_gross_consistent constraint in the database.
 */
final readonly class Money
{
    private const SCALE = 2;

    private function __construct(
        public string $amount,
        public string $currency,
    ) {}

    public static function of(string $amount, string $currency): self
    {
        if (preg_match('/^-?\d+(\.\d{1,2})?$/', $amount) !== 1) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a decimal amount with at most two fraction digits.', $amount)
            );
        }

        return new self(bcadd($amount, '0', self::SCALE), strtoupper($currency));
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf('Cannot combine %s with %s.', $this->currency, $other->currency)
            );
        }

        return new self(bcadd($this->amount, $other->amount, self::SCALE), $this->currency);
    }
}
