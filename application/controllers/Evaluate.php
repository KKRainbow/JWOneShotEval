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
     * @param $kcdm string 课程代码
     * @param $number int 教师编号
     * @param $pj string 评价列表
     * @param $py string 评语
     * @return bool 评价是否成功
     */
    public function evalTeacher($kcdm,$number,$pj = "",$py = "")
    {
        $course = $this->jiaowu->getCourseArray();
        array_walk($course,function(&$item) use ($number,$pj,$py,$kcdm)
        {
            if($item['form']['KCDM'] != $kcdm)return;
            foreach($item['teachers'] as &$t)
            {
                if($t['zgh'] == $number)
                {
                    $this->jiaowu->evaluateTeacher($t,array_merge(str_split($pj,1),[$py]));
                    echo $this->jiaowu->saveTeacher($t);
                    exit();
                }
            }
        });
        exit("0");
    }

    /**
     * @param $kcdm string 课程代码
     */
    public function saveCourse($kcdm)
    {
        $course = $this->jiaowu->getCourseArray();
        array_walk($course,function(&$item) use ($kcdm)
        {
            if($item['form']['KCDM'] == $kcdm)
            {
                echo $this->jiaowu->saveCourse($item);
                exit();
            }
        });
        exit("0");
    }
}