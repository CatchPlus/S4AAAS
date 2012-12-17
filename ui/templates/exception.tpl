{include file="header_error.tpl"}
<body id="exception">
	<div class='header'></div>
`        <h1>Error</h1>
         <p>Oops, daar ging iets mis</p>
         <p>Foutmelding: {$exceptionMessage}</p>
         <p>Mocht dit zich vaker voordoen, neem dan contact 
            op met <a href="mailto:stefan@deontwikkelfabriek.nl">stefan@deontwikkelfabriek.nl</a></p>
         <p>
             <a href="#" onclick="history.go(-1); return false;">Terug</a>
         </p>
	</div>
{include file="footer.tpl"}