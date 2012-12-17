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
    <script type="text/javascript">
        $(function() {
            {*var startPages = {*}
                {*{foreach from=$books item=book}*}
                {*"{$book->id}" : {*}
                    {*"firstPage" : "{$book->firstPage}",*}
                    {*"startPage" : "{$book->startPage}",*}
                    {*"lastPage"  : "{$book->lastPage}"*}
                {*},*}
                {*{/foreach}*}
            {*};*}
            {*$('#book_id').change(function() {*}
                {*var startPage = startPages[$('#book_id').val()].startPage;*}
                {*$('#pagenr').val(startPage);*}
            {*});*}

            {*var startPage = startPages[$('#book_id').val()].startPage;*}
            {*$('#pagenr').val(startPage);*}


        });
    </script>
</head>
<body>

	{include file="../../search/templates/topmenu_auth.tpl"}

	<noscript>
		<p>javascript required</p>
	</noscript>
	<div id='header'></div> <!-- #header -->

	<div id='container'>
            <div id="main">
            <h1 class="titel">Monk Transcriptie</h1>
                {if !empty($errorMessage)}
                <div id="monkerror">
                    <h2>Error</h2>
                    <p>
                    {foreach from=$errorMessage item=error}
                    {$error}<br />
                    {/foreach}
                    </p>
                </div>
                {/if}
                    {if $role == 1}
                        {assign "rolename" "Guest/Bot"}
                        {assign "rolemessage" "U bent ingelogd als bot, er wordt niks verstuurd naar Monk"}
                    {elseif $role == 3}
                        {assign "rolename" "Trainee"}
                        {assign "rolemessage" "U bent ingelogd als leerling, uw resultaten worden lokaal opgeslagen en niet naar Monk verstuurd"}
                    {elseif $role == 7}
                        {assign "rolename" "Transcriber"}
                        {assign "rolemessage" "U bent ingelogd als transcribeerder, uw resultaten worden naar Monk verstuurd"}
                    {elseif $role == 15}
                        {assign "rolename" "Trainer"}
                        {assign "rolemessage" "U bent ingelogd als trainer uw resultaten worden naar Monk verstuurd en u kunt, indien aanwezig, opgeslagen resultaten van leerlingen controleren"}
                    {elseif $role == 31}
                        {assign "rolename" "Ingest admin"}
                        {assign "rolemessage" "U bent ingelogd als Ingest admin"}
                    {elseif $role == 63}
                        {assign "rolename" "Global admin"}
                        {assign "rolemessage" "U bent ingelogd als Global admin"}
                    {else}
                        {assign "rolename" "Unknown"}
                        {assign "rolemessage" "U bent ingelogd met een onbekende rol, uw rechten zijn beperkt."}
                    {/if}
                <div id="rolemessage">
                    <h2>{$rolename}</h2>
                    <p>{$rolemessage}</p>
                </div>
            {*<form action="index.php" id="start">
                <div id="inputelems">
                    <div class="formelem">
                        <p><strong>Kies uw boek:</strong></p>
                        <select name="book_id" id="book_id" class="radius-small">
                            {foreach from=$books item=book}
                                {if $book->humanName != ''}
                                    <option value="{$book->id}">{$book->humanName}</option>
                                {else}
                                    <option value="{$book->id}">{$book->name}</option>
                                {/if}
                            {/foreach}
                        </select>
                        </div>
                    <div class="formelem" id="pageselect">
                        <label for="pagenr"><strong>Pagina nummer:</strong></label><br />
                        <input type="text" value="107" id="pagenr" name="pagenr" class="radius-small" /><br />
                    </div>
                <div class="clear"></div>
                </div>
                <div>
                    <input type="submit" id="submitbookrequest" value="Start" />
                </div>
                
            </form>*}

            <form action="index.php" id="start">
                <div id="inputelems">
                    <div class="formelem">
                        <p><strong>Kies uw pagina</strong></p>
                        <select name="book_id" id="book_id" class="radius-small">
                        {html_options options=$pages}
                        </select>
                    </div>
                </div>
                <div>
                    <input type="submit" id="submitbookrequest" value="Start" />
                </div>
            </form>
            <p>&nbsp;</p>
            {if count($trainFiles) > 0}
            <form action="index.php" name="control" id="control">
                <div id="inputelem">
                    <div class="formelem">
                        <p><strong>Kies boek ter controle</strong></p>
                            <select name="cBook_id" id="cBook_id" class="radius-small">
                                {*<option value="navis-NL_HaNa_H2_7823_0132">Minne: navis-NL_HaNa_H2_7823_0132</option>*}
                                {html_options options=$trainFiles}
                            </select>
                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <input type="submit" id="submitcontrolrequest" value="Check" />
                </div>
            </form>
            {/if}
            {literal}
            <script type="text/javascript">
                $(document).ready(function(){
                    $("form#start").submit(function() {
//                        window.location = sprintf('http://217.21.192.132/monkui/monkswork/?cmd=showtranscribe&page=%s_%04d', $('#book_id').val(), parseInt($('#pagenr').val()));
//                        window.location = sprintf('http://localhost/transcribe/?cmd=transcribe&page=%s_%04d', $('#book_id').val(), parseInt($('#pagenr').val()));
//                        window.location = sprintf('http://localhost/transcribe/?cmd=transcribe&page=%s_%04d&bookid=%s', $('#book_id').val(), parseInt($('#pagenr').val()), $('#book_id').val());
//                        window.location = sprintf('http://localhost/transcribe/?cmd=transcribe&page=%d&bookid=%s', $('#book_id').val(), parseInt($('#pagenr').val()), $('#book_id').val());
                        window.location = sprintf('http://s4aaas.target-imedia.nl/ui/?cmd=transcribe&bookid=%s', $('#book_id').val());
                        return false;
                    });

                    $("form#control").submit(function() {
//                        window.location = sprintf('http://217.21.192.132/monkui/monkswork/?cmd=showtranscribe&page=%s_%04d', $('#book_id').val(), parseInt($('#pagenr').val()));
//                        window.location = sprintf('http://localhost/transcribe2/?cmd=loadtranscribe&page=%s_%04d', $('#cBook_id').val(), parseInt($('#pagenr').val()));
                        window.location = sprintf('http://s4aaas.target-imedia.nl/ui/?cmd=transcribe&page=%s', $('#cBook_id').val());
                        return false;
                    });
                });
            </script>
            {/literal}

        </div> <!-- #main -->
	</div> <!-- #container -->

	{*<div id='footer'>*}
		{*<img src='css/images/logo/nationaal-archief.jpg' alt='Nationaal Archief' /> *}
		{*<img src='css/images/logo/targetlogo.png' alt='Target' /> *}
		{*<img src='css/images/logo/rug.png' alt='RUG' /> *}
		{*<img src='css/images/logo/ontwikkel-logo.png' alt='Ontwikkelfabriek' />   *}
		{*<img src='css/images/logo/logo-NWO.jpg' alt='NWO' />*}
	{*</div> <!-- #footer -->*}
    {include file="footer.tpl"}
</body>
</html>