<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午2:29
 */

class Jiaowu extends CI_Model{
    private static $url_test_login = "/login";
    private static $url_test_courses_list = "/queryPjkc";
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

    private function _getTeacherOfCourse($course)
    {

    }
    public function getCourseArray()
    {
        if($this->courses != null)return $this->courses;

        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_test_courses_list);
        $course_html = Connection::getResponsBody($ch);
    }
}