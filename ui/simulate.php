<?php
$nonces = array(
    'trainee',
    'transcribent',
    'trainer',
    'bot'
);
if(!(isset($_GET['subcmd']))) {
    if(isset($_GET['appid'])) {
        $nonce = md5(rand());
        ?>
        Found nonce for application id "<?php echo $_GET['appid'];?>"<br />
        <?php foreach($nonces as $nonce):?>
            <a href="http://localhost/transcribe/index.php?username=fabriek&nonce=<?php echo $nonce;?>">Click to simulate login as <?php echo $nonce;?></a><br />
        <?php endforeach;?>
        <?php
    }
} else {
    $token = '4013761414a37968645208c2593f89fa';
    if(isset($_GET['nonce']))
    {
        switch($_GET['nonce']) {
            case 'trainee':
                $token = '0ca9274221ea4b0d744e9b106b90f3f0';
                break;
            case 'transcribent':
                $token = '005cfdcf65fc73ce00d215124a032206';
                break;
            case 'trainer':
                $token = '571ee1c30fcbcb3f29e324ec2156df59';
                break;
        }
        // echo the token
        //echo '4013761414a37968645208c2593f89fa'; <-- minne
        echo $token;
        //echo 'ontwikkelfabriektest';
    } else {
        echo "What?";
    }
}
    
?>
