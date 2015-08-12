<?php

    function json_config($application_json,$deeparray=false,$host_specific=true) {
        if (!file_exists($application_json)) return [];
        
        $config=json_decode(file_get_contents($application_json),true);
        
        if ($host_specific) {
        
            if (isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']),'engine'))
            {
                $config_file=str_replace('~','-',$_SERVER['APPLICATION_ID']);
            }
            else
            {
                $config_file=strtolower($_SERVER['HTTP_HOST']);
            }        
    
            $f=dirname($application_json).'/'.$config_file.'.json';
            
            if (file_exists($f))
            {
                $config=array_merge($config,json_decode(file_get_contents($f),true));
            }
        }

        if (!$deeparray) return $config;


        return $config;
    }