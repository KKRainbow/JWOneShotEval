<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/css/common.css"/>
    <link rel="stylesheet" type="text/css" href="/static/js/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/js/themes/icon.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="/static/js/easyui/datagrid-groupview.js"></script>
    <title></title>
</head>
<style>
    #courses
    {
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }
    .teacher_table
    {
        width: 100%;
        margin-top: 50px;
        margin-bottom: 50px;
    }
    .course_btn,.submit_btn
    {
        margin-right: 5px;
    }
</style>
<script>
    var json = <?=$course?>;
    function getPjElement(pj,id)
    {
        //临时元素，方便获取innerHTML
        var div = $("<div></div>");
        var tmp = $("<select style='width:50px;' class='pjselect' name='pj_"+ id + "'></select>");

        function attrs(value)
        {
            var tmp = "type='radio' class='pj' value='" + value + "' ";
            if(value == pj)
            {
                return tmp + "selected='selected'";
            }
            else
            {
                return  tmp;
            }
        }

        tmp.append("<option " + attrs(0) + ">空</option>");
        tmp.append("<option " + attrs(1) + ">A</option>");
        tmp.append("<option " + attrs(2) + ">B</option>");
        tmp.append("<option " + attrs(3) + ">C</option>");


        div.append(tmp);

        return div.html();
    }
    function getSfpjElem(sfpj,id)
    {
        var sfpj = sfpj == "1" ?
            "Y"
            :
            "N";
        return "<span id='sfpj_" + id + "'>"+ sfpj +"</span>";
    }
    function buildTeacherItem(teacher,course)
    {
        var s = teacher.skjs.split('@');
        var py = teacher.pj[6];
        var row =
        {
//            select的id格式：sfpj_教师编号 课程代码
            "sfpj" : getSfpjElem(teacher.sfpj,s[0] + course.form.KCDM),
            "cname" : course.form.name,
            "code" : s[0],
            "tname" : s[1],
//            select的id格式：pj_教师编号 课程代码 评价序号
            <?php foreach(range(1,6) as $i):?>
            "pj<?=$i?>" : getPjElement(teacher.pj[<?=$i-1?>],s[0] + course.form.KCDM + <?=$i?>),
            <?php endforeach?>
//            select的id格式：py_教师编号 课程代码
            "py" : "<textarea rows='2' class='pingyu' id='py_"+
                s[0] + course.form.KCDM
            +"' style='width:80px;'>"+py+"</textarea>",
            "teacher" : teacher,
            "course" : course
        }
        ;
//        console.log(row);
        return row;
    }
    String.prototype.repeat = function(count)
    {
        if (count < 1) return '';
        var result = '', pattern = this.valueOf();
        while (count > 1) {
            if (count & 1) result += pattern;
            count >>= 1, pattern += pattern;
        }
        return result + pattern;
    };
    function initStyles()
    {
        $(".course_btn").linkbutton(
            {
                iconCls : 'icon-save'
            }
        );
        $(".submit_btn").linkbutton(
            {
                iconCls : 'icon-add'
            }
        );

        $('.pjselect').combobox(
            {
                editable:false
            }
        );
//        var data = $("#grid").datagrid('getData');
////        console.log(data.rows[1].sfpj = 'a');
//        $("#grid").datagrid('loadData',data.rows);

    }
    function initGrid(json)
    {
        var rows = [];

        for(var index in json)
        {
            var course = json[index];
            for(var i in course.teachers)
            {
                var item = buildTeacherItem(course.teachers[i],course);
                rows.push(item);
                $("#grid").datagrid(
                    'appendRow',item
                );
            }
        }
        $("#grid").datagrid(
            'hideColumn', "code"
        );

        initStyles();
//        $("#grid").datagrid(
//            {
//                data:rows
//            }
//        );

    }
    $(function(){
        $.messager.progress(
            {
                title:"请稍后",
                msg: "正在加载数据"
            }
        );
        setTimeout(function()
        {
            initGrid(json);
            $.messager.progress(
                'close'
            );
        },200);
    });
    function getCourseByKCDM(kcdm)
    {
        for(index in json)
        {
            if(json[index].form.KCDM == kcdm)
            {
                return json[index];
            }
        }
        return false;
    }
    //一个递归过程
    function saveTeachers(kcdm,index)
    {
        if(typeof index == 'undefined')index = 0;
        var course = getCourseByKCDM(kcdm);
        if(!course)return;

        if(index < course.teachers.length)
        {
            var teacher = course.teachers[index];
            var number = teacher.skjs.split('@')[0];
            var oldrowindex = $("#grid").datagrid(
                "getRowIndex",number
            );
            var pj = [];
            <?php foreach(range(1,6) as $i):?>
            pj.push($("input[name=pj_" + number + course.form.KCDM + <?=$i?> + "]").val());
            <?php endforeach?>
            var py =$("#py_" + number + course.form.KCDM).val();

            console.log(pj);

            //检查有无未填项
            var complete = true;
            for(var i = 0;i<6 ;i ++ )
            {
                if(!/[1-3]/.test(pj))
                {
                    complete = false;
                    break;
                }
            }

            //班号
            var kcdm =  course.form.KCDM;
            var number = number;
            var pingjia = pj.join("");
            var pingyu = py;
            var url = "/index.php/evaluate/evalTeacher/" + kcdm + "/" + number + "/" + pingjia + "/" + pingyu;
            console.log(url);
            if(complete)
            {
                $.get(
                    url,
                    function(data,status)
                    {
                        alert("fdsa");
                        var nextIndex = index;
                        console.log(data);
                        if(data == "1")
                        {
                            //更新表格
                            $('#sfpj_' + number + kcdm).html("Y");
                            nextIndex = index + 1;
                        }
                        saveTeachers(kcdm,nextIndex);
                    }
                );
            }
            else
            {
                saveTeachers(kcdm,index+1);
            }

            //提交表单
        }
        else
        {
            //收尾工作。
//            initStyles();
        }
    }
    function saveCourse(kcdm)
    {
        if(typeof index == 'undefined')index = 0;
        var course = getCourseByKCDM(kcdm);
        if(!course)return;

        var url = "/index.php/evaluate/saveCourse/" + course.form.KCDM;
        $.get(
            url,
            function(data,status)
            {
                if(data == "1")
                {
                    $.messager.show(
                        {
                            title:"更新成功",
                            msg:"课程 "+course.form.name + "的评价已经保存，将无法通过教务系统更改"
                        }
                    );

                }
            }
        );
    }
    function pjsy()
    {
        $(".pjselect").combobox('select',1);
        $("textarea[class=pingyu]").val("您的课让人受益匪浅！");
    }
