<?php

    function simple_pass(Array $pass)
    {
	if (isset($_POST['_pass'])) {
		$_COOKIE['PASS']=$_POST['_pass'];
		SetCookie('PASS',$_POST['_pass']);
	}
	if (!isset($_COOKIE['PASS']) || !in_array($_COOKIE['PASS'],$pass)) {
	    
	    die('<html><head><title>Please login</title></head><body><form method="post" style="text-align:center; margin-top:200px;"><input type="password" name="_pass"><input type="submit" value="Password"></form></body></html>');
	}        
    }