<?php

namespace Polaris\utils;

class ChatUtils{

    public const BAD_WORDS = [
        "fuck",
        "shit",
        "bitch",
        "ass",
        "pussy",
        "salope",
        "salaud",
        "pd",
        "fdp",
        "pute",
        "putain",
        "negre",
        "ntm"];

    public static function containBadWord(string $message): bool{
        $str  = self::withoutAccent($message);
        foreach(self::BAD_WORDS as $badWord){
            if(str_contains(strtolower($str), $badWord)){
                return true;
            }
        }
        return false;
    }

    public static function withoutSpace(string $str): string{
        return str_replace(" ", "", $str);
    }

    public static function withoutAccent(string $str): string{
        $str = strtolower($str);
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

}