<?php

namespace Arkenstone\Core\Services;

class UtilityService
{
    /**
     * generate random string
     * @param int $length
     * @return string
     */
    public function generateRandomString(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }    


    /**
     * generate random number
     * @param int $length
     * @return string
     */
    public function generateRandomNumber(int $length = 10): string
    {
        return substr(str_shuffle(str_repeat($x = '0123456789', ceil($length / strlen($x)))), 1, $length);
    }

    public function getName(): string
    {
        return "Arkenstone Test";
    }
}
