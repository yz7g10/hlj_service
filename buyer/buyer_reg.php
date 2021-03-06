<?php
require_once('../saeDao.php');
session_start();

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>红领巾小助手</title>
    <meta content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" name="viewport">
    <link rel="stylesheet" href="http://7u2les.com1.z0.glb.clouddn.com/jquery.mobile.min.css"/>
    <link rel="stylesheet" href="http://7u2les.com1.z0.glb.clouddn.com/cust.css"/>
    <script src="http://7u2les.com1.z0.glb.clouddn.com/jquery-2.0.3.min.js"></script>
    <script src="http://7u2les.com1.z0.glb.clouddn.com/jquery.mobile.min.js"></script>
    <script src="http://7u2les.com1.z0.glb.clouddn.com/jquery.validate.min.js"></script>
</head>
<body>
<div data-role="page" id="home">
    <div data-role="header" data-position="fixed">
        <h1>联系方式</h1>
    </div>

    <div class="ui-body ui-body-a content-it">
        <form method="post" action="buyer_reg_success.php" id="personForm">
            <ul data-role="listview">
                <li>
                    <br>
                    <div class="clearfix">
                        <label for="mobile" class="control-label">手机号码</label>
                        <input name="mobile" id="mobile" type="tel" value="" data-clear-btn="true">
                    </div>
                    <br>
                    <br>
                    <div class="clearfix">
                        <label for="email">常用邮箱</label>
                        <input name="email" id="email" value="" type="email" data-clear-btn="true">
                    </div>
                    <br>
                    <br>
                    <div class="clearfix">
                        <label for="we_chat">微信号（或QQ号）</label>
                        <input type="text" name="we_chat" id="we_chat" value=""  data-clear-btn="true">
                    </div>
                    <br>
                    <br>
                </li>
            </ul>
            <input type="submit" value="提交" data-transition="slideup"  data-theme="b" id="submit_btn">

            <script>
                $().ready(function() {
                    jQuery.validator.addMethod("isMobile", function(value, element) {
                        var length = value.length;
                        var mobile = /^1[3|4|5|8|7][0-9]\d{4,8}$/;
                        return this.optional(element) || (length == 11 && mobile.test(value));
                    }, "请正确填写您的手机号码");


                    $("#personForm").validate({
                        errorPlacement: function(error, element) {
                            error.appendTo(element.parent());
                        },
                        rules: {
                            we_chat: "required",
                            mobile: {
                                required:true,
                                isMobile: true
                            },
                            email: {
                                required: true,
                                email: true
                            }
                        },
                        messages:{
                            we_chat:"微信号（或QQ号）不能为空",
                            mobile:{
                                required:"手机号码不能为空",
                                isMobile:"请填写正确的手机号码"
                            },
                            email:{
                                required:"邮箱不能为空",
                                email:"请填写正确的邮箱地址"
                            }
                        }
                    });
                });

            </script>
        </form>
    </div>
    <div data-role="footer" data-position="fixed">
        <h4>红领巾海外代购</h4>
    </div>
</div>
</body>
</html>