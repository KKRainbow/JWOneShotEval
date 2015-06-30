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

    .green
    {
        color:green;
    }
    .green:before
    {

        content:"已评价";
    }
    .red
    {
        color:red;
    }
    .red:before
    {
        content:"未评价";
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
        var color = sfpj == "0" ?
            "red"
            :
            "green";
        return "<span id='sfpj_" + id + "' class='" + color + "'></span>";
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
                $(document).queue("initGrid",
                function()
                {
                    var i = $(document).queue("initGrid").length - 1;
                    $("#grid").datagrid(
                        'appendRow',rows[rows.length - i -1]
                    );
                    setTimeout(function()
                    {
                        $(document).dequeue("initGrid");
                    },90)
                });
            }
        }
//        $("#grid").datagrid(
//            'hideColumn', "code"
//        );

        $(document).queue("initGrid",initStyles);
        $(document).dequeue("initGrid");
    }
    $(function(){
        initGrid(json);
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
    function saveTeachers(kcdm,imme,index)
    {
        if(typeof index == 'undefined')index = 0;
        var course = getCourseByKCDM(kcdm);
        if(!course)return;

        if(index >= course.teachers.length)
        {
            //提交表单
            //收尾工作。
            if(imme)
            {
                $.messager.show({
                    'title':"提示",
                    'msg' : "正在保存中"
                });
                console.log(imme);
                $(document).dequeue("ajaxRequests");
            }
            return;
        }

        var teacher = course.teachers[index];
        var num = teacher.skjs.split('@')[0];

        var pj = [];
        <?php foreach(range(1,6) as $i):?>
        pj.push($("input[name=pj_" + num + course.form.KCDM + <?=$i?> + "]").val());
        <?php endforeach?>
        var py =$("#py_" + num + course.form.KCDM).val();

        console.log(pj);

        //检查有无未填项
        var complete = true;
        for(var i = 0;i<6 ;i ++ )
        {
            if(!/[1-3]/.test(pj[i]))
            {
                complete = false;
                break;
            }
        }

        //班号
        var number = num;
        var pingjia = pj.join("");
        var url = "/index.php/evaluate/evalTeacher/" + kcdm + "/" + num + "/" + pingjia + "/" + py;
        if(complete)
        {
            $(document).queue(
                "ajaxRequests",
                function()
                {
                    var u = url.toString();
                    console.log(u);
                    $.get(
                        u,
                        function(data,status)
                        {
                            console.log(data);
                            if(data == "1")
                            {
                                //更新表格
                                $('#sfpj_' + number + kcdm).attr("class","green");
                            }
                            $(document).dequeue("ajaxRequests");
                        }
                    );
                }
            );
        }
        saveTeachers(kcdm,imme,index + 1);
//      initStyles();
    }
    function saveCourse(kcdm,imme)
    {
        if(typeof index == 'undefined')index = 0;
        var course = getCourseByKCDM(kcdm);
        if(!course)return;

        var url = "/index.php/evaluate/saveCourse/" + course.form.KCDM;
        $(document).queue(
          "ajaxRequests",
            function()
            {
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
                        $(document).dequeue("ajaxRequests");
                    }
                );
            }
        );
        if(imme)
        {
            $.messager.show({
                'title':"提示",
                'msg' : "正在保存中"
            });

            $(document).dequeue("ajaxRequests");
        }
    }
    function saveAll()
    {
        $.messager.confirm(
            '确定',
            "提交？",
            function(b)
            {
                if(!b)return;
                for(var i in json)
                {
                    var dm = json[i].form.KCDM;
                    saveTeachers(dm,false);
                    saveCourse(dm,false);
                }
                console.log($(document).queue("ajaxRequests").length);
                $(document).dequeue("ajaxRequests");
            }
        );
    }
    function pjsy()
    {
        $.messager.prompt('请输入', '评价等级(A,B,C,a,b,c)：', function(r){
            if (r){
                r = r.toLowerCase();
                if(!/[a-c]/.test(r))
                {
                    r = 'a';
                }
                r = r.charCodeAt(0) - 96;
                console.log(r);
                $(".pjselect").combobox('select',r);
                $("textarea[class=pingyu]").val("您的课让人受益匪浅！");
            }
        });
    }
    function yjpj()
    {
        $.messager.confirm(
            '确定',
            "将会评价为5A 1B，因为教务网不允许所有评价完全相同，请确定这是您想要的评价？",
            function(b)
            {
                if(!b)return;
                pjsy();
                saveAll();
            }
        );
    }
</script>
<body>

<div class="easyui-layout" id="main-panel" style="width:100%;height:600px;margin-left:auto;margin-right: auto;margin-top:20px;">
    <div data-options="region:'west',split:true" title="工具" style="width:180px;">
        <div class="easyui-layout" id="main-panel" style="padding: 0px 0 20px 0">
            <a href="javascript:yjpj();" class="easyui-linkbutton" style="width:180px;height: 80px;">
                <span style="color: red;font-size:20px;">一键评价</span>
            </a>
        </div>
            <div class="easyui-layout" id="main-panel" style="padding: 00px 0 20px 0">
            <button onclick="pjsy()" class="easyui-linkbutton" style="width:180px;">填充所有评价</button>
            <button onclick="saveAll()" class="easyui-linkbutton" style="width:180px;">全部提交(无法撤销）</button>
            <a href="/index.php/login/logout" class="easyui-linkbutton" style="width:180px;">退出登录</a>
        </div>
    </div>
    <div id="courses" data-options="region:'center',title:'评价列表'">
        <table id="grid" class="easyui-datagrid" title="教师列表"
               style="height: 560px"
               data-options="
            fitColumns:true,
            singleSelect:true,
            groupField : 'cname',
            idField : 'code',
            view : groupview,
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
            '<a href=\'javascript:\' class=\'course_btn\''+disable+' onclick=\'saveTeachers(&quot;' + kcdm +'&quot;,true)\'>保存</a>'
+           '<a href=\'javascript:\' class=\'submit_btn\''+disable+' onclick=\'saveCourse(&quot;' + kcdm + '&quot;,true)\'>提交（无法撤销）</a>'

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
</div>
</body>
</html>
