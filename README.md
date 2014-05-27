proxyCheck
==========

### 主程式
`php proxyCheck.php > /dev/null &`

檢查Proxy的on-line, off-line，檢查排程是否執行

### 設定檔
config/Config.php
* 資料庫連線 SQLService::$host = ''; SQLService::$dbname = ''; SQLService::$user = ''; SQLService::$password = '';
* 檢查時間
  ProxyCheck::$chkAllTime = 10;
* 額外的程式
  ProxyCheck::$extraProgram = "php main.php ";
* 檢查方式 project , url
  ProxyCheck::$chkType = "project";
* 檢查時間
  ProxyCheck::$chkTime = "240";
* 日期
  date_default_timezone_set('Asia/Taipei');



 

 
