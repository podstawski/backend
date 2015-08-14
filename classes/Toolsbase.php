<?php

use \google\appengine\api\mail\Message;
use google\appengine\api\cloud_storage\CloudStorageTools;

class Toolsbase {
    
    
    public static function semaphore($key,$set=null)
    {
	$key='sem:'.md5($key);
	return self::memcache($key,$set);
    }

    public static function memcache($key,$val=null,$expire_in=1800)
    {
        if (isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']),'engine'))
	{
	    $memcache = new Memcache;
	    if (!is_null($val))
	    {
		$memcache->set($key,array('v'=>$val,'t'=>Bootstrap::$main->now+$expire_in));
		return $val;
	    }
	    $val=$memcache->get($key);
	    if ($val===false) return false;
	    if ($val['t'] > Bootstrap::$main->now) return $val['v'];
	    return false;
	    
	}
        
        $key_file=sys_get_temp_dir().'/'.md5($_SERVER['HTTP_HOST'].$key).'.memcache';
        if (!is_null($val))
        {
	    
            file_put_contents($key_file,serialize($val));
	    //mydie($val,"$key:$key_file");
            return $val;
        }
        $time=0;
        if (file_exists($key_file)) $time=filemtime($key_file);
        if ($time+($expire_in/2) > Bootstrap::$main->now) return unserialize(file_get_contents($key_file));
        return false;
    }

    public static function str_to_url($s, $case = 0, $dots=false)
    {
	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
		'ß' => 'ss', 
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
		'ÿ' => 'y',
 
		// Latin symbols
		'©' => '(c)',
 
		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
 
		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
 
		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
 
		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
 
		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
		'Ž' => 'Z', 
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z', 
 
		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
		'Ż' => 'Z', 
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
 
		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
	);
     
        $out = str_replace(array_keys($char_map), $char_map, $s);
        $out = str_replace(' ', '-', trim($out));
        

        /*        
        for ($i=0;$i<strlen($out)-1;$i++) {
            if ((ord($out[$i])==216 || ord($out[$i])==217) && ord($out[$i+1])>127)
            {
                $ar = new I18N_Arabic('Transliteration');    
                $out = trim($ar->ar2en($out));
                break;
            }
        }
        */    
        
        
        $out=str_replace('/','-',$out);
        if ($dots) $out=str_replace('.','-',$out);
        $out=preg_replace('#[^0-9a-z\/\-\._]#i','-',$out);
        $out=preg_replace('#-+#','-',$out);

        while (strlen($out)>3 && $out[0]=='-') $out=substr($out,1);
	while (strlen($out)>3 && substr($out,-1)=='-') $out=substr($out,0,strlen($out)-1);
        
        if ($case == -1) {
            return strtolower($out);
        } else {
            if ($case == 1) {
                return strtoupper($out);
            } else {
                return ($out);
            }
        }
    }


    
    public static function geoip($ip=null)
    {
	if (!$ip) $ip=Bootstrap::$main->ip;

	if ( isset($_SERVER['HTTP_X_APPENGINE_CITYLATLONG']) && isset($_SERVER['HTTP_X_APPENGINE_COUNTRY']) && strpos($_SERVER['HTTP_X_APPENGINE_CITYLATLONG'],',') )
	{
	    $latlng=explode(',',$_SERVER['HTTP_X_APPENGINE_CITYLATLONG']);
	    $geo=[
		  'country'=>['iso_code'=>$_SERVER['HTTP_X_APPENGINE_COUNTRY']],
		  'location'=>[
		    'latitude'=>$latlng[0]+0,
		    'longitude'=>$latlng[1]+0,
		    'country'=>$_SERVER['HTTP_X_APPENGINE_COUNTRY']
		  ]
	    ];
	    
	    if (isset($_SERVER['HTTP_X_APPENGINE_CITY']))
	    {
		$geo['location']['city']=$_SERVER['HTTP_X_APPENGINE_CITY'];
	    }
	    
	    if ($geo['location']['latitude'] && $geo['location']['longitude']) return $geo;
	}
	
	
	if (substr($ip,0,7)=='192.168' || substr($ip,0,3)=='10.') return false;
	
	if (!Bootstrap::$main->session('geo'))
	{
	    $token='geo:'.$ip;
	    
	    $geo=Tools::memcache($token);
	    if (!$geo)
	    {
		$url='https://geoip.maxmind.com/geoip/v2.1/city/'.$ip;
		
		$context = array(
		    "http"=> array(
			"method" => "GET",
			"header" => "Authorization: Basic " . base64_encode(Bootstrap::$main->getConfig('maxmind.user_id').':'.Bootstrap::$main->getConfig('maxmind.license_key')) . "\r\n",
		    ),
		    "ssl" => array (
			"verify_peer" => "0",
			'ciphers'=>'AES256-SHA'
		    )
		);
		$context['https']=$context['http'];

		$context = stream_context_create($context);
		$geo = file_get_contents($url, false, $context);		
 
		if ($geo)
		{
		    $geo=json_decode($geo,true);
		    if (isset($geo['country']['iso_code'])) $geo['location']['country']=$geo['country']['iso_code'];
		    if (isset($geo['city']['names']['en'])) $geo['location']['city']=$geo['city']['names']['en'];
		    if (isset($geo['city']['names'][Bootstrap::$main->lang])) $geo['location']['city']=$geo['city']['names'][Bootstrap::$main->lang];
		    
		    self::memcache($token,$geo,3*24*3600);
		}
	    }
	    
	    Bootstrap::$main->session('geo',$geo);
	}
	
    
	return Bootstrap::$main->session('geo');
	
    }
    
    
    public static function saveRoot($prefix='')
    {
		if (Bootstrap::$main->appengine)
		{
			require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
		}
	
		$root=Bootstrap::$main->appengine ? 'gs://'.CloudStorageTools::getDefaultGoogleStorageBucketName() : Bootstrap::$main->mediaPath();
		if ($prefix) $root.='/'.$prefix;
		
		if (!Bootstrap::$main->appengine && !file_exists(dirname($root))) mkdir(dirname($root),0755,true);
		return $root;
    }
    
    
    public static function save($file,$data,$fromfile=null)
    {
	
		$root=self::saveRoot().'/';
		
		$file=$root.$file;
		if (!Bootstrap::$main->appengine)
		{
			@mkdir(dirname($file),0755,true);
		}
		
		if (is_null($fromfile))
			file_put_contents($file,$data);
		else
			rename($root.$fromfile,$file);    
    }
    
