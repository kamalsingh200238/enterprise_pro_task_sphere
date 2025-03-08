<?php

namespace App\Enums;

enum FlashMessageType: string
{
    case Normal = 'normal';
    case CreatedProject = 'createdProject';
    case CreatedTask = 'createdTask';
}
