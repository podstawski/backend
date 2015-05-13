<?php


class Controller {
    protected $id,$data,$name,$parts;
    protected $_appengine=false;
    protected $error_trap=null;
    
    public function __construct($id=0,$data=[],$parts=[])
    {
        $this->id=$id;
        $this->data=$data;
        $this->parts=$parts;
        $this->name = str_ireplace('controller','',get_class($this));
        $this->_appengine=Bootstrap::$main->appengine;
    }
    
    
    public function __call($name,$args)
    {
        Bootstrap::$main->result(array('name'=>$this->name,'method'=>$name),2);
    }
    
    public function init()
    {
    }
    
    public function get()
    {
	return $this->status([]);
        
    }

    public function post()
    {
        
    }
    public function delete()
    {
        
    }

    public function put()
    {
        
    }

    protected function error($id=0,$ctx=null)
    {
        if (!is_null($ctx) && !is_array($ctx)) $ctx=['ctx'=>$ctx];
        if (is_null($this->error_trap)) Bootstrap::$main->result($ctx,$id);
        $this->error_trap=Bootstrap::$main->result($ctx,$id,false);
    }

    protected function status($data=null,$status=true,$name=null)
    {
        if (is_null($name)) $name=$this->name;
        $ret=array('status'=>$status);
        if ($data || is_array($data))
        {
            $ret[$name] = $data;
        }
        return $ret;
    }
    
    public function options()
    {
        die();
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    protected function _getParam($name, $defaultValue = null)
    {
        $value = @$_REQUEST[$name];
        if (($value === null || $value === '') && ($defaultValue !== null)) {
            $value = $defaultValue;
        }
        return $value;
    }
    
    protected function redirect($redirect)
    {
        if ($redirect=='__close') die('<script>window.close();</script>');
        Header('Location: '.$redirect);
        die();
    }
    
    protected function requiresLogin($admin=false)
    {
        if (!Bootstrap::$main->user) $this->error(15);
        if ($admin && !Bootstrap::$main->isAdmin()) $this->error(19);
    }
    
    protected function urlencode(array $data)
    {
	$d='';
	foreach($data AS $k=>$v) {
	    if ($d) $d.='&';
	    $d.=urlencode($k).'='.urlencode($v);
	}
	return $d;	
    }    
    
    protected function req($url,$data=null,$method='POST',$header="Content-Type: application/x-www-form-urlencoded")
    {
	if (is_null($data)) return file_get_contents($url);
	
	if (is_array($data)) $data=$this->urlencode($data);

	$context = array("http" => array(
			    "method" => $method,
			    "header" => $header,
			    "content" => $data,
			    "follow_location" =>0
			 ),
			 "ssl" => array (
			    "verify_peer" => "0",
			    'ciphers'=>'AES256-SHA'
			 )
	);
	$context['https']=$context['http'];
	
	//mydie($context);
	$ctx = stream_context_create($context);

	return file_get_contents($url, false, $ctx);
    }
    
    
    protected function data($i)
    {
        if (isset($this->data[$i])) return $this->data[$i];
        return null;
    }
    
    

    
    protected function nav_array($search_limit)
    {
        $opt=array();
	$opt['limit']=isset($this->data['limit']) && $this->data['limit']+0>0 ? $this->data['limit'] : $search_limit;
	$opt['offset']=isset($this->data['offset']) && $this->data['offset']+0>0 ? $this->data['offset'] : 0;
        return $opt;
    }
    

    
    protected function check_input($arrays=[],$data=null)
    {
        if (is_null($data)) $data=$this->data;
        

        if (is_array($data)) foreach ($data AS $k=>$v) {
            if (!is_array($v)) continue;
            if (!is_array($arrays) || ( !isset($arrays[$k]) && !isset($arrays['*'])) ) {
                $this->error(65,$k);
            }
            if (!isset($arrays[$k])) $this->check_input($arrays['*'],$v);
            else $this->check_input($arrays[$k],$v);
            
        }
    }
    

    
}
