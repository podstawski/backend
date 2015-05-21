<?php

class Bootstrapbase
{
    private static $SESSION_PREFIX = 'backend';
    private $session_prefix;
    private $conn,$config,$root;
    public static $main;
    public $ip;
    public $now;
    public $user;
    public $lang;
    public $appengine=false;
    public $beta=false;
    public $admin=false;
    public $system=[];
    protected $json_return=false;
    
    public function __construct($config)
    {
	$this->system['start']=isset($_SERVER['backend_start'])?$_SERVER['backend_start']:microtime(true);
	$this->system['db']=false;
	$postfix=isset($_SERVER['APPLICATION_ID'])?$_SERVER['APPLICATION_ID']:__DIR__;
        $this->session_prefix = self::$SESSION_PREFIX . '_' . md5($postfix);
        self::$main = $this;
        $this->now = time();
        $this->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'0.0.0.0');
	$this->config=$config;
    
	if (isset($_SERVER['REQUEST_URI']))
	{
	    $uri = $_SERVER['REQUEST_URI'];
	    $root = dirname($_SERVER['SCRIPT_NAME']);
    
	    $uri = str_replace($root, '', $uri);
	    if ($root != '/') $root .= '/';
    
	    $this->root = $root;
	}
	else $root='/';
	
    
	$pos=strpos($this->config['db.dsn'],'dbname=');
	if ($pos) $this->session('db_name',substr($this->config['db.dsn'],$pos+7));
	
	$this->user=$this->session('user');

	
	if (isset($_SERVER['SERVER_SOFTWARE']) && strstr(strtolower($_SERVER['SERVER_SOFTWARE']),'engine'))
	{
	    $this->appengine=true;
	    if (isset($_SERVER['APPLICATION_ID']) && strstr($_SERVER['APPLICATION_ID'],'beta')) $this->beta=true;
	}
	else {
	    $this->beta=true;
	}
    }

    
    public function system($mod)
    {
	if (!isset($this->system['traps'])) $this->system['traps']=[];
	if (!isset($this->system['start'])) $this->system['start']=isset($_SERVER['jemyrazem_start'])?$_SERVER['jemyrazem_start']:microtime(true);
	$this->system['traps'][sprintf("%02d",count($this->system['traps'])+1).'_'.$mod]=microtime(true)-$this->system['start'];
    }
    
    
    public function lang()
    {
	
	if ($_l=$this->session('ulang')) return $_l;
	
        $langs = $this->langs();

        // break up string into pieces (languages and q factors)
	$alang=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'en';
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',$alang , $matches);

        if (count($matches[1])) {
            $tmp = array_combine($matches[1], $matches[4]);

            foreach ($tmp as $lang => $val) {
                if ($val === '')
                    $tmp[$lang] = 1;
            }

            arsort($tmp, SORT_NUMERIC);

            $lang = substr(key($tmp), 0, 2);
            if (in_array($lang, $langs)) return $lang;
        }
	

        return 'en';
    }    
    
    public function getConn()
    {
	if (!is_object($this->conn))
	{
	    try {
		$this->conn = new PDO($this->config['db.dsn'],$this->config['db.user'],$this->config['db.pass']);
		$this->system['db']=true;
	    } catch (Exception $e) {
		mydie($e,'Connection error');
	    }
	    
	}

        return $this->conn;
    }

    
    public function session($key = null, $val = null)
    {

        if (is_null($key)) return isset($_SESSION[$this->session_prefix]) ? $_SESSION[$this->session_prefix] : array();
        if (is_null($val)) {
            return isset($_SESSION[$this->session_prefix][$key]) ? $_SESSION[$this->session_prefix][$key] : null;
        }
        if ($val !== false) $_SESSION[$this->session_prefix][$key] = $val; else unset($_SESSION[$this->session_prefix][$key]);

        return $val;
    }
    
    public function logout()
    {
	$_SESSION[$this->session_prefix]=array();
    }
    
    public function run($method='get')
    {
	$this->json_return=true;
	
        $part = substr($_SERVER['REQUEST_URI'], 1+strlen(dirname($_SERVER['SCRIPT_NAME'])));
        if ($pos = strpos($part, '?')) $part = substr($part, 0, $pos);
        $part=preg_replace('~/+~','/',$part);
        $parts = explode('/', $part);

	
	$data=array();
	if ($method=='get' || $method=='delete')
	{
	    $data=$_GET;
	}
	else
	{
	    $data=file_get_contents("php://input");
	    
	    if ($data && isset($_SERVER['CONTENT_TYPE']))
	    {
		if (strstr($_SERVER['CONTENT_TYPE'],'json')) $data=json_decode($data,true);
		if (strstr($_SERVER['CONTENT_TYPE'],'form-urlencoded')) parse_str($data,$data);
		if (strstr($_SERVER['CONTENT_TYPE'],'form-data')) parse_str($data,$data);
	    }
	    else
	    {
		$data=$_REQUEST;
	    }
	}
	
	if (is_array($data) && !$this->isAdmin()) foreach($data AS $k=>$v) if ($k[0]=='_') unset($data[$k]);

	if (!strlen($parts[0])) $parts[0] = 'index';
	$controller_name=$parts[0];

        
	$id=0;

	if (isset($parts[1]) && $parts[1]+0>0) $id=$parts[1]+0;
	elseif (isset($parts[2])) $id=$parts[2];

	if (!$id && isset($data['id']))
	{
	    $id=$data['id'];
	    unset($data['id']);
	}	
	
	
	$controller_name.='Controller';
	$controller=new $controller_name($id,$data,$parts);
	$controller->init();
	
	$this->system('init');
	
	$controller_method=$method;
	
	if (isset($data['action']) && preg_match('/^[a-z]/i',$data['action']))
	{
	    $controller_method.='_'.strtolower($data['action']);
	}
	elseif (isset($parts[1]) && preg_match('/^[a-z]/i',$parts[1]))
	{
	    $controller_method.='_'.$parts[1];
	}
	
	
	$result=$controller->$controller_method();
	
        $this->result($result);
    }
    
    public function closeConn()
    {
        if (is_object($this->conn)) unset($this->conn);
    }
    
    protected function clear_data(&$data)
    {
	
    }
    
    public function result($result,$error=null,$die=true)
    {
        header("Content-Type: application/json; charset=utf8");
        if (!is_array($result)) $result=array('result'=>$result);
	
        if (!is_null($error))
	{
	    
	    $result['status']=false;
	    ini_set('display_erros','on');
	    $result['error']=Error::e($error);

	}
	$this->clear_data($result);
	$this->system('total');
	unset($this->system['start']);
	$result['x_system']=$this->system;
	if ($die && $this->json_return) die(json_encode($result,JSON_NUMERIC_CHECK));
	if ($die) mydie($result,'Result');
	return $result;
    }



    public function getConfig($index=null)
    {
	if ($index && !isset($this->config[$index])) return false;
	if ($index) return $this->config[$index];
	return $this->config;
    }
    
    
    public function getRoot()
    {
        return $this->root;
    }
    
    
    public function isAdmin()
    {
	if (!isset($this->user['id']) || !$this->user['id']) return false;
	return in_array($this->user['id'],$this->getConfig('admin.ids'));
    }
    
    public function clearRow(&$data,$hidden_fields=false)
    {
	
    }
    
    public function mediaPath($prefix='')
    {
	$path='/tmp';
	if ($prefix) $path.='/'.$prefix;
	return $path;
    }
    
}