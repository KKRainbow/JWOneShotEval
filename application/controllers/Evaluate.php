<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午3:18
 */

class Evaluate extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("jiaowu");
    }
    public function index()
    {
        if(!$this->jiaowu->testHasLoggedIn())
        {
            echo "您还未登录";
            exit();
        }
        $jw = $this->jiaowu;
        $course = $jw->getCourseArray();

        $t = &$course[3]['teachers'][0];
        $jw->evaluateTeacher($t);
        $res = $jw->saveTeacher($t);
        echo $res;
    }
}