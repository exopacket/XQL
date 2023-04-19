<?php

namespace App\XQL\Classes\Types;

enum XQLHookType
{
    case UPDATE;
    case INSERT;
    case DELETE;
}
