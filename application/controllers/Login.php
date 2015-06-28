<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */

    //0 => 教务入口 1 => 统一认证
    private function _getLoginModel($which)
    {
        if (!in_array($which, [0, 1])) $which = 0;

        if ($which == 0) {
            $this->load->model("normallogin", "login");
        } else {
            $this->load->model("unitedlogin", "login");
        }
    }

    public function index()
    {
        $this->load->model("jiaowu");
        if($this->jiaowu->testHasLoggedIn())
        {
            //已经登录
            //TODO 跳转到管理首页
            echo "您已经登录";
            redirect("evaluate");
            exit();
        }
        $this->load->view('jw_login');
    }

    public function captcha($which = 0)
    {
        $this->_getLoginModel($which);
        header("Content-Type:\"image/jpeg\"");
        header("Pragma:\"no-cach\"");

        /**
         * @var Normallogin $this ->login
         */
        echo $this->login->GetCaptchaImage();
    }

    /**
     * @return string 成功则什么都不返回，否则返回除错原因
     */
    public function loginpost($which = 0)
    {
        //检查三个域
        if(
            !isset($_POST['username'])||
            !isset($_POST['password'])||
            !isset($_POST['code'])
        )
        {
            show_error("错误的请求");
        }

        $this->_getLoginModel($which);
        $res = $this->login->loginPost($_POST['username'],
            $_POST['password'],
            $_POST['code']);

        echo $res;
    }

    public function logout()
    {
        $this->connection->destroySession();
        redirect("login");
    }
}
