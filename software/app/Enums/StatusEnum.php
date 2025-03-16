<?php

namespace App\Enums;

enum StatusEnum: string
{
    case Backlog = 'Backlog';
    case InProgress = 'In Progress';
    case OnHold = 'On Hold';
    case InReview = 'In Review';
    case Done = 'Done';
}
