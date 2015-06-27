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
    private function _test()
    {
        if(!$this->jiaowu->testHasLoggedIn())
        {
            echo "您还未登录";
            exit();
        }
    }
    public function index()
    {
        $this->_test();


    }

    public function evalall()
    {
        $this->_test();

        $jw = $this->jiaowu;
        $course = $jw->getCourseArray();

        foreach($course as &$c)
        {
            foreach($c['teachers'] as &$t)
            {
                $jw->evaluateTeacher($t);
                $res = $jw->saveTeacher($t);
            }
            $res = $jw->saveCourse($c);
        }
    }
}