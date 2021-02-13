<?
// Require the global top file for references
require_once('../top.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login Required | Mega Man RPG Prototype</title>
        <meta name="robots" content="noindex,nofollow,noodp" />
        <base href="<?= MMRPG_CONFIG_ROOTURL ?>" />
        <style type="text/css">
            body, html {
                background-color: #262626;
                color: #dedede;
                font-size: 12px;
                line-height: 1.6;
                font-family: Verdana;
                text-align: center;
            }
            body {
                padding: 100px 50px;
            }
        </style>
        <script type="text/javascript">
            var reloadTimeout = setTimeout(function(){
                if (window.self != window.parent){
                    window.parent.location.href = 'file/load/';
                    } else {
                    window.location.href = 'file/load/';
                    }
                }, 2000);
        </script>
    </head>
    <body>
        <p>
            You have been logged out due to inactivity.
            <br />
            Please log in again to start a new session.
        </p>
    </body>
</html>
