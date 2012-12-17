<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
    'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>

<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<title>:: Monk.Transcription ::</title>
	<link rel='stylesheet' type='text/css' href='css/yui.2.8.1.css' />
	<link rel='stylesheet' type='text/css' href='css/monk.transcribe.css' />
	<!--[if IE]>
		<link rel='stylesheet' type='text/css' href='css/all-ie-only.css' />
	<![endif]-->
	<script type='text/javascript' src='js/lib/jquery-1.6.1.min.js'></script>
	<script type='text/javascript' src='js/lib/jquery-ui-1.8.13.custom.min.js'></script>
    <script type='text/javascript' src='js/lib/sprintf-0.7-beta1.js'></script>
</head>
<body>

	{include file="../../search/templates/topmenu.tpl"}

	<noscript>
		<p>javascript required</p>
	</noscript>
	<div id='header'></div> <!-- #header -->

	<div id='container'>
            <div id="main">
            <h1 class="titel">Login</h1>
                {if !empty($errorMessage)}
                <div id="monkerror">
                    <h2>Error</h2>
                    <p>
                    {foreach from=$errorMessage item=error}
                    {strip_tags($error)}<br />
                    {/foreach}
                    </p>
                </div>
                {/if}

				<form id="loginForm" method="post" action="login.php">
					<p>
						<label for="username">Username:</label>
						<input type="text" id="username" name="username">
					</p>
					<p>
						<label for="password">Password:</label>
						<input type="password" id="password" name="password">
					</p>
					<p>
						<input type="submit" value="Inloggen" id="submit">
					</p>
				</form>

        </div> <!-- #main -->
	</div> <!-- #container -->
    {include file="footer.tpl"}
</body>
</html>