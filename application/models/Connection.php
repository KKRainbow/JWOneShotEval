<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午12:04
 */

define("COOKIE_DIR","/tmp/jwcookie" . DIRECTORY_SEPARATOR);
define("ROOT","http://10.200.21.61:7001/ieas2.1");//统一认证的HOST
define("CURL_TIMEOUT" , 10);
class Connection extends CI_Model{
    private $cookie;
    private $cacheFile;
    private $cache = null;
    private $curl = null;
    public $login = null;
    public function _setFields()
    {
        //不会出现的情况
        if(!isset($_SESSION['hash']))show_error("Coding Error");
        $hash = $_SESSION['hash'];
        //检查cookie保存目录的权限
        @mkdir(COOKIE_DIR,0744,true);
        if(!is_dir(COOKIE_DIR) || !is_writable(COOKIE_DIR))
        {
            show_error("COOKIE目录无法写入！！！",503);
        }

        $this->cookie = COOKIE_DIR  . $hash;
        $this->cacheFile = COOKIE_DIR  . $hash . ".cache";
        //检查Cookie是否已经存在
        //载入Cache文件
        $filesToCheck = [$this->cookie,$this->cacheFile];
        foreach($filesToCheck as $file)
        {
            if(!file_exists($file) )
            {
                file_put_contents($file,"");
            }
            if(!file_exists($file) || !is_writable($file))
            {
                show_error("无法写入文件" . $file,503);
            }
        }

        //载入cache
        $this->cache = json_decode(file_get_contents($this->cacheFile),true);
        //看是不是第一次创建的cache
        if(!isset($this->cache['time']))
        {
            $this->cache['time'] = time();
        }

        //设置入口地址,目前只使用一个入口
        if(!isset($_SESSION['root']))
        {
            $_SESSION['root'] = ROOT;
        }

    }
    //返回curl指针
    public function getCurlPointer()
    {
        if($this->curl)return $this->curl;
        $this->curl = curl_init();

        $header[] = "Accept-Language: zh-CN,en;q=0.5";
        curl_setopt_array($this->curl,
            array(
                CURLOPT_COOKIEFILE => $this->cookie,
                CURLOPT_COOKIEJAR => $this->cookie,
                CURLOPT_TIMEOUT => CURL_TIMEOUT,
                CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)",
                CURLOPT_HEADER => $header,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true //禁止输出，保存到变量
            ));
        return $this->curl;
    }
    public function __construct()
    {
        //注意必须设置Session为Autoload
        if(!isset($_SESSION['hash']) || !$_SESSION['hash'])
        {
            //作为整个会话的ID
            $_SESSION['hash'] = md5(time()) . rand() . rand();
        }
        $this->_setFields();
        $this->getCurlPointer();
    }

    public static function getResponsBody($ch)
    {
        $res = curl_exec($ch);
        if(!$res || curl_getinfo($ch,CURLINFO_HTTP_CODE) == 404)
        {
            show_error("连接教务系统失败",503);
        }
        //
        $res = strval($res);
        $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
        return substr($res,$header_size);
    }

    public function __destruct()
    {
        curl_close($this->curl);
        file_put_contents($this->cacheFile,json_encode($this->cache));
    }

    public function destroySession()
    {
        if(isset($_SESSION['hash']))
        {
            //防止通配符
            if(preg_match("/[0-9a-fA-F]{32}/",$_SESSION['hash']))
            {
                @unlink(COOKIE_DIR . $_SESSION['hash']);
            }
        }
    }

    public function loadCache($key)
    {
        if(!isset($this->cache['data'][$key]))
            return null;
        return $this->cache['data'][$key];
    }
    public function setCache($key,$data)
    {
        $this->cache['data'][$key] = $data;
    }

    public function getCookieContent()
    {
        return file_get_contents(COOKIE_DIR . $_SESSION['hash']);
    }
}