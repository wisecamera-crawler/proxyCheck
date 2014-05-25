<?php
/**
 * Dispatch Server 主要的檔案
 *
 * 使用方法:
 *   php main.php > /dev/null &
 *
 * 停止方法:
 *   ps aux | grep '[p]hp main.php | awk '{print $2}' | xargs kill -9
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
$Proxy = new ProxyCheck();

do {

    // 檢查Proxy Server
    $proxy_server = $SQL->getProxyId();
    $noProxyStatus = 0;

    $allProxyStatus = $SQL->getAllProxyStatus();

    // Proxy Server 檢查
    if ($allProxyStatus == 'proxy_nice') {
        $noProxyStatus = 0;
        $proxy_svr = $Proxy->checkProxy($proxy_server);
        foreach ($proxy_svr as $proxy => $status) {
            $fileName = 'log/proxy/proxy::' . $proxy;
            $proxyGet = explode(":", $proxy);
            $last_status = "";
            if (file_exists($fileName)) {
                $fp = fopen($fileName, 'r');
                $last_status = fgets($fp);
                fclose($fp);
            }
            $temp_status = $SQL->getProxyStatus($proxy);
            if ($last_status == "") {
                $last_status = $temp_status;
            }

            $curr_status = $status;

            if (($curr_status != $last_status) || ($temp_status != $last_status)) {
                if ($curr_status == 'on-line') {
                    Mailer::$subject = "Notice: " .
                        $proxy . " has on-line status.";
                    $SQL->updateLog($proxyGet[0], '偵測Proxy Server恢復連線');
                } else {
                    Mailer::$subject = "Alert: " .
                        $proxy . " has off-line status.";
                    $SQL->updateLog($proxyGet[0], '偵測Proxy Server中斷連線');
                }
                Mailer::$msg = Mailer::$subject;
                $mail = new Mailer();
                $mail->mailSend();
            }

            $SQL->updateProxyStatus($proxy, $curr_status);
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

    // 檢查排程, 如果proxy都沒有就跳過
    if ($noProxyStatus == 0) {
        $schedule = $SQL->getSchedule();
        $proxy_server = new ProxyCheck();

        while ($rows = $schedule->fetch()) {

            if ($rows['sch_type'] == "one_time") {
                $theDate = date('Y-m-d H:i:00');

                $result = $SQL->getScheduleParam(
                    "`sch_type` = 'one_time'
                    AND `time` = '" . $theDate . "'"
                );
            }

            if ($rows['sch_type'] == "daily") {
                $theDate = date("H:i:00");

                $result = $SQL->getScheduleParam(
                    "`sch_type` = 'daily'
                    AND `time` = '2012-01-01 " . $theDate . "'"
                );
            }

            if ($rows['sch_type'] == "weekly") {
                $theDate = date("H:i:00");

                $result = $SQL->getScheduleParam(
                    " `sch_type` = 'weekly'
                        AND `time` = '2012-01-01 " . $theDate . "'"
                    . " AND `schedule` = " . date('N')
                );
            }

            // 狀態為空的，或者為finish
            while ($arrRow = $result->fetch()) {
                // status is empty or finish
                $arrID = $arrRow['schedule_id'];
                $updFile = "log/server/server::" . $arrID;
                $sGroup = $SQL->getScheduleGroup($arrID);

                if (file_exists($updFile)) {
                    $updateSchedule = fopen($updFile, "r");
                    $updLine = fgets($updateSchedule);
                    fclose($updateSchedule);
                } else {
                    $updateSchedule = fopen($updFile, "w+");
                    $updLine = fgets($updateSchedule);
                    fclose($updateSchedule);
                }

                if ($updLine  == '' || $updLine == 'finish') {
                    $time = $arrRow['time'];
                    $type = array();
                    $runVar = array();


                    while ($sGRow = $sGroup->fetch()) {
                        if ($sGRow['type'] == 'year' || $sGRow['type'] == 'group') {

                            if ($sGRow['member'] != 'all' && $sGRow['type'] == 'year') {
                                $type[] = 'year';
                                $runVar['year'] = "`year` = " . $sGRow['member'];
                            }

                            if ($sGRow['member'] != 'all' && $sGRow['type'] == 'group') {
                                $type[] = 'group';
                                $runVar['group'] = "`type` = '" . $sGRow['member'] . "'";
                            }

                        }

                        if ($sGRow['type'] == 'project') {
                            $type[] = 'project';
                            $runVar[] = $sGRow['member'];
                        }
                    }

                    $runPrg = array();
                    $runPrg1 = array();
                    $runPrg2 = array();
                    $runPrg3 = array();
                    $runExec = "";

                    // all
                    if (count($runVar) == 0) {
                        $run = $SQL->getProjectNoParam();
                        while ($runRows = $run->fetch()) {
                            $runPrg1[] = ProxyCheck::$extraProgram .
                                ((ProxyCheck::$chkType == "project") ?
                                    $runRows['project_id'] : $runRows['url']);
                        }
                    }

                    // project
                    if (count($runVar) >= 1 && $type[0] == 'project') {
                        for ($i = 0; $i < count($runVar); $i++) {
                            $project = $SQL->getProject($runVar[$i]);
                            $project_id = ((ProxyCheck::$chkType == "project") ?
                                $project['project_id'] : $project['url']);
                            $runPrg2[] = ProxyCheck::$extraProgram . $project_id;
                        }

                    }

                    // year or group
                    if (count($runVar) >= 1) {
                        if ($type[0] == 'year' || $type[0] == 'group') {
                            if ($type[0] == 'year') {
                                $runPrg[] = $runVar['year'];
                            }
                            if ($type[0] == 'group') {
                                $runPrg[] = $runVar['group'];
                            } elseif (count($type) > 1 && $type[1] == 'group') {
                                $runPrg[] = $runVar['group'];
                            }
                            $runExec = implode(" AND ", $runPrg);

                            $prg3Result = $SQL->getProjectParam($runExec);
                            while ($prg3Rows = $prg3Result->fetch()) {
                                $runPrg3[] = ProxyCheck::$extraProgram .
                                    ((ProxyCheck::$chkType == "project") ?
                                        $prg3Rows['project_id'] : $prg3Rows['url']);
                            }
                        }
                    }

                    if (count($runPrg1) == 0 && count($runPrg2) == 0 && count($runPrg3) == 0) {

                        // not schedule project
                        $updateSchedule = fopen("log/server/server::" . $arrID, "w+");
                        fwrite($updateSchedule, "not_exist");
                        fclose($updateSchedule);

                    } else {

                        // schedule project
                        if (count($runPrg1) > 0) {
                            $fp = fopen("log/" . $arrID . ".log", "w+");
                            for ($i=0; $i < count($runPrg1); $i++) {
                                exec($runPrg1[$i] . " > /dev/null &");
                                fputs($fp, $runPrg1[$i] . chr(10));
                            }
                            fclose($fp);
                        }

                        if (count($runPrg2) > 0) {
                            $fp = fopen("log/" . $arrID . ".log", "w+");
                            for ($i=0; $i < count($runPrg2); $i++) {
                                exec($runPrg2[$i] . " > /dev/null &");
                                fputs($fp, $runPrg2[$i] . chr(10));
                            }
                            fclose($fp);
                        }

                        if (count($runPrg3) > 0) {
                            $fp = fopen("log/" . $arrID . ".log", "w+");
                            for ($i=0; $i < count($runPrg3); $i++) {
                                exec($runPrg3[$i] . " > /dev/null &");
                                fputs($fp, $runPrg3[$i] . chr(10));
                            }
                            fclose($fp);
                        }

                        $updateSchedule = fopen("log/server/server::" . $arrID, "w+");
                        fwrite($updateSchedule, "work");
                        fclose($updateSchedule);
                    }

                }

            }
        }

        // 讀取日誌檔案
        $logDir = "./log/server/";
        $files = scandir($logDir);
        foreach ($files as $fileName) {
            if ($fileName != "." && $fileName != "..") {

                $logFile = explode("::", $fileName);

                $fileLog = fopen($logDir . $fileName, "r");
                $fileLogLine = fgets($fileLog);
                fclose($fileLog);

                if ($fileLogLine == "work") {
                    $fp = fopen("./log/" . $logFile[1] . ".log", "r");
                    while (!feof($fp)) {
                        $cmdLine = fgets($fp);
                        $output = array();
                        if (trim($cmdLine) != "") {
                            $cmdLine = trim($cmdLine);
                            $cmdLine =  substr_replace($cmdLine, "[p]", 0, 1);
                            exec("ps aux | grep '$cmdLine' | awk '{print $2}' | xargs");
                            exec("ps aux | grep '$cmdLine' | awk '{print $10}' | xargs", $output);

                            // 檢查是否逾時
                            if (count($output) > 0) {
                                if ($output[0] >= "2:00.0") {
                                    $errLog = fgets("log/error.log", "w");
                                    $errorMsg = "Alert " . date("Y-m-d H:i") .
                                        " " .
                                        $logFile[1] .
                                        ": Run at 2 hours ago";
                                    fputs($errLog, $errorMsg);
                                    fclose($errLog);
                                    exec("ps aux | grep '$cmdLine' | awk '{print $2}' | xargs kill -9");

                                    //$SQL->updateLog($errorMsg);

                                    Mailer::$subject = $errorMsg;
                                    $mail = new Mailer();
                                    $mail->mailSend();
                                }
                            }

                            // 沒有錯誤則表示finish
                            if (count($output) == 0) {
                                $updateSchedule = fopen($logDir . $fileName, "w+");
                                fwrite($updateSchedule, "finish");
                                fclose($updateSchedule);

                            }
                        }
                    }
                    fclose($fp);
                }

            };
        }
    }

    sleep(1);
} while (true);
