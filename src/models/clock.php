<?php
class Clock {

    private static $fakeNow = '2025-06-25';

    public static function today($format = 'Y-m-d') {
        return date($format, strtotime(self::$fakeNow));
    }

    public static function strtotime($relative) {
        return strtotime($relative, strtotime(self::$fakeNow));
    }
}
?>