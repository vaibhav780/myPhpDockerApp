<?php $msg = (rand(0,1) == 0) ? "Action successful" : "Action unsuccesful, please try again"; ?>
<div id="flash" style="background:#ddd; padding:10px;"><?= $msg ?></div>
<a href="notification_message.php">Click here to load a new message</a>