<?php
/**
 * Mailer is simple mail class
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Utility
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @version   GIT: <id>
 * @link      none
 */

namespace dispatch;

/**
 * Mailer is simple mail class
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Utility
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @link      none
 */
class Mailer
{
    public static $msg;
    public static $subject;

    /**
     * mailSend function, mail to send administrator
     *
     * PHP version 5
     *
     * LICENSE: none
     *
     * @category  Utility
     * @package   PackageName
     * @author    Patrick Her <zivhsiao@gmail.com>
     * @copyright 1997-2005 The PHP Group
     * @license   none <none>
     * @version   SVN: $Id$
     * @link      none
     * @return    none
     */
    public function mailSend()
    {
        $SQL = new SQLService();
        $mailer = $SQL->getMailer();

        $mail = new \PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";

        $mail->Username = "openfoundry.mailer@gmail.com";
        $mail->Password = "qwerfdsazxcv4321";

        $mail->From = 'openfoundry.mailer@gmail.com';
        $mail->FromName = "Admin";

        $mail->Subject = Mailer::$subject;
        $mail->Body = Mailer::$msg;
        $mail->IsHTML(true);

        while ($rows = $mailer->fetch()) {
            $mail->AddAddress($rows['email'], "Admin Messenger");
        }

        if (!$mail->Send()) {
            return "Mailer Error: " . $mail->ErrorInfo;
        } else {
            return "Message sent!";
        }
    }
}
