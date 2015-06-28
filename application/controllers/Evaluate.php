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
            redirect("login");
            exit();
        }
    }
    public function index()
    {
        $this->_test();
        //获得course，并转化为json
        $jw = $this->jiaowu;
        $data['course'] = json_encode($jw->getCourseArray());

        $this->load->view("jw_pj",$data);
    }

    /**
     * @param $kcdm 课程代码
     */
    public function get_teachers($kcdm)
    {
        $c = $this->jiaowu->getCourseArray();
        array_walk($c,function(&$item) use($kcdm)
        {
               if($item['form']['KCDM'] == $kcdm)
               {
                   if($this->jiaowu->getTeacherOfCourse($item))
                   {
                       echo json_encode($item['teachers']);
                       exit();
                   }
                   exit("0");
               }
        });
        echo false;
    }
    public function evalall()
    {
        $this->_test();

        $jw = $this->jiaowu;
        $course = $jw->getCourseArray();

        foreach($course as &$c)
        {
            $jw->getTeacherOfCourse($c);
            foreach($c['teachers'] as &$t)
            {
                $jw->evaluateTeacher($t);
                $res = $jw->saveTeacher($t);
            }
            $res = $jw->saveCourse($c);
        }
    }
}