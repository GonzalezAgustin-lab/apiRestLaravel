<?php

namespace App\Enums;

enum PlayerPosition :string
{    
    case DEFENDER = 'defender';
    case MIDFIELDER = 'midfielder';
    case FORWARD = 'forward';
}