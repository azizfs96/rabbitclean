<?php

namespace App\Enum;

enum FileTypes: string
{
    case DOCUMENT = 'Document';
    case IMAGE = 'image';
    case AUDIO = 'Audio';
    case VIDEO = 'Video';
}
