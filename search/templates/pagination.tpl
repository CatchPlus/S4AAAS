<div class='pag'>
    
    
{if ( $send < $total )} 
    {* set begin *}
    <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset=0" class="large radius-small">Begin</a>
    {* set rest of pages *}
    {for $page=1 to $nrPages}
        <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($page-1)*$rows}" class="radius-small{if (($offset / $rows) + 1) == $page} current{/if}">{$page}</a>
    {/for}
    <a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={$total-$rows}" class="large radius-small{if ($offset == ($total - $rows))} current{/if}">Einde</a>
{/if}
<div class="clear"></div>
</div> <!-- pag -->