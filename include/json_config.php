<?php

    function json_config($application_json,$deeparray=false,$host_specific=true) {
        if (!file_exists($application_json)) return [];
        
        $config=json_decode(file_get_contents($application_json),true);
        
        if ($host_specific) {
        
            $f='';
            if (isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']),'engine'))
            {
                $config_file=str_replace('~','-',$_SERVER['APPLICATION_ID']);
                $f=dirname($application_json).'/'.$config_file.'.json';
            }
             
            if ($f && file_exists($f))
            {
                
                //echo $f; print_r(json_decode(file_get_contents($f),true));
                $config=array_merge($config,json_decode(file_get_contents($f),true));
            }
            
            $f='';
            if (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']))
            {
                $config_file=strtolower($_SERVER['HTTP_HOST']);
                $f=dirname($application_json).'/'.$config_file.'.json';
            }
            
            if ($f && file_exists($f))
            {
                //echo $f; print_r(json_decode(file_get_contents($f),true));
                $config=array_merge($config,json_decode(file_get_contents($f),true));
            }
            
            
            $f='';
            if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] )
            {
                $ref=[];
                preg_match('~^http[^:]*://([^/]+)/~',$_SERVER['HTTP_REFERER'],$ref);
                if (isset($ref[1])) $f=dirname($application_json).'/'.$ref[1].'.json';
            }
            
            if ($f && file_exists($f))
            {
                $config=array_merge($config,json_decode(file_get_contents($f),true));
            }

            $f='';
            if (isset($_GET['_site']) && $_GET['_site'] )
            {
                $config_file=strtolower($_GET['_site']);
                $config_file=str_replace('..','',$config_file);
                $f=dirname($application_json).'/'.$config_file.'.json';
            }
            
            if ($f && file_exists($f))
            {
                $config=array_merge($config,json_decode(file_get_contents($f),true));
            }

            
        }

        //mydie($config);
        
        if (!$deeparray) return $config;


        return $config;
    }