<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午1:45
 */

class Unitedlogin extends CI_Model{

    private static $url_info =
        "https://sso.buaa.edu.cn/login?service=http%3A%2F%2F10.200.21.61%3A7001%2Fieas2.1%2Fwelcome";
    private static $url_captcha =
        "https://sso.buaa.edu.cn/ImageCodeServlet";
    public function __contruct()
    {
        parent::__construct();
    }
    public function getCaptchaImage()
    {
        $ch = $this->connection->getCurlPointer();

        curl_setopt($ch,CURLOPT_URL,self::$url_captcha . "?id=" . time());

        return Connection::getResponsBody($ch);
    }

    public function loginPost($username,$password,$code)
    {
        $this->connection->destroySession();
        $ch = $this->connection->getCurlPointer();

        $url = "https://sso.buaa.edu.cn/login?service=".
            urlencode("http://10.200.21.61:7001/ieas2.1/welcome");
        curl_setopt($ch,CURLOPT_URL,$url);


//         <input type="hidden" name="lt" value="LT-104593-7GD4QB4dWid1k4cvb621ekccv1EsR4" />
//			<input type="hidden" name="execution" value="e1s2" />
//			<input type="hidden" name="_eventId" value="submit" />
        //重点在于获取前两个的值
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);
        $html = curl_exec($ch);

        $lt = null;
        preg_match("/<input type=\"hidden\" name=\"lt\" value=\"([^\"]*)\"/",$html,$lt);
        $execution = null;
        preg_match("/<input type=\"hidden\" name=\"execution\" value=\"([^\"]*)\"/",$html,$execution);

        if($lt == null || $execution == null)
        {
            return "获取登录信息失败";
        }


        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_REFERER,$url);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);
        curl_setopt($ch,CURLOPT_POST,true);
        $fields = [
            "lt" => $lt[1],
            "execution" => $execution[1],
            "_eventId" => "submit",
            "submit" => urlencode("登录"),
            "username" => $username,
            "password" => $password,
            "code" => $code
        ];
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($fields));
        curl_setopt($ch,CURLOPT_TIMEOUT,60); //这一步进行的相当慢
        //忽略中断，进行到这步说明用户名密码是正确的。
        ignore_user_abort(1);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        $res = curl_exec($ch);


        if(!preg_match("/教务管理系统/",$res))
        {
            return "用户名或密码错误";
        }
        else
        {
            return true;
        }
    }
}