<?php
/**
 * Proxy Server 檢查程式，如果搭配main.php，不需執行
 *
 * 使用方法:
 *   php proxyChk.php > /dev/null &
 *
 * 停止方法:
 *   ps aux | grep '[p]hp proxyChk.php | awk '{print $2}' | xargs kill -9
 *
 * PHP version 5
 *
 * LICENSE : none
 *
 * @category Controller
 * @package  Patrick
 * @author   Patrick Her <zivhsiao@gmail.com>
 * @license  none <none>
 * @version  GIT: <id>
 * @link     none
 */

namespace dispatch;

require_once "utility/SQLService.php";
require_once "utility/ProxyCheck.php";
require_once "utility/Mail.php";
require_once "utility/PHPMailer/PHPMailerAutoload.php";
require_once "config/Config.php";

$SQL = new SQLService();

ignore_user_abort(true);
set_time_limit(0);

do {

    echo 'Proxy Server Check' . chr(10);

    $proxy_server = $SQL->getProxyId();
    $noProxyStatus = 0;

    $allProxyStatus = $SQL->getAllProxyStatus();

    if ($allProxyStatus == 'proxy_nice') {
        $noProxyStatus = 0;
        foreach ($proxy_server as $key => $tmp) {
            // Proxy check
            $Proxy = new ProxyCheck();
            $proxy_check = $Proxy->check($tmp);

            echo $tmp . " " . $proxy_check . chr(10);
            $proxy_svr = explode(":", $tmp);

            // first status don't sent email, second time will be sent email.
            $last_status = "";
            $fileName = 'log/proxy/proxy::' . $proxy_svr[0] . ":" . $proxy_svr[1];
            if (file_exists($fileName)) {
                $fp = fopen($fileName, 'r');
                $last_status = fgets($fp);
                fclose($fp);
            }
            $temp_status = $SQL->getProxyStatus($proxy_svr[0], $proxy_svr[1]);
            if ($last_status == "") {
                $last_status = $temp_status;
            }
            $curr_status = ($proxy_check == 'worked' ? 'on-line' : 'off-line');

            if (($curr_status != $last_status) || ($temp_status != $last_status)) {
                if ($curr_status == 'on-line') {
                    Mailer::$subject = "Notice: " .
                        $proxy_svr[0] . ":" .
                        $proxy_svr[1] . " has on-line status.";
                } else {
                    Mailer::$subject = "Alert: " .
                        $proxy_svr[0] . ":" .
                        $proxy_svr[1] . " has off-line status.";
                }
                Mailer::$msg = Mailer::$subject;
                $mail = new Mailer();
                echo $mail->mailSend();
            }

            $SQL->updateProxyStatus($proxy_svr[0], $proxy_svr[1], $curr_status);
            $fp = fopen($fileName, 'w+');
            fwrite($fp, $curr_status);
            fclose($fp);
        }
    } else {
        // only first time must be sent email
        $noProxyStatus ++;
        if ($noProxyStatus == 1) {
            Mailer::$subject = "Alert: all Proxy Server has off-line status.";
            $mail = new Mailer();
            $mail->mailSend();
        }

    }

    // 檢查排程

    echo 'Wait ' . ProxyCheck::$chkAllTime . ' second.' . chr(10);
    sleep(ProxyCheck::$chkAllTime);

} while (true);
