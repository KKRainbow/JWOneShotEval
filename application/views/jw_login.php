<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/static/css/common.css"/>
    <link rel="stylesheet" type="text/css" href="/static/js/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="/static/js/themes/icon.css">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/jquery.easyui.min.js"></script>
    <title>登录教务系统</title>
    <script>
        function login()
        {
            var username = $("#username").val();
            var password = $("#password").val();
            var code = $("#code").val();

            var action = $("#loginform").attr("action");

            if(typeof action == 'undefined')
            {
                action = $("#loginform").action;
            }

            var msg = action.substr(-1) == "0" ? "请稍后。。。":
                "统一认证入口身份验证成功，正在授权给教务系统，这一步相当慢，请耐心等待。。。";

            $.messager.progress(
                {
                    title: "正在登录",
                    msg: msg,
                    interval:"3000"
                }
            );

            $.post(
                action,

                {
                    "username" : username,
                    "password" : password,
                    "code" : code
                },

                function(data,status)
                {
                    $.messager.progress('close');
                    if(data == "1")
                    {
                        //登录成功
                        //跳转
                        window.location.href = "/index.php/evaluate";
                    }
                    else
                    {
                        $.messager.show(
                            {
                                title : "登录失败",
                                msg: data
                            }
                        );
                        //登录失败
                    }
                }
            );
        }

        $(function()
        {
            var post = "/index.php/login/loginpost/";
            var captcha = "/index.php/login/captcha/";
            $("#loginform").attr("action" , post + 0);
            $("#captcha").attr("src",captcha + 0);
            $("#entry0").click(function()
            {
                $("#loginform").attr("action" , post + 0);
                $("#captcha").attr("src",captcha + 0);
                $("#loginimg").attr("src","/static/pic/normal.png");

            }).select();
            $("#entry1").click(function()
            {
                $("#loginform").attr("action" , post + 1);
                $("#captcha").attr("src",captcha + 1);
                $("#loginimg").attr("src","/static/pic/united.png");
            });

        });
    </script>

</head>
<body>
<style>
    #loginform{
        margin-top : 4%;
    }
    #loginform,#loginimage
    {
        width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    #logintable label
    {
        display: inline-block;
        width: 90px;
    }
    #captcha
    {
        margin-top: 10px;
        margin-bottom: -10px;

    }
    input[type = 'text'],
    input[type = 'password']
    {
        width: 100%;
    }

</style>
<form id="loginform" action="/index.php/login/loginpost/0" method="post" onsubmit="login();return false;">
    <div id="logintable">
        <div class="easyui-panel" title="登录教务系统" style="width:400px;padding:30px 60px;">
            <div style="margin-bottom:20px">
                <div><label for="username">用户名:</label></div>
                <input id="username" name="username" type="text" />
            </div>
            <div style="margin-bottom:20px">
                <div><label for="password">密码:</label></div>
                <input id="password" name="password" type="password" />
            </div>
            <div style="margin-bottom:15px">
                <div><label for="code">验证码:</label></div>
                <input id="code" name="code" type="text"/>
                <img id="captcha" src="/index.php/login/captcha/0" height="30px"/>
            </div>
            <div style="margin-bottom:10px">
                <div><label for="code">入口:</label></div>
                <input type="radio" id="entry0" name="entry" checked="checked" alt="统一认证入口（入口一）"
                       value="0" />

                <label for="entry0">
                    直接登录入口
                </label>
                <input type="radio" id="entry1" name="entry" alt="教务登录入口（入口二）"
                       value="1" />
                <label for="entry1">
                    统一认证入口
                </label>
            </div>
            <div>
                <a href="javascript:"
                   onclick="$('#loginform').trigger('submit');"
                   class="easyui-linkbutton" iconCls="icon-ok"
                   style="width:100%;height:32px">
                    登录
                </a>
            </div>
        </div>
    </div>
</form>
<div id="loginimage">
    <img id="loginimg" src='/static/pic/normal.png' width='400px' height='200px'/>
</div>

</body>
</html>