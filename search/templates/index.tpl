{include file="header_index.tpl"}
<body id="search">
{if $tokenIsSet == true}
{include file="topmenu_auth.tpl"}
{/if}
{if $tokenIsSet == false}
{include file="topmenu.tpl"}
{/if}
BLA!
    <div class='header'>
	</div>

	<div class='container radius shadow'>

		<div class='book'></div>

		<div class='inner radius-small'>
			<form id='form' method='get' action=''>
			<div>
				<input type='text' id='needle' name='needle' value='' class='radius-small' />
				{*<input type='button' id='send' name='send' value='Zoeken' onclick="form.submit()" />*}
				<input type='button' id='send' name='send' value='Zoeken' />

                                <br />
                                <p id="settingshead">>> Boeken selecteren (standaard alle boeken)</p>
                                <div id='settings'>
                                    <h2>Selecteer de instituten/collecties/boeken waarin u wilt zoeken.
                                        <a href="{$wikiurl}#{$anchors.bookCollection}" onclick="window.open(this.href);return false;"><img src="images/Info-icon.png" alt="Info" title="Markeer uw voorkeur" id="infoCollectie"/></a></h2>

                                    <div id="tree"> </div>

                                    {*<fieldset id='institutionfieldset'>
                                        <legend>Instituten</legend>
                                        <input type="checkbox" name="institutions[]" value="all" checked="checked" id="institutions" />Alle instituten<br />
                                        {html_checkboxes class="institution" name=institutions options=$institutions selected=$defaultInstitution separator='<br />' labels='class="institution"'}
                                    </fieldset>
                                    <fieldset id='collectionfieldset'>    
                                        <legend>Collecties</legend>
                                        <input type="checkbox" name="collections[]" value="all" checked="checked" id="collections" />Alle collecties<br />
                                        {html_checkboxes class="collection" name=collections options=$collections selected=$defaultCollection separator='<br />'}
                                    </fieldset>
                                    *}
                                </div> <!-- #settings -->
                                <div class="clear"></div>
                                <p id="advancedhead">>> Geavanceerde zoekinstellingen</p>
                                <div id="advanced">
                                    <fieldset>
                                        <legend>{$wordzonelabel}</legend>
                                        {html_checkboxes name=wordzonetypes options=$wordzonetypes selected=$defaultWordzoneTypes separator='<br />' class="wordzonetypes"}
                                    </fieldset>
                                    <a href="{$wikiurl}#{$anchors.wordzone}" onclick="window.open(this.href);return false;"><img src="images/Info-icon.png" alt="Info" title="Machine is experimenteel" id="infoWordLabel"/></a>

                                    <fieldset>
                                        <legend>{$annotationlabel}</legend>
                                        {html_checkboxes name=annotations options=$annotation selected=$defaultAnnotations separator='<br />' class="annotations"}
                                    </fieldset>
                                    <a href="{$wikiurl}#{$anchors.annotations}" onclick="window.open(this.href);return false;"><img src="images/Info-icon.png" alt="Info" title="Markeer uw voorkeur" id="infoAnnotation"/></a>

                                    <fieldset>
                                    <legend>{$matchlabel}</legend>
                                    {html_radios name=match options=$matches selected=$defaultMatch separator='<br />' class="match"}
                                    </fieldset>
                                    <a href="{$wikiurl}#{$anchors.match}" onclick="window.open(this.href);return false;"><img src="images/Info-icon.png" alt="Info" title="Kies het deel van het woord dat u zoekt" id="infoMatch"/></a>

                                    {*<fieldset>*}
                                        {*<legend>Search Type</legend>*}
                                        {*<label for="typespeed">Snel</label>*}
                                        {*<input type="radio" name="speed" value="fast" id="typespeed" checked='checked' /><br />*}
                                        {*<label for="typenormal">Normaal</label>*}
                                        {*<input type="radio" name="speed" value="normal" id="typenormal" />*}
                                    {*</fieldset>*}

                                    <div class="clear"></div>
                                </div> <!-- #advanced -->
                                <div id="version">
                                    {$version}
                                </div>

                                {*<div id='booklisting'>
                                    <fieldset id='bookfieldset'>
                                        <legend>Boeken</legend>
                                        <input type="checkbox" name="books[]" value="all" checked="checked" id="books" />Alle boeken<br />
                                        {html_checkboxes class="booky" name=books options=$books selected=$defaultBook separator='<br />'}
                                    </fieldset>
                                </div>*}

                                <div class="clear"></div>
                                
                                
                                <input type="hidden" name="rows" value="10" />
                                <div class="clear"></div>
                <div id="output"></div>
                <noscript><p>U hebt javascript nodig om deze webpagina te kunnen gebruiken</p></noscript>
			</div>
			</form>
		</div>
	</div>
        <script type='text/javascript' src='{$baseurl}js/lib/jquery-1.6.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/jquery-ui-1.8.12.custom.min.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/jquery.cookie.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/jquery.preload-min.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/dynatree/jquery.dynatree.min.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/tooltip/jquery.tooltip.min.js'></script>

    <script type='text/javascript' src='{$baseurl}js/treeview.js'></script>
    <script type='text/javascript' src='{$baseurl}js/parseform.js'></script>
    <script type='text/javascript' src='{$baseurl}js/toggleadvanced.js'></script>
    <script type='text/javascript' src='{$baseurl}js/autocomplete.js'></script>
    <script type='text/javascript' src='{$baseurl}js/lib/of.common.js'></script>
    <script type="text/javascript" src="{$baseurl}js/jquery.autocomplete.js"></script>
{*    <script type='text/javascript' src='{$baseurl}js/init.js'></script>*}
{include file="footer.tpl"}
