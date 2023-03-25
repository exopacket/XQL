<?php

namespace App\XQL\Classes\Types;

enum XQLBindingType: int
{
    case DB_TO_FILE = 1;
    case FILE_TO_DB = 2;
}