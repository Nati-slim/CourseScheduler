<?php
//http://3ft9.com/snippet-time-class-for-php/
class Time
{
    const OneMinute = 60;
    const FiveMinutes = 300;
    const TenMinutes = 600;
    const FifteenMinutes = 900;
    const HalfHour = 1800;
    const OneHour = 3600;
    const SixHours = 21600;
    const HalfDay = 43200;
    const OneDay = 86400;
    const SevenDays = 604800;
    const ThirtyDays = 2592000;
    const OneYear = 31536000;

    public static function GetAbsolute($time, $format = false)
    {
        if (is_numeric($time) and $time &lt; (time()-1))
        {
            $time = time() + $time;
        }
        else
        {
            $time = strtotime($time);
        }
        return (false === $format ? $time : date($format, $time));
    }
}
?>
