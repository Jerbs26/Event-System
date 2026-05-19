<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Gmail SMTP Configuration 
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'ardientejerby26@gmail.com');
define('MAIL_PASSWORD',   'khnxhqdcauumcpsv');
define('MAIL_FROM_EMAIL', 'ardientejerby26@gmail.com');
define('MAIL_FROM_NAME',  'EventHub');


function sendOtpEmail(string $toEmail, string $toName, string $otp): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Your EventHub Verification Code';
        $mail->Body    = buildOtpEmailHtml($toName, $otp);
        $mail->AltBody = "Hi {$toName},\n\nYour EventHub verification code is: {$otp}\n\nThis code expires in 10 minutes.\n\nIf you did not request this, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer sendOtpEmail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendResetEmail(string $toEmail, string $toName, string $otp): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Your EventHub Password Reset Code';
        $mail->Body    = buildResetEmailHtml($toName, $otp);
        $mail->AltBody = "Hi {$toName},\n\nYour EventHub password reset code is: {$otp}\n\nThis code expires in 10 minutes.\n\nIf you did not request a password reset, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer sendResetEmail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function buildOtpEmailHtml(string $name, string $otp): string
{
    $firstName  = htmlspecialchars(explode(' ', $name)[0]);
    $digitBoxes = buildDigitBoxes($otp);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Your EventHub Verification Code</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f0;padding:40px 0;">
        <tr><td align="center">
            <table width="520" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(0,0,0,0.08);">

                <!-- Header -->
                <tr>
                    <td style="background:#0f0f0f;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">&#9733; EventHub</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:44px 40px 36px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2.5px;color:#999;">Email Verification</p>
                        <h1 style="margin:0 0 18px;font-size:26px;font-weight:800;color:#0f0f0f;line-height:1.2;">Hey {$firstName}, verify your email</h1>
                        <p style="margin:0 0 32px;font-size:15px;color:#555;line-height:1.7;">Enter the 6-digit code below to complete your registration. This code expires in <strong style="color:#0f0f0f;">10 minutes</strong>.</p>

                        <!-- OTP Digit Boxes -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 36px;">
                            <tr>
                                <td align="center" style="padding:24px;background:#f8f8f6;border-radius:16px;">
                                    {$digitBoxes}
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 24px;font-size:14px;color:#999;line-height:1.6;">If you didn't create an EventHub account, you can safely ignore this email.</p>
                        <hr style="border:none;border-top:1px solid #eeeeee;margin:0 0 20px;">
                        <p style="margin:0;font-size:12px;color:#bbb;">&copy; EventHub &middot; This is an automated message, please do not reply.</p>
                    </td>
                </tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
HTML;
}

function buildResetEmailHtml(string $name, string $otp): string
{
    $firstName  = htmlspecialchars(explode(' ', $name)[0]);
    $digitBoxes = buildDigitBoxes($otp);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Your EventHub Password Reset Code</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f0;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f0;padding:40px 0;">
        <tr><td align="center">
            <table width="520" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 40px rgba(0,0,0,0.08);">

                <!-- Header -->
                <tr>
                    <td style="background:#0f0f0f;padding:28px 40px;text-align:center;">
                        <p style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">&#9733; EventHub</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:44px 40px 36px;">
                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:2.5px;color:#999;">Password Reset</p>
                        <h1 style="margin:0 0 18px;font-size:26px;font-weight:800;color:#0f0f0f;line-height:1.2;">Hey {$firstName}, reset your password</h1>
                        <p style="margin:0 0 32px;font-size:15px;color:#555;line-height:1.7;">Enter the 6-digit code below to reset your password. This code expires in <strong style="color:#0f0f0f;">10 minutes</strong>.</p>

                        <!-- OTP Digit Boxes -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 36px;">
                            <tr>
                                <td align="center" style="padding:24px;background:#f8f8f6;border-radius:16px;">
                                    {$digitBoxes}
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 24px;font-size:14px;color:#999;line-height:1.6;">If you did not request a password reset, you can safely ignore this email.</p>
                        <hr style="border:none;border-top:1px solid #eeeeee;margin:0 0 20px;">
                        <p style="margin:0;font-size:12px;color:#bbb;">&copy; EventHub &middot; This is an automated message, please do not reply.</p>
                    </td>
                </tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
HTML;
}

function buildDigitBoxes(string $otp): string
{
    $boxes = '';
    foreach (str_split($otp) as $d) {
        // Use table cells instead of spans — more reliable across email clients
        $boxes .= '<table cellpadding="0" cellspacing="0" border="0" style="display:inline-table;margin:0 4px;vertical-align:middle;">'
                . '<tr><td width="48" height="56" align="center" valign="middle" '
                . 'style="width:48px;height:56px;background-color:#0f0f0f;border-radius:10px;'
                . 'font-size:26px;font-weight:700;font-family:\'Courier New\',Courier,monospace;'
                . 'color:#f59e0b;text-align:center;vertical-align:middle;letter-spacing:0;">'
                . htmlspecialchars($d)
                . '</td></tr></table>';
    }
    return $boxes;
}