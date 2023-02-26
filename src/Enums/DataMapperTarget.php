<?php

declare(strict_types=1);

namespace NorseBlue\Xmlify\Enums;

enum DataMapperTarget: string
{
    case Attribute = 'attribute';
    case Element = 'element';
    case Root = 'root';
}
