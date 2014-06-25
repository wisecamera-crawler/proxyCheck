dispatcher
==========

### 主程式
`php proxyCheck.php > /dev/null &`

啟動後會依序檢查Proxy Server狀態、專案排程表以及執行中的專案狀態

### 設定檔，系統自動產生
`~/crawler_conf`

crawler_conf內容說明容如下，第一次預設值如下

主機位置
$host = '127.0.0.1'

資料庫名稱
$dbname = 'NSC'

資料庫登入帳號
$user = 'root'

資料庫登入密碼
$password = 'openfoundry'

dispatcher每次檢查的間隔時間
$chkAllTime = 10

dispatcher在檢查Proxy Server時，等待Proxy Server回應的時間
$chkProxyTime = 10

在指定的路徑下呼叫crawler程式執行
$extraProgram = 'php ../crawler/main.php '

檢查方式，分為 project，url
$chkType = 'project'

檢查專案執行的逾時時間，以分鐘計算
$chkTime = 240
