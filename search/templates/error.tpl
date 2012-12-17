{include file="header_error.tpl"}
<body id="error">
	<div class='header'></div>
`        <h1>Error: 404 - {$errormessage|default: 'Pagina niet gevonden'}</h1>
         <p>Oops, pagina niet gevonden</p>
         <p>
            Geen paniek, gewoon <a href="#" onclick="history.go(-1); return false;">terug</a> gaan.
         </p>
	</div>
{include file="footer.tpl"}