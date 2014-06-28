<?php

date_default_timezone_set('Asia/Taipei');
require_once ("autoLoader.php");
require_once ("utility/PHPMailer/class.phpmailer.php");

use utility\Mailer;
use utility\MakeConfig;
use utility\ProxyCheck;
use utility\ProxySQLService;

$makeConfig = new MakeConfig();
$makeConfig->Make();

exec("ps aux | grep '[p]hp proxyCheck.php' | awk '{print $2}' | xargs", $firstCmd);
$firstRun = explode(" ", $firstCmd[0]);
if (count($firstRun) > 1) {
    echo "Alert: This program is still running." . chr(10);
    exit;
}


$SQL = new ProxySQLService();
$Proxy = new ProxyCheck();
$check = 0;

do {
    // Proxy Server 檢查
    $proxyServer = $SQL->getProxyId();
    $proxySvr = $Proxy->checkProxy($proxyServer);

    foreach ($proxySvr as $proxy => $status) {
        $fileName = 'log/proxy/proxy::' . $proxy;
        $proxyGet = explode(":", $proxy);

        $currStatus = $status;
        $tempStatus = $SQL->getProxyStatus($proxy);

        if ($currStatus != $tempStatus) {
            if (!empty($currStatus) && !empty($tempStatus)) {
                $msg1 = "偵測Proxy Server恢復連線";
                $msg2 = "偵測Proxy Server中斷連線";
                $message1 = "偵測Proxy Server, " . $proxyGet[0] . ", 於" . date("Y-m-d H:i:s") . "恢復連線";
                $message2 = "偵測Proxy Server, " . $proxyGet[0] . ", 於" . date("Y-m-d H:i:s") . "中斷連線";

                if ($currStatus == 'on-line') {
                    Mailer::$subject = $message1;
                    $SQL->updateLog($proxyGet[0], $msg1);
                } else {
                    Mailer::$subject = $message2;
                    $SQL->updateLog($proxyGet[0], $msg2);
                }
                Mailer::$msg = Mailer::$subject;
                $mail = new Mailer();
                $mail->mailSend();
            }
        }

        $SQL->updateProxyStatus($proxy, $currStatus);
        $fp = fopen($fileName, 'w+');
        fwrite($fp, $currStatus);
        fclose($fp);
    }

    $allProxyStatus = $SQL->getAllProxyStatus();

    if ($allProxyStatus == "proxy_error") {
        $check ++;
    } else {
        $check = 0;
    }

    if ($allProxyStatus == "proxy_error" && $check == 1) {
        $msgAll = "偵測所有Proxy Server中斷連線";
        $SQL->updateLog('', $msgAll);
        Mailer::$subject = $msgAll;
        $mail = new Mailer();
        $mail->mailSend();
    }

    // 檢查排程, 如果proxy都沒有就跳過
    if ($allProxyStatus == 'proxy_nice') {
        $schedule = $SQL->getSchedule();
        $proxyServer = new ProxyCheck();

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

                if ($updLine  == '' || $updLine == 'finish' || $updLine == 'not_exist') {
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
                            $projectID = ((ProxyCheck::$chkType == "project") ?
                                $project['project_id'] : $project['url']);
                            $runPrg2[] = ProxyCheck::$extraProgram . $projectID;
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
                                $out = explode(" ", $runPrg1[$i]);
                                $proxyStatus = $SQL->getProjectStatus(trim($out[2]));
                                if ($proxyStatus != 'working') {
                                    $SQL->updateProjectStatus(trim($out[2]), "working");
                                    exec($runPrg1[$i] . " > /dev/null &");
                                }
                                fputs($fp, $runPrg1[$i] . chr(10));
                            }
                            fclose($fp);
                        }

                        if (count($runPrg2) > 0) {
                            $fp = fopen("log/" . $arrID . ".log", "w+");
                            for ($i=0; $i < count($runPrg2); $i++) {
                                $out = explode(" ", $runPrg2[$i]);
                                $proxyStatus = $SQL->getProjectStatus(trim($out[2]));
                                if ($proxyStatus != 'working') {
                                    $SQL->updateProjectStatus(trim($out[2]), "working");
                                    exec($runPrg2[$i] . " > /dev/null &");
                                }
                                fputs($fp, $runPrg2[$i] . chr(10));
                            }
                            fclose($fp);
                        }

                        if (count($runPrg3) > 0) {
                            $fp = fopen("log/" . $arrID . ".log", "w+");
                            for ($i=0; $i < count($runPrg3); $i++) {
                                $out = explode(" ", $runPrg3[$i]);
                                $proxyStatus = $SQL->getProjectStatus(trim($out[2]));
                                if ($proxyStatus != 'working') {
                                    $SQL->updateProjectStatus(trim($out[2]), "working");
                                    exec($runPrg3[$i] . " > /dev/null &");
                                }
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

    }

    // 讀取日誌檔案
    $logDir = "log/server/";
    $files = scandir($logDir);
    foreach ($files as $fileName) {
        if ($fileName != "." && $fileName != "..") {

            $logFile = explode("::", $fileName);

            $fileLog = fopen($logDir . $fileName, "r");
            $fileLogLine = fgets($fileLog);
            fclose($fileLog);

            if ($fileLogLine == "work") {
                $fp = fopen("log/" . $logFile[1] . ".log", "r");
                while (!feof($fp)) {
                    $cmdLine = fgets($fp);
                    $output = array();
                    if (trim($cmdLine) != "") {
                        $cmdLine = trim($cmdLine);
                        $cmdLine =  substr_replace($cmdLine, "[p]", 0, 1);
                        exec("ps aux | grep '$cmdLine' | awk '{print $2}' | xargs");
                        exec("ps aux | grep '$cmdLine' | awk '{print $9}' | xargs", $output);

                        // 檢查是否逾時
                        if (count($output) > 0) {
                            $timeDiff = $SQL->dateDifference("n", $output[0] . ":00", date("H:i:s"));
                            if (!empty($output[0]) && ($timeDiff >= ProxyCheck::$chkTime)) {
                                $cmdFile = explode(" ", $cmdLine);
                                $errLog = fopen("log/error.log", "w+");
                                $errorMsg = $cmdFile[2] .
                                    " 執行由 " . $output[0] . ":00" . " 已經超過" . (ProxyCheck::$chkTime / 60) . "小時";
                                fputs($errLog, $errorMsg);
                                fclose($errLog);
                                exec("ps aux | grep '$cmdLine' | awk '{print $2}' | xargs kill -9");

                                Mailer::$subject = $errorMsg;
                                $mail = new Mailer();
                                $mail->mailSend();
                            }
                        }

                        // 沒有錯誤則表示finish
                        if (empty($output[0])) {
                            if (file_exists($logDir . "/" . $fileName)) {
                                unlink($logDir . "/" . $fileName);
                            }
                        }
                    }
                }
                fclose($fp);
            }

        };
    }


    sleep(1);
} while (true);
