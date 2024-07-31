# Site
# 1.上传site到wwwboot文件夹
# 2.创建数据库site  账号site 密码Qb16HdE1NT
# 3.web.com 绑定 /www/wwwroot/Site/Check  文件夹
# sj.web.com  dn.web.com  ip   绑定 /www/wwwroot/Site/Web 文件夹
# 4.修改\Web\99\run.php 第10行IP地址  define('API_URL', 'http://admin.web.com/99/index/api/');
# 目录 cd \www\wwwroot\Site\Web\99\
# 运行  nohup php run.php
# https://admin.xxxx.com/99/index/api/order  10
# https://admin.xxxx.com/99/index/api/allotorder  30
# https://admin.xxxx.com/99/index/api/checkbal  30
# https://admin.xxxx.com/99/index/api/getdate  60
# https://admin.xxxx.com/99/index/api/interest  60
