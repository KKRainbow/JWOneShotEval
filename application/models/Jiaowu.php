<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午2:29
 */

class Jiaowu extends CI_Model{
    private static $url_test_login = "/login";
    private static $url_test_courses_list = "/xspj/queryPjkc";
    private static $url_test_teachers_list = "/xspj/pjkc";
    public function testHasLoggedIn()
    {
        $ch = $this->connection->getCurlPointer();

        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_test_login);

        $res = Connection::getResponsBody($ch);

        //这个字符串只有在未登录时才会出现
        return !preg_match("/非法登陆/",$res);
    }
    public function __construct()
    {
        parent::__construct();
    }

    //课程数组
    /*
     * Array("form" => 表单，"teachers" => 教师列表)
     */
    private $courses;

    private function _getTeacherOfCourse(&$course)
    {
        if(!isset($course))
        {
            $course["teachers"] = array();
            return;
        }


        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_test_teachers_list);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,[
            "skjs" => $course['form']['SKJS'],
            "kcdm" => $course['form']['KCDM'],
            "jxbh" => $course['form']['JXBH'],
            "sfpj" => $course['form']['SFPJ'],
            "gjz"  => "",
            "gjz2" => ""
        ]);
        $teacher_html = Connection::getResponsBody($ch);

        //<a href="javascript:pjjs('09759','01','02','0','E27D245A','2014-2015-2-E27D245A-1');" >
        if(!preg_match_all(
            "/<a href=\"javascript:pjjs\\(([^;]*)\\);\"/",
            $teacher_html,$matches))
        {
            echo "该课程没教师？逗我";
            exit();
        }

        //从course中提炼出教师名字，加到我们这个数组中，方便显示
        $names = explode(",",$course["form"]["SKJS"]);
        $index = 0;
        foreach($matches[1] as $params)
        {
            $t = array();
            $params = explode(',',$params);
            //参数本身室友单引号，我们需要去掉它
            array_walk($params,function(&$item){
                trim($item,"'");
            });

            //function pjjs(zgh,yxfldm,fldm,sfpj,kcdm,jxbh){
            list(
                $t['zgh'],
                $t['yxfldm'],
                $t['fldm'],
                $t['sfpj'],
                $t['kcdm'],
                $t['jxbh'],
                ) = $params;

            //自定义字段
            $t['name'] = $names[$index];

            $course['teachers'][] = $t;
        }
    }
    public function getCourseArray()
    {
        if($this->courses != null)return $this->courses;

        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_test_courses_list);
        $course_html = Connection::getResponsBody($ch);

        //一个都没有匹配到，不会吧，很可能是错误了，直接展示错误截面把= =
        $matches = null;
        //<input type="button" class="btn2"
        // idvalue="{'SKJS':'08297@张东凤','KCDM':'F27D3130','JXBH':'2014-2015-2-F27D3130-1','SFPJ':'0'}"
        // onclick="javascript:pjkc(this);" value="评价"/>
        if(!preg_match_all("/<input type=\"button\" class=\"btn2\" idvalue=\"([^\"]*)\" onclick/",
            $course_html,$matches))
        {
            echo "没有可以评价的课程";
        }

        //匹配到的就是json字符串。
        foreach($matches[1] as $json_str)
        {
            //php中json必须为单引号
            $j = preg_replace("/'/","\"",$json_str);
            //转换成关联数组而非stdClass
            $json = json_decode($j,true);
            if(!$json)
            {
                show_error("解码json错误");
            }
            $course = array(
                "form" => $json
            );
            $this->_getTeacherOfCourse($course);
            $this->courses[]  = $course;
        }
        return $this->courses;
    }
}