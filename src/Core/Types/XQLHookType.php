<?php

namespace App\XQL\Core\Types;

enum XQLHookType
{
    case UPDATE;
    case INSERT;
    case DELETE;
}
