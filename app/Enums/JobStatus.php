<?php

namespace App\Enums;

enum JobStatus: string
{
    case Requested = 'requested';
    case Assigned = 'assigned';
    case Completed = 'completed';
}
