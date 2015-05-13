<?php

function mydie($txt,$h1='Info',$print_r=true)
{
    @header('Content-type: text/html; charset=utf-8');
    
    if ($h1) echo "<h1>$h1</h1>";
    die('<pre>' . ($print_r ? print_r($txt, true) : var_export($txt,true)));
}