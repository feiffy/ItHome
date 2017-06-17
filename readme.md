### ItHome

IT之家评论爬虫，统计IT之家最近新闻评论区的各种手机型号的数量。

**示例**: test.php

``` php
require "ithome/phone.php"

$pages = 10; // 指定页数
$ithome = new ItHome();
$ithome->start($pages);
```

注：需要安装 php-curl 模块