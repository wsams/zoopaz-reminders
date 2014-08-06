<?php
session_start();

date_default_timezone_set($timezone);

ob_start("ob_gzhandler");

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
        if ($msgType === "subject") {
            file_put_contents("jobs/{$rand}", "echo \"\" | mail -a \"From: t@i.me\" -s \"{$message}\" {$email}\n", FILE_APPEND);
        } else if ($msgType === "body") {
            file_put_contents("jobs/{$rand}", "echo \"{$message}\" | mail -a \"From: t@i.me\" -s \"reminder\" {$email}\n", FILE_APPEND);
        } else {
            file_put_contents("jobs/{$rand}", "echo \"{$message}\" | mail -a \"From: t@i.me\" -s \"{$message}\" {$email}\n", FILE_APPEND);
        }
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

// start: Builds queue list for UI
$jobIDs = array();
ob_start();
system("sudo atq");
$c = ob_get_contents();
ob_end_clean();
$queue = "<pre class='alert alert-info'>";
$ca = explode("\n", $c);
foreach ($ca as $k=>$l) {
    $jobId = trim(preg_replace("/^([0-9]*)\s*.*$/", "\${1}", $l));
    if (intval($jobId) > 0) {
        $jobIDs[] = $jobId;
        ob_start();
        system("sudo at -c {$jobId}");
        $jobCMD = ob_get_contents();
        ob_end_clean();
        $jobCMDa = explode("\n", $jobCMD);
        $l = preg_replace("/\t/", "    ", $l);
        $l = preg_replace("/([0-9]{2}:[0-9]{2}):[0-9]{2}/", "\${1}", $l);
        $queue .= "<span class=\"at-time\">{$l}</span>\n";

        // Uncomment to add a dashed line between at-time and at-message.
        //for ($i=0; $i<strlen($l); $i++) {
        //    $queue .= "-";
        //}
        //$queue .= "\n";

        $stop = 0;
        foreach ($jobCMDa as $k2=>$l2) {
            if ($stop > 0) {
                break;
            }
            if (preg_match("/^echo/", $l2)) {
                $l2 = preg_replace("/^.*echo \"\" \| mail -a \".*?\" -s \"(.*?)\".*$/", "\${1}", $l2);
                $queue.= "<span class=\"at-message\">{$l2}</span>\n";
                $stop++;
            }
        }
        $queue .= "\n";
    }
}
$queue .= "</pre>";
// end: Builds queue list for UI

$options = "";
foreach ($jobIDs as $id) {
    $options .= "<option value=\"{$id}\">{$id}</option>";
}

$html = <<<eof
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Zoopaz-Reminders</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link rel="shortcut icon" href="favicon.ico" />
        <link type="text/css" rel="stylesheet" href="cdn/js/bootstrap/3.0.0/css/bootstrap.min.css" />
        <link type="text/css" rel="stylesheet" href="cdn/css/siamnet/default.css" />
        <style type="text/css">
            .home {
                max-width:600px;
            }    

            .at-time {
                font-weight:bold;
            }

            .at-message {
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
            <p>Job ID is the integer in the first column below.</p>
            {$queue}
            <form role="form" method="get" action="{$_SERVER['PHP_SELF']}">
                <h4>Remove Job</h4>
                <input type="hidden" name="a" value="remove" />
                <div class="form-group">
                    <select id="atid" name="atid" class="form-control">
                        {$options}
                    </select>
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

function print_gzipped_page() {
    global $HTTP_ACCEPT_ENCODING;
    if (headers_sent()) {
        $encoding = false;
    } else if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) {
        $encoding = 'x-gzip';
    } else if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) {
        $encoding = 'gzip';
    } else {
        $encoding = false;
    }

    if ($encoding) {
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    } else {
        ob_end_flush();
        exit();
    }
}

ob_start();
ob_implicit_flush(0);
print($html);
print_gzipped_page();
