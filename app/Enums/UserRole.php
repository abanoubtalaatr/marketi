<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';
    case SupportAgent = 'support_agent';
}
