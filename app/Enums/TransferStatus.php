<?php

declare(strict_types=1);

namespace App\Enums;

enum TransferStatus: string
{
    case Pending    = 'pending';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * @return self[]
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending   => [self::Cancelled, self::Completed],
            self::Completed,
            self::Cancelled => [],
        };
    }

    public function isFinal(): bool
    {
        return empty($this->allowedTransitions());
    }

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public function isNot(self $status): bool
    {
        return $this !== $status;
    }

    public function isAny(array $statuses): bool
    {
        return in_array($this, $statuses, true);
    }
}