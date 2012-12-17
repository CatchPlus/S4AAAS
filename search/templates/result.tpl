{include file="header.tpl"}
<body id='rest'>
    	<noscript>
		<p>javascript required</p>
	</noscript>
          {if $tokenIsSet == true}
          {include file="topmenu_auth.tpl"}
          {/if}
          {if $tokenIsSet == false}
          {include file="topmenu.tpl"}
          {/if}
        <div>
		<a class='header' href='index.php' title='Home'></a>
	</div>

	<div class='container'>
		<div class='nav'>
                    {* set some values, default if not set *}
                    {if isset($smarty.get.offset)}
                        {assign var=offset value=$smarty.get.offset}
                    {else}
                        {assign var=offset value=0}
                    {/if}
                    {if isset($smarty.get.match)}
                        {assign var=match value=$smarty.get.match}
                    {else}
                        {assign var=match value='prefix'}
                    {/if}
                    
                    {if $total == 0}
                        <p>Er zijn <span>geen</span> resultaten voor <span>{$searchTerm}</span></p>
                    {elseif $total == 1}
                        <p>Er is <span>{$total}</span> resultaat voor <span>{$searchTerm}</span></p>
                    {else}
                        <p>Er zijn <span>{$total}</span> resultaten voor <span>{$searchTerm}</span></p>
                     {/if}
                        <div class='search'>
			<form id='minisearchform' action=''>
                            <div>
                                <input type='text' id='needle' class='radius-small' name='needle' value='{if isset($smarty.get.needle)}{$smarty.get.needle}{/if}' />
                                <input type='button' id='send' name='send' value='Zoeken' onclick="form.submit()" />
                                {if isset($smarty.get.institutions)}
                                {foreach from=$smarty.get.institutions item=institution}
                                <input type="hidden" name="institutions[]" value="{$institution}" />
                                {/foreach}
                                {/if}
                                {if isset($smarty.get.collections)}
                                
                                {foreach from=$smarty.get.collections item=collection}
                                <input type="hidden" name="collections[]" value="{$collection}" />
                                {/foreach}
                                {/if}
                                {if isset($smarty.get.books)}
                                {foreach from=$smarty.get.books item=book}
                                <input type="hidden" name="books[]" value="{$book}" />
                                {/foreach}
                                {/if}
                                {if isset($smarty.get.annotations)}
                                {foreach from=$smarty.get.annotations item=annotation}
                                <input type="hidden" name="annotations[]" value="{$annotation}" />
                                {/foreach}
                                {/if}
                                {if isset($smarty.get.wordzonetypes)}
                                {foreach from=$smarty.get.wordzonetypes item=wordzonetype}
                                <input type="hidden" name="wordzonetypes[]" value="{$wordzonetype}" />
                                {/foreach}
                                {/if}
                                <input type="hidden" name="offset" value="0" />
                                {if isset($smarty.get.match)}
                                <input type="hidden" name="match" value="{$smarty.get.match}" />
                                {/if}
                            </div>
			</form>
                    </div>
                    {include file='pagination-v3.tpl'}
                    </div> <!-- .nav -->
                    
                    
                    <div class="results">
                        {* use counter to print result number *}
                        {counter start=0 print=false}
                        {* print the results *}
                        {include file='showAnnotationsV2.tpl' annotations=$wordzoneAnnotations offset=$offset type='wordzone_annotation'}
                        {include file='showAnnotationsV2.tpl' annotations=$lineAnnotations offset=$offset type='line_annotation'}
                        {include file='showAnnotationsV2.tpl' annotations=$pageAnnotations offset=$offset type='page_annotation'}
                    </div> <!-- .results-->
                    {* put some navigation at bottom too *}
                    {include file='pagination-v3.tpl'}
                    <form method="get" action="" id="changeRows">
                        {* set current vars as hidden fields *}
                        <div id="vars">
                            {if isset($smarty.get.needle)}
                            <input type="hidden" name="needle" value="{$searchTerm}" />
                            {/if}
                            {if isset($smarty.get.institutions)}
                            {foreach from=$smarty.get.institutions item=institution}
                            <input type="hidden" name="institutions[]" value="{$institution}" />
                            {/foreach}
                            {/if}
                            {if isset($smarty.get.collections)}
                            {foreach from=$smarty.get.collections item=collection}
                            <input type="hidden" name="collections[]" value="{$collection}" />
                            {/foreach}
                            {/if}
                            {if isset($smarty.get.books)}
                            {foreach from=$smarty.get.books item=book}
                            <input type="hidden" name="books[]" value="{$book}" />
                            {/foreach}
                            {/if}
                            {if isset($smarty.get.annotations)}
                            {foreach from=$smarty.get.annotations item=annotation}
                            <input type="hidden" name="annotations[]" value="{$annotation}" />
                            {/foreach}
                            {/if}
                            {if isset($smarty.get.wordzonetypes)}
                            {foreach from=$smarty.get.wordzonetypes item=wordzonetype}
                            <input type="hidden" name="wordzonetypes[]" value="{$wordzonetype}" />
                            {/foreach}
                            {/if}
                            {if isset($smarty.get.offset)}
                            <input type="hidden" name="offset" value="{$smarty.get.offset}" />
                            {/if}
                            {if isset($smarty.get.match)}
                            <input type="hidden" name="match" value="{$smarty.get.match}" />
                            {/if}
                            
                            
                        </div> <!-- .vars -->
                        <div class='nav-bottom'>
                                Items per pagina: {html_options name='rows' id='rows' options=$pageschoice selected=$rows onchange="document.forms['changeRows'].submit()"}
                        </div> <!-- .nav-bottom -->
                    </form>
              </div> <!-- container -->
	{include file="footer.tpl"}