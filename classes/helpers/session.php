<?php
define('SECRET','>hxJ%.v>`iVok[Hc72oV7@%z-CN(]RF^-E&jE,+L6vnB[@;j*.}{OA8/[pU]|n?&');
define('HASH',md5(dirname(__FILE__) . SECRET) );
define('KEY','cp_sess_' . HASH);

//http://www.cs.wcupa.edu/~rkline/php/sessions.html
class Session {

    function __construct(){
        session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
        session_set_cookie_params(86400,"/","janeullah.com",false,true);
        session_name('CoursePicker');
        session_start();        
    }
    
    //__set: $_SESSION[KEY]['var'] = 17
    public function __set($name,$value) {
        $_SESSION[KEY][$name] = $value;
    }
    
    //__get: $value =& $_SESSION[KEY]['var'];
    public function & __get($name) {
        return $_SESSION[KEY][$name];
    }

    // __toString
    public function __toString() {
        $sess = $_SESSION[KEY];
        foreach ($sess as $key => $value) {
            if (!isset($value)) unset($sess[$key]); 
        }
        return print_r($sess,true);
    }
        
    //__isset: if (isset($_SESSION[KEY]['var'])) 
    public function __isset($name) {
        return isset($_SESSION[KEY][$name]);
    }
    
    // __unset: unset($_SESSION[KEY]['var']);
    public function __unset($name) {
        unset($_SESSION[KEY][$name]);
    }
    
    public function unsetAll() {
        unset($_SESSION[KEY]);
    }
}