</script>
<body>
<a href="/index.php/login/logout">退出登录</a>
<a href="/index.php/evaluate/evalall" class="easyui-linkbutton">一键评价</a>
<button onclick="pjsy()">评价所有</button>

<div id="courses">
    <table id="grid" class="easyui-datagrid" title="教师列表"
           style="height: 600px"
        data-options="
            fitColumns:true,
            singleSelect:true,
            groupField : 'cname',
            idField : 'code',
            view : groupview,
            rowStyler: function(index,row){
                return 'height:35px;';
            },
            groupFormatter:function(value,rows){
            var pj = rows[0].course.form.SFPJ == '1'?
            '<font color=\'red\'>课程已评价    </font>'
            :
            '';
            var disable = rows[0].course.form.SFPJ == '1'?
                        ' style=\'display : none;\' ' : '';
                        disable = '';
            var kcdm = rows[0].course.form.KCDM;
            var btn =
            '<a href=\'javascript:\' class=\'course_btn\''+disable+' onclick=\'saveTeachers(&quot;' + kcdm +'&quot;)\'>保存</a>'
+           '<a href=\'javascript:\' class=\'submit_btn\''+disable+' onclick=\'saveCourse(&quot;' + kcdm + '&quot;)\'>提交（无法撤销）</a>'

            ;
            var text = pj + value + ' - ' + rows.length + ' Item(s)';
            return btn + text;
            }
        "
        >

        <thead>
        <tr>
            <!--是否评价-->
            <th data-options="field:'sfpj'">已评价</th>
            <!--课程名称-->
            <th data-options="field:'cname'" style="width: 30px">课程名称</th>
            <!--教师编号-->
            <th data-options="field:'code'">教师编号</th>
            <!--上课教师-->
            <th data-options="field:'tname'">上课教师</th>
            <?php foreach(range(1,6) as $i):?>
            <th data-options="field:'pj<?=$i?>',align:'left'
            ">评价项目<?=$i?></th>
            <?php endforeach?>
            <th data-options="field:'py',align:'left',editor:'text'">评语</th>

        </tr>
        </thead>
    </table>
</div>
</body>
</html>