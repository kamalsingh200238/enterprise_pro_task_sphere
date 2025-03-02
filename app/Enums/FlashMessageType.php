<?php

namespace App\Enums;

enum FlashMessageType
{
    case Normal = 'normal';
    case CreatedProject = 'created project';
    case DeletedProject = 'deleted project';
}
