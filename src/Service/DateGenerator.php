<?php

namespace App\Service;

use App\Entity\Card;
use Symfony\Component\Config\Definition\Exception\Exception;

class DateGenerator
{
    const RESET = 0;
    const HARD = 1.2;
    const MEDIUM = 2.4;
    const EASY = 3.6;

    const LEVEL = [
        'RESET' => self::RESET,
        'HARD' => self::HARD,
        'MEDIUM' => self::MEDIUM,
        'EASY' => self::EASY
    ];

    public function getDate(Card $card, $answer)
    {
        $step = $card->getStep();

        $answerConst = strtoupper($answer);

        if (!array_key_exists($answerConst, self::LEVEL)) {
            throw new Exception("The key " . sprintf("'%s'", $answerConst) . " is not registrated as a difficulty level");
        }

        // connect the value of the answer with the value of the related CONST
        $const = self::LEVEL[$answerConst];

        $date = $const * $step;
        
        // return a round date, i.e. a number of days
        return round($date); 
    }
}