<?php

namespace Polaris\trait;

trait ConversionTrait
{
    protected function secondToTimer(int $seconds): string
    {
        $second = $seconds;
        $minute = 00;
        $hour = 0;
        while ($second >= 60) {
            $minute++;
            $second -= 60;
        }
        while ($minute >= 60) {
            $hour++;
            $minute -= 60;
        }
        if($hour >= 1)
        {
            return $this->timeToTwoChars($hour).":".$this->timeToTwoChars($minute).":".$this->timeToTwoChars($second);
        }
        return $this->timeToTwoChars($minute).":".$this->timeToTwoChars($second);
    }

    /**
     * 1 second => 01 seconds
     * @param int $time
     * @return string
     */
    protected function timeToTwoChars(int $time): string
    {
        if($time >= 10) return $time;
        return "0$time";
    }
}