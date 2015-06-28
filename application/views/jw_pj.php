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
<script>
    var json = <?=$course?>;
    $(function()
    {
        for(var c in json)
        {
            var form = json[c].form;
            console.log(form);

            $("#courses").append("<h3 id=\"" + form.KCDM + "\">" + form.name + "</h3>")
                .append("<div id=\"div_" + form.KCDM + "\">" + form.name + "</div>")
        }

        $("#courses").accordion();
    });
</script>
<body>
<a href="/index.php/login/logout">退出登录</a>
<a href="/index.php/evaluate/evalall">一键评价</a>
<div id="courses">

</div>
</body>
</html>