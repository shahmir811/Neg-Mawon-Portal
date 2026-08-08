<?php

namespace App\Enums;

enum AgreementStatus: string
{
    case NotSubmitted = 'not_submitted';
    case Pending = 'pending';
    case Approved = 'approved';
}
