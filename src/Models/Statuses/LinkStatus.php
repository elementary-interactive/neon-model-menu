<?php

namespace Neon\Models\Statuses;

use Neon\Models\Statuses\Basic as BasicStatus;

enum LinkStatus: string implements BasicStatus {
    case Active     = 'A';
    case Inactive   = 'I';
    case New        = 'N';

    public static function default(): string {
      return LinkStatus::New->value;
    }
}