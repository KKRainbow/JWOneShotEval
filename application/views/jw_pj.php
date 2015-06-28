<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/css/common.css"/>
    <link rel="stylesheet" type="text/css" href="/static/js/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/js/themes/icon.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="http://www.jeasyui.com/easyui/datagrid-groupview.js"></script>
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
    .course_btn
    {
        margin-right: 5px;
    }
</style>
<script>
    var json = <?=$course?>;
    function buildTeacherItem(teacher,course)
    {
        var s = teacher.skjs.split('@');
        var row =
        {
            "sfpj" : teacher.sfpj == "1" ? "Y":"N",
            "cname" : course.form.name,
            "code" : s[0],
            "tname" : s[1],
            "pj1" : teacher.pj[0],
            "pj2" : teacher.pj[1],
            "pj3" : teacher.pj[2],
            "pj4" : teacher.pj[3],
            "pj5" : teacher.pj[4],
            "pj6" : teacher.pj[5],
            "py" : teacher.pj[6],
            "teacher" : teacher,
            "course" : course
        }
        ;
        console.log(row);
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
    $(function(){
       for(var index in json)
       {
           var course = json[index];
           for(var i in course.teachers)
           {
               var item = buildTeacherItem(course.teachers[i],course);
               $("#grid").datagrid(
                   'appendRow',item
               );
           }
       }

        $(".course_btn").linkbutton(
            {
                iconCls : 'icon-save'
            }
        );

        var data = $("#grid").datagrid('getData');
        console.log(data.rows[1].sfpj = 'a');
        $("#grid").datagrid('loadData',data.rows);
    });
</script>
<body>
<a href="/index.php/login/logout">退出登录</a>
<a href="/index.php/evaluate/evalall" class="easyui-linkbutton">一键评价</a>

<div id="courses">
    <table id="grid" class="easyui-datagrid" title="教师列表"
           style="height: 800px"
        data-options="
            fitColumns:true,
            singleSelect:true,
            groupField : 'cname',
            idField : 'code',
            view : groupview,
            groupFormatter:function(value,rows){
            console.log(rows);
            var pj = rows[0].course.form.SFPJ == '1'?
            '<font color=\'red\'>课程已评价    </font>'
            :
            '';
            var disable = rows[0].course.form.SFPJ == '1'?
                        ' style=\'display : none;\' ' : '';
            var btn =
            '<a href=\'javascript:\' class=\'course_btn\''+disable+'>保存课程评价（无法撤销）</a>';
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
            <th data-options="field:'cname'">课程名称</th>
            <!--上课教师-->
            <th data-options="field:'tname'">上课教师</th>
            <!--教师编号-->
            <th data-options="field:'code'">教师编号</th>
            <?php foreach(range(1,6) as $i):?>
            <th data-options="field:'pj<?=$i?>',align:'left'
                ,editor:{type:'checkbox',options:{}}
            ">评价项目<?=$i?></th>
            <?php endforeach?>
            <th data-options="field:'py',align:'left'">评价项目</th>
        </tr>
        </thead>
    </table>
</div>
</body>
</html>