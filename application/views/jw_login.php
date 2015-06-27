<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <link rel="stylesheet" href="/static/js/theme/smothness.css"/>
    <script src="/static/js/external/jquery/jquery.js"></script>
    <script src="/static/js/jquery-ui.js"></script>
    <title>登录教务系统</title>
</head>
<body>
<style>
    body{
        font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
        font-size:13px;
    }
    #loginform
    {
        width: 400px;
        margin-left: auto;
        margin-right: auto;
        margin-top : 10%;
    }
    #logintable
    {
        width: inherit;
    }

    #logintable label
    {
        display: inline-block;
        width: 90px;
    }

    #loginform input[type='submit'],
    #loginform input[type='reset']
    {
        width: 40%;
        margin-right: 20px;
    }

</style>
<form action="/index.php/login/loginpost/0" method="post" onsubmit="login();return false;">
    <div id="loginform">
        <table id="logintable" cellspacing="10px">
            <tr>
                <td>
                    <label for="username">用户名:</label>
                </td>
                <td>
                    <input id="username" name="username" type="text" />
                </td>
            </tr>
            <tr>
                <td>
                    <label for="password">密码:</label>
                </td>
                <td>
                    <input id="password" name="password" type="password" />
                </td>
            </tr>

            <tr>
                <td>
                    <label for="code">验证码:</label>
                </td>
                <td>
                    <input id="code" name="code" type="text" />
                </td>
                <td>
                    <img id="captcha" src="/index.php/login/captcha/0"/>
                </td>
            </tr>
            <tr>
                <td>
                    登录入口
                </td>
                <td>
                    <div id="entry" style="display: inline">
                        <input type="radio" id="entry0" name="entry" checked="checked" alt="统一认证入口（入口一）"
                            value="0" onchange="entryChange(this);"/>

                        <label for="entry0">
                            入口一
                        </label>
                        <input type="radio" id="entry1" name="entry" alt="教务登录入口（入口二）"
                             value="1" onchange="entryChange(this);"/>
                        <label for="entry1">
                            入口二

                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>

                </td>
                <td>
                    <input type="submit" value="登录"/>
                    <input type="reset" value="重置"/>
                </td>
                <td>
                </td>
            </tr>

        </table>
    </div>
</form>

<div id="pic">

</div>

</body>

<script>
    $(function(){
       $("#entry").buttonset({
       });
    });
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
        $.post(
            action,
            {
                "username" : username,
                "password" : password,
                "code" : code
            },
            function(data,status)
            {
                alert(data);
            }
        );
    }
    function entryChange(obj)
    {
        var entry = obj.value;

        if(entry != 0 && entry != 1)
        {
            entry = 0;
        }

    }


    $(function()
    {
        $("#loginform").attr("action" , "/index.php/login/loginpost/" + 0);
        $("#captcha").attr("src","/index.php/login/captcha/" + 0);
        $("#entry0").click(function()
        {
            $("#loginform").attr("action" , "/index.php/login/loginpost/" + 0);
            $("#captcha").attr("src","/index.php/login/captcha/" + 0);

        }).select();
        $("#entry1").click(function()
        {
            $("#loginform").attr("action" , "/index.php/login/loginpost/" + 1);
            $("#captcha").attr("src","/index.php/login/captcha/" + 1);
        });
    });
</script>

</html>