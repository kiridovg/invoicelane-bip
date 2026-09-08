<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    private const SCALE = 2;

    private function __construct(
        public string $amount,
        public string $currency,
    ) {
    }

    public static function of(string $amount, string $currency): self
    {
        if (preg_match('/^-?\d+(\.\d{1,2})?$/', $amount) !== 1) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a decimal amount with at most two fraction digits.', $amount)
            );
        }

        return new self(self::normalize($amount), $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcadd($this->amount, $other->amount, self::SCALE), $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency
            && bccomp($this->amount, $other->amount, self::SCALE) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 1;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === -1;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    private static function normalize(string $amount): string
    {
        return bcadd($amount, '0', self::SCALE);
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf('Cannot combine %s with %s.', $this->currency, $other->currency)
            );
        }
    }
}
