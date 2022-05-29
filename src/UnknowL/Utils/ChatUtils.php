<?php

namespace UnknowL\Utils;

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
        "negre"];

    public static function containBadWord(string $message): bool{
        $str  = self::withoutAccent($message);
        foreach(self::BAD_WORDS as $badWord){
            if(str_contains(strtolower($str), $badWord)){
                return true;
            }
        }
        return false;
    }

    public static function withoutAccent(string $message): string{
        $messages = str_replace(' ', '-', $message);
        $messages = preg_replace('/pratique/[^A-Za-z0-9-]/', '', $messages);
        return preg_replace('/pratique/-+/', '-', $messages);
    }

}