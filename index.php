<?php
session_start();

date_default_timezone_set($timezone);

if (file_exists("config.php")) {
    require_once("config.php");
} else {
    die("Copy <code>example.config.php</code> to <code>config.php</code> and email addresses to the \$emails array.");
}

if (!file_exists("jobs")) {
    mkdir("jobs");
}

if ($_GET['a'] == "remind") {
    $date = date("n/j/Y", strtotime(trim($_GET['date'])));
    $time = trim($_GET['time']);
    $message = preg_replace("/\"/", "\\\"", trim($_GET['message']));
    $message = preg_replace("/\r|\n/", "", $message);

    // sudo at 16:57 7/9/2014 -f test.sh
    $rand = sha1(microtime() . date("U") . time() . $date . $time);
    foreach ($emails as $email) {
        file_put_contents("jobs/{$rand}", "echo \"{$message}\" | mail -a \"From: t@i.me\" -s \"reminder\" {$email}\n", FILE_APPEND);
    }
    exec("sudo at {$time} {$date} -f jobs/{$rand}");
    $cmd = "sudo at {$time} {$date} -f jobs/{$rand}";
    file_put_contents("commands.log", "sudo at {$time} {$date} -f jobs/{$rand}\n", FILE_APPEND);
    $_SESSION['msg'] = "<code>" . $cmd . "</code>";
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

if ($_GET['a'] == "remove") {
    exec("sudo atrm " . intval($_GET['atid']));
    header("Location:{$_SERVER['PHP_SELF']}");
    exit();
}

$curdate = date("Y-m-d");
$curtime = date("H:i", strtotime("+1 hour")) . ":AM/PM";

$out = "";
if (isset($_SESSION['msg'])) {
    $out = <<<eof
<h4>Last job submitted</h4>
<div style="overflow:auto;" class="alert alert-success" role="alert">{$_SESSION['msg']}</div>
eof;
}

ob_start();
system("sudo atq");
$c = ob_get_contents();
ob_end_clean();
$queue = "<pre class='alert alert-info'>" . $c . "</pre>";

$html = <<<eof
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Zoopaz-Reminders</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link type="text/css" rel="stylesheet" href="cdn/js/bootstrap/3.0.0/css/bootstrap.min.css" />
        <link type="text/css" rel="stylesheet" href="cdn/css/siamnet/default.css" />
        <style type="text/css">
            .home {
                max-width:600px;
            }    
        </style>
    </head>
    <body>
        <div class="container home">
            <form role="form" method="get" action="{$_SERVER['PHP_SELF']}">
                <h4>When?</h4>
                <input type="hidden" name="a" value="remind" />
                <div class="form-group">
                    <input type="date" value="{$curdate}" class="form-control" id="date" name="date" placeholder="Date..." />
                </div>
                <div class="form-group">
                    <input type="time" value="{$curtime}" class="form-control" id="time" name="time" placeholder="Time..." />
                </div>
                <h4>What?</h4>
                <div class="form-group">
                    <input type="text" class="form-control" id="message" name="message" placeholder="Message..." />
                </div>
                <button type="submit" class="btn btn-primary">Remind</button>
            </form>
            <br />
            {$out}
            <h4>Job Queue</h4>
            {$queue}
            <form role="form" method="get" action="{$_SERVER['PHP_SELF']}">
                <h4>Remove Job</h4>
                <input type="hidden" name="a" value="remove" />
                <div class="form-group">
                    <input type="text" class="form-control" id="atid" name="atid" placeholder="Enter job ID" />
                </div>
                <button type="submit" class="btn btn-primary">Remove</button>
            </form>
        </div>
        <script src="cdn/js/jquery/1.10.2/jquery-1.10.2.min.js"></script>
        <script src="cdn/js/bootstrap/3.0.0/js/bootstrap.min.js"></script>
        <script src="cdn/js/siamnet/default.js"></script>
        <script type="text/javascript">
            function random(min, max) {
                return Math.round(Math.random() * (max - min) + min);
            }

            $(document).ready(function() {
            });
        </script>
    </body>
</html>
eof;

print($html);
