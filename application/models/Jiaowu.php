<?php
/**
 * Created by PhpStorm.
 * User: ssj
 * Date: 15-6-27
 * Time: 下午2:29
 */

class Jiaowu extends CI_Model{
    private static $url_test_login = "/login";
    private static $url_courses_list = "/xspj/queryPjkc";
    private static $url_teachers_list = "/xspj/pjkc";
    private static $url_teacher_pj = "/xspj/pjTeacher";
    private static $url_insert_teacher = "/xspj/insertPj";
    private static $url_update_teacher = "/xspj/updatePj";
    private static $url_save_course = "/xspj/updateTj";
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

    //如果没有被选择的，就填充0
    private function _getTeacherPj(&$teacher)
    {
        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'].self::$url_teacher_pj);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($teacher));
        $html = curl_exec($ch);

        $e = curl_error($ch);
        $matches = null;
        $ys = array_fill(0,7,0);
        $ys[6] = "";

        $teacher['pj'] = $ys;
        if(preg_match_all("/<td>0\\d0(\\d)<\\/td>.*?<option value=\"0(\\d)\" selected/s",
            $html,$matches))
        {
            $len = count($matches[1]);
            foreach(range(0,$len-1) as $i)
            {
                $teacher['pj'][$matches[1][$i] - 1] = $matches[2][$i];
            }
        }
        //获取评语
        $pymatch = null;
        if(
            preg_match("/<td colspan=\"3\"><span>评语:<\\/span><span>([^<]*)<\\/span><\\/td>/s",$html,$pymatch)
        ||
            preg_match("/<td colspan=\"2\">评语:".
                "<textarea id=\"pyxx\" name=\"pyxx\" rows=\"5\" cols=\"100\">([^<]*)<\\/textarea><\\/td>/s"
                ,$html,$pymatch)
        )
        {
            $teacher['pj'][6] = $pymatch[1];
        }
    }

    public function getTeacherOfCourse(&$course)
    {
        if(!isset($course))
        {
            $course["teachers"] = array();
            return false;
        }
        $course["teachers"] = array();


        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_teachers_list);
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
                $item = trim($item,"'");
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
            $t['skjs'] = $names[$index++];
            //获取评价
            $this->_getTeacherPj($t);

            $course['teachers'][] = $t;
        }
        return true;
    }
    public function getCourseArray()
    {
        if($this->courses != null)return $this->courses;

        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_courses_list);
        $course_html = Connection::getResponsBody($ch);

        //一个都没有匹配到，不会吧，很可能是错误了，直接展示错误截面把= =
        $matches = null;
        //<input type="button" class="btn2"
        // idvalue="{'SKJS':'08297@张东凤','KCDM':'F27D3130','JXBH':'2014-2015-2-F27D3130-1','SFPJ':'0'}"
        // onclick="javascript:pjkc(this);" value="评价"/>

//        <tr class="trbgfff">
//    <td>B08D3010</td>
//    <td>经济管理</td>
        if(!preg_match_all("/<input type=\"button\" class=\"btn2\" idvalue=\"([^\"]*)\" onclick/",
            $course_html,$matches))
        {
            echo "没有可以评价的课程";
        }


        //匹配到的就是json字符串。
        foreach($matches[1] as $index => $json_str)
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

            $name = null;
            //课程名称
            if(preg_match("/<td>". $json['KCDM'] ."<\\/td>[^<]*<td>([^<]*)<\\/td>/s"
                ,$course_html,$name))
            {
                $course['form']['name'] = $name[1];
            }
            else
            {
                 $course['form']['name']  = "未命名";
            }

//            $this->getTeacherOfCourse($course);
            $this->courses[]  = $course;
        }
        return $this->courses;
    }

    //评价一个教师
    public function evaluateTeacher(&$teacher,array $pj = array())
    {
        //前6个是选择，ABC分别为123，如果没有填则默认为A
        //需要检查是否有全部相同的,如果全部相等，那么array_unique自然为空

        //评价的表单
        //重置掉，保证后面合并array的时候这个是一维数组
        unset($teacher['pjform']);

        $testUnique = array();
        for($i = 0;$i < 6; $i ++)
        {
            //转换失败就是0，没影响。
            if(!isset($pj[$i]))
            {
                $testUnique[$i] = $pj[$i] = 1;
            }
            else
            {
                $pj[$i] = intval($pj[$i]);
                if(!in_array($pj[$i],[1,2,3]))
                {
                    $pj[$i] = 1;
                }
                $testUnique[$i] = $pj[$i];
            }
        }

        //全部一样，你在逗我？。
        $unique = array_unique($testUnique);
        if(count($unique) == 1)
        {
            $pj[0] = $pj[0] == 1 ? 2: 1;
        }

        //评语
        if(!isset($pj[6]))$pj[6] = "您的课让人受益匪浅！";

        //最后填充好的表单
        $resform = "";
        //zhi:"02"
        //ids:"{"zbdm":"0104","zbfz":"02"}"
        //按上面的格式格式化pj数组
        $resform .= "pyxx=" . urlencode($pj[6]);
        foreach($pj as $i => $v)
        {
            //注意，教务网是从1开始编号的
            $i++;
            if($i > 6)break;//前6个是选项
            $resform .= "&";
            $resform .= "zhi=" . urlencode("0$v");
            $resform .= "&";
            $resform .= "ids=". urlencode("{\"zbdm\":\"010$i\",\"zbfz\":\"0$v\"}");
        }

        //开始填写提交表单
        array_walk($teacher,function(&$item,$key) use (&$resform)
        {
            if(gettype($item) == "array")return;
            $resform .= "&";
            $tmp = urlencode($item);
            $resform .= "$key=$tmp";
        });

        $resform .= "&gjz=&gjz2=";
        $teacher['pjform'] = $resform;
    }


    //保存所有教师，但是不保存课程，因为一旦保存课程就没法改了0 0
    public function saveTeacher($teacher)
    {
        if(!isset($teacher['pjform']))//还没有评价，保存个毛线
        {
            return false;
        }

        $ch = $this->connection->getCurlPointer();
        //已经评价过就是update，否则就是insert
        $url = $teacher["sfpj"] === "1"?
            self::$url_update_teacher:self::$url_insert_teacher;
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . $url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$teacher['pjform']);

        return curl_exec($ch);
    }

    //真怕教务又不在服务端检查教师是否全部评价，所以我们来做这事把= =
    public function saveCourse(&$course)
    {
        if(!isset($course['form']['JXBH']))return false;
        if(count($course['teachers']) == 0)return false;
        foreach($course['teachers'] as &$t)
        {
            if($t['sfpj'] !== "1")
            {
                return false;
            }
        }

        $ch = $this->connection->getCurlPointer();
        curl_setopt($ch,CURLOPT_URL,$_SESSION['root'] . self::$url_save_course
            . "?rwh=" . $course['form']['JXBH']);
        curl_exec($ch);

        return curl_getinfo($ch,CURLINFO_HTTP_CODE) == 200;
    }
}