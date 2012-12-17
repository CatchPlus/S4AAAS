<link rel="stylesheet" type="text/css" href="/../ui/css/monk.css" />
<div id="topMenu">
    <ul>
<?php
if(isset($_SESSION['token'])){
?>
        <li><a href="http://s4aaas.target-imedia.nl/search/index.php">Search</a></li>        
        <li><a href="http://s4aaas.target-imedia.nl/ui/index.php">Monk Transcriptie</a></li>
        <li><a href="http://s4aaas.target-imedia.nl/user_management/users_controller.php">User Management</a></li>
        <li><a href="http://s4aaas.target-imedia.nl/user_management/users_controller.php?action=logout">Logout <?php print $_SESSION['username']?></a></li>
<?php }else{ ?>
        <li><a href="http://s4aaas.target-imedia.nl/ui/login.php">Login</a></li>
<?php } ?>
    </ul>
</div>
<br>
<br>