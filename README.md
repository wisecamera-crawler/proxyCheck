proxyCheck
==========

### 主程式
`php proxyCheck.php > /dev/null &`

檢查Proxy的on-line, off-line，檢查排程是否執行

### 設定檔，系統自動產生
`~/crawler_conf`

檔案的內容如下，第一次預設值如下
* 資料庫連線 
`SQLService::$host = ''`
`SQLService::$dbname = ''` 
`SQLService::$user = ''` 
`SQLService::$password = ''`
* Proxy Server每次檢查間隔的時間，以秒計算
`ProxyCheck::$chkAllTime = 10`
* Proxy Server每次檢查回應的時間，以秒計算
`ProxyCheck::$chkProxyTime = 20`
* crawler的程式指定的完整路徑
`ProxyCheck::$extraProgram = "php ./crawler/main.php "`
* 檢查方式，分為 project, url
`ProxyCheck::$chkType = "project"`
* 檢查排程逾時的時間，以分鐘計算
`ProxyCheck::$chkTime = "240"`



 

 
