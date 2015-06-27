<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-18
 * Time: 上午11:05
 */

//每个Login的目的都是为了在Cooike中填入正确的内容。
class Normallogin extends CI_Model{
    private static $url_login_post = "/login";
    private static $url_login_captcha = "/captchaImage";
    private static $url_check_code = "/checkCode";

    public function __construct()
    {

        parent::__construct();
    }
    public function getCaptchaImage()
    {
        $se = $this->connection;
        /**
         * @var Connection $se
         */
        $cp = $se->getCurlPointer();

        curl_setopt($cp,CURLOPT_URL,$_SESSION['root'] . self::$url_login_captcha);

        $res = Connection::getResponsBody($cp);

        return $res;
    }

    private function _checkCode($code)
    {
        $ch = $this->connection->getCurlPointer();

        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_check_code);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,[
            "code" => $code
        ]);

        $res = Connection::getResponsBody($ch);

        return $res === "true";
    }
    public function loginPost($username,$password,$code)
    {
        if(!$this->_checkCode($code))
        {
            return "验证码错误";
        }

        $ch = $this->connection->getCurlPointer();

        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_login_post);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,[
            "usercode" => $username,
            "password" => $password,
            "usertype" => "xj",
            "code" => $code
        ]);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false); //防止收到302后跳转

        if(!curl_exec($ch))
        {
            show_error("服务器错误",503);
        }

        $http_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        //根据http_status来判断，如果302跳转说明登录失败
        if($http_status != 200)
        {
            return "用户名或密码错误";
        }
        else
        {
            return true;
        }
    }
}