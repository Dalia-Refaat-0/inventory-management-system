<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class SKU implements Stringable
{
    private const PATTERN = '/^SKU-[A-Z]{3}-\d{4}-\d{4}$/';

    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $value): self
    {
        $value = strtoupper(trim($value));

        if (! preg_match(self::PATTERN, $value)) {
            throw new InvalidArgumentException(
                "Invalid SKU format: {$value}. Expected: SKU-ABC-2501-0042"
            );
        }

        return new self($value);
    }


    public static function generate(): self
    {
        $prefix = collect(range('A', 'Z'))->random(3)->implode('');
        $date   = now()->format('ym');
        $number = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return new self("SKU-{$prefix}-{$date}-{$number}");
    }

    public function value(): string
    {
        return $this->value;
    }

    public function prefix(): string
    {
        return explode('-', $this->value)[1];
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}