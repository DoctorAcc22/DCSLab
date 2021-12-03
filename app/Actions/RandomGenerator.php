<?php

namespace App\Actions;

use Illuminate\Support\Facades\Config;

class RandomGenerator
{
    private static $RSeed = 0;

    public function seed($s = 0)
    {
        self::$RSeed = abs(intval($s)) % 9999999 + 1;
        $this->generateNumber();
    }

    public function generateNumber($min = 0, $max = 9999999)
    {
        if ($max == 0) return 0;

        if (self::$RSeed == 0)
            $this->seed(mt_rand());

        self::$RSeed = (self::$RSeed * 125) % 2796203;

        return self::$RSeed % ($max - $min + 1) + $min;
    }

    public function generateFixedLengthNumber($length = 2)
    {
        if ($length < 2) $length = 2;

        return rand(pow(10, $length - 1), pow(10, $length) - 1);
    }

    public function generateOne($max = 0)
    {
        if ($max == 0) {
            return $this->generateNumber(0, 9999999);
        } else {
            return $this->generateNumber(0, $max);
        }
    }

    public function generateAlphaNumeric($length)
    {
        $generatedString = '';
        $characters = array_merge(Config::get('const.DEFAULT.RANDOMSTRINGRANGE.ALPHABET'), Config::get('const.DEFAULT.RANDOMSTRINGRANGE.NUMERIC'));
        $max = sizeof($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $characters[mt_rand(0, $max)];
        }

        return strtoupper($generatedString);
    }

    public function randomTrueOrFalse($howManyTimes = 1)
    {
        if ($howManyTimes <= 1) return (bool)rand(0,1);

        $result = array();
        
        for($i = 0; $i < $howManyTimes; $i++) {
            $result[$i] = (bool)rand(0,1);
        }

        return $result;
    }

    public function generateRandomOneZero($maxZero = 1)
    {
        if ($maxZero == 1) return 10;

        $rand = rand(2, $maxZero);

        return pow(10, $rand);
    }
}
