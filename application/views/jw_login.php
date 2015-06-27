<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<form action="/index.php/login/loginpost" method="post">
<table>
    <tr>
        <td>
            <label for="username">用户名</label>
        </td>
        <td>
            <input id="username" name="username" type="text" width="100px"/>
        </td>
    </tr>
    <tr>
        <td>
            <label for="password">密码</label>
        </td>
        <td>
            <input id="password" name="password" type="password" width="100px"/>
        </td>
    </tr>

    <tr>
        <td>
            <label for="code">验证码</label>
        </td>
        <td>
            <input id="code" name="code" type="text" width="100px"/>
            <img src="/index.php/login/captcha"/>
        </td>
    </tr>
    <tr>
        <td>
            <input type="submit"/>
        </td>
    </tr>

</table>
</form>

</body>
</html>