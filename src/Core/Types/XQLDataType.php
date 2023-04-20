<?php

namespace App\XQL\Core\Types;

enum XQLDataType: int
{
    case INTEGER = 1;
    case FLOAT = 2;
    case STRING = 3;
    case TEXT = 4;
    case BOOLEAN = 5;
    case DATE = 6;
    case TIME = 7;
    case DATETIME = 8;
    case TIMESTAMP = 9;
    case DYNAMIC = 10;
}