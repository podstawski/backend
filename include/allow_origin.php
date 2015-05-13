<?php
    function allow_origin($referers)
    {
        if (!is_array($referers)) $referers=[$referers];
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer=strtolower($_SERVER['HTTP_REFERER']);
            $pos=strpos($referer,'//');
            $referer_ok=substr($referer,0,$pos+2);
            $referer=substr($referer,$pos+2);
            $pos=strpos($referer,'/');
            $referer_ok.=substr($referer,0,$pos);
            
            foreach ($referers AS $r)
            {      
                if (preg_match('/'.$r.'/i',$referer_ok)) {
                    Header('Access-Control-Allow-Origin: '.$referer_ok);
                    Header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
                    Header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');
                    header("Access-Control-Allow-Credentials: true");
                    
                    break;
                }
            }
        }        
    }