{foreach from=$annotations->$type key=k item=v}
{assign var="theTypeEnd" value=$type|stripos:"_"}
{assign var="theType" value=$type|substr:0:$theTypeEnd}
<div class='image-holder radius'>
    <div class='links'>
        {assign var="pipe" value=false}
{*        {if $v->marked_wordzone != ''}*}
{*            <a href='{$v->marked_wordzone}' class='showpage'>toon pagina1</a>*}
{*            {assign var="pipe" value=true}*}
{*        {elseif $v->file_page_path != ''}*}
            <a href='{$v->file_page_path}' class='showpage'>toon pagina</a>
            {assign var="pipe" value=true}
{*        {/if}*}
        {if $v->metadata != ''}
            {if $pipe == true}| {/if}
            <span class='metadata'><a href="{$v->metadata}" onclick='window.open(this.href);return false;'>Broninformatie</a></span>
        {/if}
    </div>
    {if isset($v->annotated_page)}
    <div class="title center">Pagina titel: <span>{$v->annotated_page}</span></div>
    <div><br /></div>
    {/if}
    {assign var='begin' value=$v->file_page_path|strpos:"/monk/"}
    {assign var='end' value=$v->file_page_path|strpos:"/Jpeg/"}
    {$begin = $begin + 6}
    {assign var='length' value=$end - $begin}
    {assign var='bookIdIndex' value=$v->file_page_path|substr:$begin:$length}
    <div class='shadow'>
        <div class='loader'>loading</div>
            <span class='hidden'></span>
            {*<span class='hidden'>{$v->marked_wordzone}</span>*}
            <div class='json'>{*{if $type == 'wordzone_annotation'}*}
{
    "x": {if ($v->x|ltrim:'0') == ''}0{elseif ($v->file_line_path_factor) != ''}{$v->x|string_format:"%d" * $v->file_line_path_factor|string_format:"%f"}{else}{$v->x|ltrim:'0'}{/if},
    "y": {if ($v->y|ltrim:'0') == ''}0{elseif ($v->file_line_path_factor) != ''}{$v->y|string_format:"%d" * $v->file_line_path_factor|string_format:"%f"}{else}{$v->y|ltrim:'0'}{/if},
    "h": {if ($v->h|ltrim:'0') == ''}0{elseif ($v->file_line_path_factor) != ''}{$v->h|string_format:"%d" * $v->file_line_path_factor|string_format:"%f"}{else}{$v->h|ltrim:'0'}{/if},
    "w": {if ($v->w|ltrim:'0') == ''}0{elseif ($v->file_line_path_factor) != ''}{$v->w|string_format:"%d" * $v->file_line_path_factor|string_format:"%f"}{else}{$v->w|ltrim:'0'}{/if},
    "url": "{$v->file_line_path}"

    
}
{*"url": "{$v->file_page_path|replace:'/Jpeg/':'/Pages/'}/Lines/web-grey/{$BookIdLookup[$bookIdIndex]|sprintf:$v->page:$v->line}-y1={$v->y1}-y2={$v->y2}.jpg"*}


{*{else}
{
    "x": -1,
    "y": -1,
    "h": -1,
    "w": -1,
    "url": "{$no_image}"
}
{/if}*}
            </div>
    
    </div>
    

    <div class='info'>
        {counter assign=count}
        {if isset($v->annotated_line)}
        <div class="annotated_line center">{$v->annotated_line}</div>
        {/if}
        <div><br /></div>
        <span class='counter'>#{$count + $offset}</span>
        <span class='institution'>Instituut: {$v->institution}</span>
        <span class='collection'>Collectie: {$v->collection}</span>
        <span class='book'>Boek: {$v->shortname}</span>
        <span class='page'>Blz: {$v->page}, regel {$v->line}</span>
        <span class='type'>Gevonden in: {$types.$theType}</span>
        {*<span class='type'>Type: {$type}</span>*}
        <br />
        <span class='needle'>Zoekterm: {$searchTerm}</span>
        <span class='completeword'>Gevonden in woord: {$v->txt}</span>
    </div> <!-- info -->
</div> <!-- .image-holder -->
{/foreach}