<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:69:"/www/wwwroot/Site-GN/Check/99/application/index/view/index/index.html";i:1690557425;}*/ ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no" />
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="telephone=no,email=no" name="format-detection">
    <title>加载中...</title>
    <link href="/99/favicon.ico" rel="shortcut icon">
    <link rel="stylesheet" href="/99/static/index/css/drag.css?e=223654">
    <script src="/99/static/index/js/jquery.min.js"></script>
    <script src="/99/static/index/js/drag.js?jw=32454"></script>
    <style type="text/css">
        .slidetounlock{
            font-size: 12px;
            background:-webkit-gradient(linear,left top,right top,color-stop(0,#4d4d4d),color-stop(.4,#4d4d4d),color-stop(.5,#fff),color-stop(.6,#4d4d4d),color-stop(1,#4d4d4d));
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            -webkit-animation:slidetounlock 3s infinite;
            -webkit-text-size-adjust:none
        }
        @-webkit-keyframes slidetounlock{0%{background-position:-200px 0} 100%{background-position:200px 0}}

    </style>

</head>
<body ontouchstart="return false;" ontouchmove="return false;" ontouchend="return false;" >
<div id="wrapper" >
    <div id='item' >
        <div style="margin-bottom: 10px;" >
            请完成以下验证后继续
        </div>
        <img id="img" src="" />
        <div id="drag">
            <div class="drag_bg"></div>
            <div class="drag_text slidetounlock" onselectstart="return false;" unselectable="on">
                点击左边圆圈验证
            </div>
            <div class="handler handler_bg" style="background: #FF571D;"></div>
        </div>
    </div>
</div>


<script>
    var __root_path__ = "/99/static",_jump_url = "/99/index/open.html";
    $('#drag').drag();
    var src=__root_path__ + '/img/'+ Math.floor( Math.random()*9)+'.jpg';
    $('#img').attr('src',src)
</script>
</body>
</html>