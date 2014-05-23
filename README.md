proxyCheck
==========

### 主程式

`php main.php > /dev/null &`

### 設定檔

config/Config.php
  針對SQL的設定 
    SQLService::$host = '';
    SQLService::$dbname = '';
    SQLService::$user = '';
    SQLService::$password = ''; 
  檢查時間，以秒統計
    ProxyCheck::$chkAllTime = 10;
  額外的程式
    ProxyCheck::$extraProgram = "php /Users/zivhsiao/gitsource/crawler/testCrawler.php ";
  檢查方式 project , url
    ProxyCheck::$chkType = "url";
  日期
    date_default_timezone_set('Asia/Taipei');

 

 
