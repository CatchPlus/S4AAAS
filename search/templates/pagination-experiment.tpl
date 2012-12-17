<div class='pag'>
    
{if ( $send < $total )} 
    {* set begin *}
    <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset=0" class="large radius-small">Begin</a>
    {* set rest of pages *}
    {assign var=start value=1}
    {if ((($offset/$rows) + 1)%15 > 0)}
    {assign var=start value=max(1, ((($offset/$rows) + 1) - 10))}
    {/if}
    
    {assign var=pages value=max($nrPages, $maxPagination)}
    {assign var=end value=min(($nrPages - $maxEndPagination), $maxPagination)}
    {if $end <= $maxEndPagination}
    {assign var=showPages value=$nrPages + $start}
    {else}
    {assign var=showPages value=$maxPagination + $start}
    {/if}
    
    {for $page=$start to $showPages}
        <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($page-1)*$rows}" class="radius-small{if (($offset / $rows) + 1) == $page} current{/if}">{$page}</a>
    {/for}
    {* show last N pages, only if $end > $maxEndPagination *}
    {if $maxEndPagination < $end}
    <a href="#" class='break'>...</a>
        {for $page=($nrPages - $maxEndPagination) to $nrPages}
            <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($page-1)*$rows}" class="radius-small{if (($offset / $rows) + 1) == $page} current{/if}">{$page}</a>        
        {/for}
    {/if}            
    
    <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={$total-$rows}" class="large radius-small{if ($offset == ($total - $rows))} current{/if}">Einde</a>
{/if}
<div class="clear"></div>
</div> <!-- pag -->