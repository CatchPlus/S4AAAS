<div class='pag'>
    {if $startPage > 1}
        <a href="?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={0}" class="radius-small large">Begin</a>
{*        <a href="#" class="nolink"> [ ... ] </a>*}
        <a href="?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($currentPage - 2) * $rows}" class="radius-small">&lsaquo;&lsaquo;</a>
    {/if}
    {for $page=$startPage to $endPage}
        <a href="?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($page - 1) * $rows}" class="radius-small{if $currentPage == $page} current{/if}">{$page}</a>
    {/for}
    {if $endPage < $totalPages}
{*        <a href="#" class="nolink"> [ ... ] </a>*}
        <a href="?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($currentPage) * $rows}" class="radius-small">&rsaquo;&rsaquo;</a>
        <a href="?needle={$searchTerm}{$url}&amp;match={$match}&amp;rows={$rows}&amp;offset={($totalPages - 1) * $rows}" class="radius-small large">Eind</a>
    {/if}
    <div class="clear"></div>
</div> <!-- pag -->