    public static function log($app,$data=null)
    {
	
		if (Bootstrap::$main->appengine)
		{
			require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
		}	
		$root=Bootstrap::$main->appengine ? 'gs://'.CloudStorageTools::getDefaultGoogleStorageBucketName().'/' : Bootstrap::$main->mediaPath().'/';
		$file=$root.'log/'.$app.'/'.date('Y').'/'.sprintf('%02d',date('m')).'/'.sprintf('%02d',date('d'));
	
		$d=date('Y-m-d_H-i-s');
		$f=0;
		while (file_exists("$file/$d-$f.txt")) $f++;
		$file="$file/$d-$f.txt";	
		
	
		
		$header=date('Y-m-d H:i:s');
		if (isset($_SERVER['REMOTE_ADDR'])) $header.=", IP:".$_SERVER['REMOTE_ADDR'];
		if (isset(Bootstrap::$main->user['email'])) $header.=", email: ".Bootstrap::$main->user['email'];
		$header.="\n";
		
		self::save(substr($file,strlen($root)),$header.print_r($data,1)."\n\n");
	

    }
	
	
	public static function logrotator()
	{
	
		$base=Tools::saveRoot('log');
		
		$ts=time()-24*3600;
		$year=date('Y',$ts);
		$month=date('m',$ts);
		$day=date('d',$ts);
		
		foreach (scandir($base) AS $component)
		{
			echo "Starting $component<br/>";
			if ($component[0]=='.') continue;
			if (substr($component,-1)=='/') $component=substr($component,0,strlen($component)-1);
			$dir="$base/$component/$year/$month/$day";
			
			echo "&nbsp; Scaning dir $dir<br/>";
			if (!file_exists($dir))
			{
				echo "&nbsp; &nbsp; does not exist<br/>";
				continue;
			}
			
			$log='';
			foreach(scandir($dir) AS $f)
			{
				if ($f[0]=='.') continue;
				$log.=file_get_contents("$dir/$f");
				unlink("$dir/$f");
			}
			file_put_contents("$base/$component/$year/$month/$day.txt",$log);
			@unlink($dir);
			@rmdir($dir);
		}
	}

	
	
}
