<?php

namespace App;

enum UserRole: string
{
    case ADMIN = "admin";
    case SUPERVISOR = "supervisor";
    case STAFF = "staff";
}
