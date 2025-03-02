<?php

namespace App\Helpers;

use App\Enums\FlashMessageType;
use App\Enums\FlashMessageVariant;

class FlashMessage
{
    public FlashMessageType $messageType;

    public string $heading;

    public string $description;

    public FlashMessageVariant $variant;

    public int $duration;

    public array $context;

    public function __construct(
        string $heading,
        FlashMessageVariant $variant = FlashMessageVariant::Info,
        FlashMessageType $messageType = FlashMessageType::Normal,
        string $description = '',
        int $duration = 10000,
        array $context = [],
    ) {
        $this->messageType = $messageType;
        $this->description = $description;
        $this->duration = $duration;
        $this->heading = $heading;
        $this->variant = $variant;
        $this->context = $context;
    }

    public function toArray(): array
    {
        return [
            'messageType' => $this->messageType->value,
            'description' => $this->description,
            'variant' => $this->variant->value,
            'duration' => $this->duration,
            'context' => $this->context,
            'heading' => $this->heading,
        ];
    }
}
