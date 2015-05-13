<?php

class Errorbase {
    protected static $err = array(
        0  => 'Class file not found',
        1  => 'No configuration file',
        2  => 'Unknown method',
    );

    public static function e($id)
    {
        $info='Unknown error';
        if (isset(self::$err[$id])) $info=self::$err[$id];
        return array('number'=>$id,'info'=>$info);
    }
}
