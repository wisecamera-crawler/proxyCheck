<?php
/**
 * Config File
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Config
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @version   SVN: $Id$
 * @link      none
 */
namespace dispatch;

SQLService::$host = '127.0.0.1';
SQLService::$dbname = 'NSC';
SQLService::$user = 'root';
SQLService::$password = 'openfoundry';

// Proxy Server每次檢查間隔的時間，以秒計算
ProxyCheck::$chkAllTime = 10;

// Proxy Server每次檢查回應的時間，以秒計算
ProxyCheck::$chkProxyTime = 20;

// crawler的程式指定的路徑
ProxyCheck::$extraProgram = "php ./crawler/main.php ";

// 檢查方式，分為 project, url
ProxyCheck::$chkType = "project";

// 檢查排程的時間，以秒計算
ProxyCheck::$chkTime = 240;

// 日期
date_default_timezone_set('Asia/Taipei');

