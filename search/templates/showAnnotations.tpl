{foreach from=$annotations->$type key=k item=v}
<div class='image-holder radius'>
    <div class='links'>
        <a href='{$v->marked_wordzone}'>toon pagina</a>
    </div>
    <div class='shadow'>
        <div class='loader'>loading</div>
            <span class='hidden'>{$imgurl}/navis-{$v->book_id}/Pages/{$v->book_id}_{if $v->page < 1000}0{/if}{if $v->page < 100}0{/if}{if $v->page < 10}0{/if}{$v->page}/Lines/web-grey/navis-{$v->book_id}_{if $v->page < 1000}0{/if}{if $v->page < 100}0{/if}{if $v->page < 10}0{/if}{$v->page}-line-{if $v->line < 100}0{/if}{if $v->line < 10}0{/if}{$v->line}-y1={$v->y1}-y2={$v->y2}.jpg</span>
            <div class='json'>
{
    "x": {$v->x},
    "y": {$v->y},
    "h": {$v->h},
    "w": {$v->w},
    "url": "{$imgurl}/navis2-{$v->book_id}/Pages/{$v->book_id}_{if $v->page < 1000}0{/if}{if $v->page < 100}0{/if}{if $v->page < 10}0{/if}{$v->page}/Lines/web-grey/navis-{$v->book_id}_{if $v->page < 1000}0{/if}{if $v->page < 100}0{/if}{if $v->page < 10}0{/if}{$v->page}-line-{if $v->line < 100}0{/if}{if $v->line < 10}0{/if}{$v->line}-y1={$v->y1}-y2={$v->y2}.jpg"
}
            </div>
    
    </div>
    

    <div class='info'>
        {counter assign=count}
        <span class='counter'>#{$count + $offset}</span>
        <span class='institution'>Instituut: {$v->institution}</span>
        <span class='collection'>Collectie: {$v->collection}</span>
        <span class='book'>Boek: {$v->shortname}</span>
        <span class='needle'>Zoekterm: {$searchTerm}</span>
        <span class='completeword'>Gevonden in woord: {$v->txt}</span>
        <span class='page'>Blz: {$v->page}, regel {$v->line}</span>
        <span class='type'>Type: {$type}</span>
    </div> <!-- info -->
</div> <!-- .image-holder -->
{/foreach}