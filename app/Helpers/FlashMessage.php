<?php

namespace App\Helpers;

class FlashMessage
{
    public string $heading;

    public string $description;

    public string $variant;

    public int $duration;

    public function __construct(string $heading, string $description = '', string $variant = 'success', int $duration = 10000)
    {
        $this->heading = $heading;
        $this->description = $description;
        $this->variant = $variant;
        $this->duration = $duration;
    }

    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'description' => $this->description,
            'variant' => $this->variant,
            'duration' => $this->duration,
        ];
    }
}
