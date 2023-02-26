<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Enums;

enum ToXmlOption: int
{
    case NONE = 0;
    case NOEMPTYTAG = 4;
}
