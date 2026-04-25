<?php

namespace App\Enums;

enum ChatMessageStatus: string
{
    case Sent = 'sent';
    case Read = 'read';
    case Hidden = 'hidden';
}
