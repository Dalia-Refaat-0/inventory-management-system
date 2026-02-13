<?php

declare(strict_types=1);

namespace App\Enums;

enum TransferStatus: string
{
    case Pending    = 'pending';
    case Approved   = 'approved';
    case Processing = 'processing';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case Rejected   = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
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
            self::Pending    => [self::Approved, self::Cancelled],
            self::Approved   => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped    => [self::Delivered, self::Rejected],
            self::Delivered  => [self::Completed, self::Rejected],
            self::Completed,
            self::Cancelled,
            self::Rejected   => [],
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