<div class='pag'>
{assign var=currentPage value=($offset / $rows) + 1}
current page = {$currentPage}
    
    
{* only show << if there is a reason*}
{if $currentPage > 1}
<a href="{$localurl}/results.php?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={$offset - $rows}" class="large radius-small"><<</a>
{/if}
<div class="clear"></div>
</div> <!-- pag -->