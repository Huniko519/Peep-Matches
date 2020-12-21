<?php
/* Peepmatches Light By Peepdev co - www.peepdev.com */
define('_PEEP_', true);

define('DS', DIRECTORY_SEPARATOR);

define('PEEP_DIR_ROOT', dirname(__FILE__) . DS);

require_once(PEEP_DIR_ROOT . 'includes' . DS . 'init.php');
$session = PEEP_Session::getInstance();
$session->start();
$errorDetails = '';

if ( $session->isKeySet('errorData') )
{
    $errorData = unserialize($session->get('errorData'));    
    $trace = '';

    if ( !empty($errorData['trace']) )
    {
        $trace = '<tr>
                        <td class="lbl">Trace:</td>
                        <td class="cnt">' . $errorData['trace'] . '</td>
                </tr>';
    }

    $errorDetails = '<div style="margin-top: 30px;">
            <b>Error details</b>:
            <table style="font-size: 13px;">
                <tbody>
                <tr>
                        <td class="lbl">Type:</td>
                        <td class="cnt">' . $errorData['type'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Message:</td>
                        <td class="cnt">' . $errorData['message'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">File:</td>
                        <td class="cnt">' . $errorData['file'] . '</td>
                </tr>
                <tr>
                        <td class="lbl">Line:</td>
                        <td class="cnt">' . $errorData['line'] . '</td>
                </tr>
                ' . $trace . '
        </tbody></table>
        </div>';
}

$output = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body style="font:18px Tahoma;">
    <div style="display: inline-block; width:100%; text-align:center; padding-right: 16px; border-bottom: 1px solid #666; padding-bottom: 6px; margin-bottom: 8px;">HTTP Error 500<br/>Internal Server Error.</div></br>
    <div style="font-size: 13px; margin-bottom: 4px;"><a href="javascript://" onclick="getElementById(\'hiddenNode\').style.display=\'block\'">click here for what caused this error</a></div>
    <div style="font-size: 13px; display: none; text-align:left; " id="hiddenNode">
        <div style="margin-top: 30px;">
    	<b style="line-height: 24px;">Something went wrong</b>!</br> 
    	To get the error details follow these steps:</br>
    	- Enable Debug in <i>config.php</i> file </br>
 		- Or if you recently coding something or uploaded a not supported plugin, delete this last activity .
       </div>
        ' . $errorDetails . '

    </div>
<div style="width:100%; background:red; color:#fff; font-size:13px; margin-top:20px; padding:10px; text-align:left;">
<strong>Important notice : </strong>If this new install with secure server ( https:// ), so this error caused because you removed secure port ( :443 ), you should leave it in installation proccess then importantly remove it after installation proccess is completed from include/config.php file .<br/>Now if you see this error for this reason you can return to installation from the start and complete it without ( :443 ), and it will not happen again, because it happen one time only .<br/>if it is old installation then check reasons above .
</div>
  </body>
</html>
';

echo $output;

