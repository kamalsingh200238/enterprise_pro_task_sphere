<?php

namespace App\Enums;

enum FlashMessageVariant: string
{
    case Success = 'success';
    case Error = 'error';
    case Info = 'info';
    case Warning = 'warning';
}
