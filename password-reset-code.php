<?php
session_start();
include('dbcon.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_password_reset($get_name, $get_email, $token)
{
    $mail = new PHPMailer(true);
    // $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    $mail->Host = 'smtp.gmail.com';
    $mail->Username = 'amasangkay15@gmail.com';
    $mail->Password = 'daiqaboyavfzjkmc';

    $mail->SMTPSecure = "tls"; 
    $mail->Port = 587;

    $mail->setFrom('amasangkay15@gmail.com', "PRC Reset Password");
    $mail->addAddress($get_email);

    $mail->isHTML(true);
    $mail->Subject = 'Reset Password Notification';

    $email_template = "
    <h2>Hello</h2>
    <h3>You are receiving this email because we received a password reset request for your account</h3>
    <br/><br/>
    <a href = 'http://localhost/PRC_website/password-change.php?token=$token&email=$email'>Click Me </a>
    ";

    $mail->Body = $email_template;
    $mail->send();
}



if(isset($_POST['password_reset_btn']))
{
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $token = md5(rand());

    $check_email = "SELECT email FROM tbl_user WHERE email = '$email' LIMIT 1";
    $check_email_run = mysqli_query($con, $check_email);

    if(mysqli_num_rows($check_email_run) > 0)
    {
        $row = mysqli_fetch_array($check_email_run);
        $get_name = $row['name'];
        $get_email = $row['email'];

        $update_token = "UPDATE tbl_user SET verify_token = '$token' WHERE email = '$get_email' LIMIT 1";
        $update_token_run = mysqli_query($con, $update_token);

        if($update_token_run)
        {
            send_password_reset($get_name, $get_email, $token);
            $_SESSION['status'] = "Password Reset link has been sent";
            header("Location: password-reset.php");
            exit(0);
        }
        else
        {
            $_SESSION['status'] = "Something went wrong.";
            header("Location: password-reset.php");
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "Email does not exists";
        header("Location: password-reset.php");
        exit(0);
    }
}

if(isset($_POST['password_update_btn']))
{
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $repassword = mysqli_real_escape_string($con, $_POST['repassword']);

    $token = mysqli_real_escape_string($con, $_POST['password_token']);

    if(!empty($token))
    {
        if(!empty($email) && !empty($new_password) && !empty($repassword))
        {
            //checking token if existing
            $check_token = "SELECT verify_token FROM tbl_user WHERE verify_token = '$token' LIMIT 1";
            $check_token_run = mysqli_query($con, $check_token);

            if(mysqli_num_rows($check_token_run))
            {
                if($new_password == $repassword )
                {
                    $update_password = "UPDATE tbl_user SET password = '$new_password' WHERE verify_token = '$token' LIMIT 1";
                    $update_password_run = mysqli_query($con, $update_password);

                    if($update_password_run)
                    {
                        $new_token = md5(rand())."PRC";
                        $update_to_new_token = "UPDATE tbl_user SET verify_token = '$new_token' WHERE verify_token = '$token' LIMIT 1";
                        $update_to_new_token_run = mysqli_query($con, $update_to_new_token);

                        $_SESSION['status'] = "Changed password successfully!";
                        header("Location: login.php");
                        exit(0);
                    }
                    else
                    {
                        $_SESSION['status'] = "Did not update password, Try again.";
                        header("Location: password-change.php?token=$token&email=$email");
                        exit(0);
                    }
                }
                else
                {
                    $_SESSION['status'] = "Password and Confirm Password does not match!";
                    header("Location: password-change.php?token=$token&email=$email");
                    exit(0);
                }
            }
            else
            {
                $_SESSION['status'] = "Invalid Token";
                header("Location: password-change.php?token=$token&email=$email");
                exit(0);
            }
        }
        else
        {
            $_SESSION['status'] = "Fill up the field";
            header("Location: password-change.php?token=$token&email=$email");
            exit(0);
        }
    }
    else
    {
        $_SESSION['status'] = "No Token Available";
        header("Location: password-change.php");
        exit(0);
    }
}


?>