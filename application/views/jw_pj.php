<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/js/theme/smothness.css"/>
    <link rel="stylesheet" href="/static/css/common.css"/>
    <script src="/static/js/external/jquery/jquery.js"></script>
    <script src="/static/js/jquery-ui.js"></script>
    <title></title>
</head>
<style>
    #courses
    {
        margin: 50px;
    }
    .teacher_table
    {
        width: 100%;
        margin-top: 50px;
        margin-bottom: 50px;
    }
</style>
<script>
    var json = <?=$course?>;
    function buildTeacherItem(teacher)
    {
        var pj = (teacher.sfpj == "1"?"(已评价)":"（未评价)");
        var html =
            "<tr>" +
            "<td>"  +
            teacher.skjs.split('@')[1] + pj +
            "</td>"  +

            "<td>"  +
            teacher.pj[0] +
            "</td>"  +

            "<td>"  +
            teacher.pj[1] +
            "</td>"  +

            "<td>"  +
            teacher.pj[2] +
            "</td>"  +

            "<td>"  +
            teacher.pj[3] +
            "</td>"  +

            "<td>"  +
            teacher.pj[4] +
            "</td>"  +

            "<td>"  +
            teacher.pj[5] +
            "</td>"  +

            "<td>"  +
            teacher.pj[6] +
            "</td>"  +

            "</tr>";
        return html;
    }
    function fillTeachers(ui)
    {
        var newPanel = ui.newPanel;
        var newHeader = ui.newHeader;
        if(typeof newPanel.get(0).loaded == 'undefined' || newPanel.get(0).loaded == false)
        {
            //加载数据
            var kcdm = newHeader.attr("id");
            $.get(
                "evaluate/get_teachers/" +  kcdm,
                function(data,status)
                {
                    if(data != "0")
                    {
                        //删除原来的数据
                        newPanel.find('table').find('tbody').children().remove();
                        //返回的是Json string
                        data = eval(data);
                        var cdom = $("#" + kcdm);
                        var course = cdom.attr("course");
                        course.teachers = data;
                        //构造教师列表

                        var teacherPanel = $("#div_" + kcdm).find("tbody").get(0);
                        for(index in data)
                        {
                            var h = buildTeacherItem(data[index]);
                            console.log(data[index]);
                            $(teacherPanel).append(
                                h
                            );
                        }

                        newPanel.get(0).loaded = true;
                        newPanel.find('table').show();
                        newPanel.find("div").remove();
                    }
                }
            ).error(
                function()
                {

                }
            );
        }
    }
    $(function()
    {
        var courseTable = $("#courses");
        for(var c in json)
        {
            var form = json[c].form;
            console.log(form);

            courseTable.append("<h3 id=\"" + form.KCDM + "\">" + form.name + "</h3>")
                .append("<div id=\"div_" + form.KCDM + "\">"
                + "</div>");

            $("#"+ form.KCDM).attr("course",c);
            $("#div_"+ form.KCDM).html(
                "<table class=\"teacher_table\" style='display: none'>" +
                "<thead>" +
                "<tr>" +
                "<td>" + "教师姓名" + "</td>" +
                "<td>" + "评价1" + "</td>" +
                "<td>" + "评价2" + "</td>" +
                "<td>" + "评价3" + "</td>" +
                "<td>" + "评价4" + "</td>" +
                "<td>" + "评价5" + "</td>" +
                "<td>" + "评价6" + "</td>" +
                "<td width='260px'>" + "评语" + "</td>" +
                "</tr>" +
                "</thead>" +
                "<tbody>" +
                "</tbody>" +
                "</table>" +
                "<div>" +
                    "请稍后，正在加载" +
                "</div>"
            );
        }

        courseTable.accordion(
            {
                heightStyle : "content",
                event: "click hoverintent",
                activate : function(event,ui)
                {
                    fillTeachers(ui);
                },
                create :function(event,ui)
                {
                    ui.newPanel = ui.panel;
                    ui.newHeader = ui.header;
                    fillTeachers(ui);
                }
            }
        );
    });

    $.event.special.hoverintent = {
        setup: function() {
            $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
        },
        teardown: function() {
            $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
        },
        handler: function( event ) {
            var currentX, currentY, timeout,
                args = arguments,
                target = $( event.target ),
                previousX = event.pageX,
                previousY = event.pageY;

            function track( event ) {
                currentX = event.pageX;
                currentY = event.pageY;
            }

            function clear() {
                target
                    .unbind( "mousemove", track )
                    .unbind( "mouseout", clear );
                clearTimeout( timeout );
            }

            function handler() {
                var prop,
                    orig = event;

                if ( ( Math.abs( previousX - currentX ) +
                    Math.abs( previousY - currentY ) ) < 7 ) {
                    clear();

                    event = $.Event( "hoverintent" );
                    for ( prop in orig ) {
                        if ( !( prop in event ) ) {
                            event[ prop ] = orig[ prop ];
                        }
                    }
                    // Prevent accessing the original event since the new event
                    // is fired asynchronously and the old event is no longer
                    // usable (#6028)
                    delete event.originalEvent;

                    target.trigger( event );
                } else {
                    previousX = currentX;
                    previousY = currentY;
                    timeout = setTimeout( handler, 100 );
                }
            }

            timeout = setTimeout( handler, 100 );
            target.bind({
                mousemove: track,
                mouseout: clear
            });
        }
    };
</script>
<body>
<a href="login/logout">退出登录</a>
<a href="evaluate/evalall">一键评价</a>
<div id="courses">

</div>
<div style="height: 500px"></div>
</body>
</html>