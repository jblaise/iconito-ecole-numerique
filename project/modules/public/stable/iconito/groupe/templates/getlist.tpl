<link rel="stylesheet" type="text/css" href="{copixresource path="styles/module_groupe.css"}" />
{if !empty($kw)}
<div class="floatleft">
    <a href="{copixurl dest="|getListPublic"}" class="button button-back"><em>{i18n key="groupe.list.back"}</em></a>
</div>
{/if}
<div class="" align="right">


<form action="{copixurl dest="|getListPublic"}" method="get">
{i18n key="groupe.search"} :
<input type="text" name="kw" class="form" style="width: 120px;" value="{$kw}" />
<input type="submit" value="{i18n key="groupe.searchSubmit"}" class="button button-confirm" />


</form>




</div>

	{if $list neq null}

<div id="groups">

    {if !empty($tagsCloud)}<br />
    <div class="content-panel">
	{foreach from=$tagsCloud item=tag}
	    {$tag} 
	{/foreach}
    </div>
    {/if}

{foreach from=$list item=groupe}
<div class="body">

			<div class="actions">
			{* {if $groupe->canViewHome}<a class="home" href="{copixurl dest="|getHome" id=$groupe->id}">{i18n key="groupe.group.home"}</a>{/if} *}
			{if !$groupe->mondroit }<a class="subscribe" href="{copixurl dest="|doJoin" id=$groupe->id}">{i18n key="groupe.group.join"}</a>{/if}
			{if $groupe->canAdmin }<a class="admin" href="{copixurl dest="|getHomeAdmin" id=$groupe->id}">{i18n key="groupe.group.admin"}</a>{/if}
			{if $groupe->blog}<a class="blog" href="{copixurl dest="blog||listArticle" blog=$groupe->blog->url_blog}">{i18n key="groupe.group.blogView"}</a>{/if}
			</div>


			<div class="titleb">{if 0 && $groupe->canViewHome}<a href="{copixurl dest="|getHome" id=$groupe->id}">{$groupe->titre}</a>{else}{$groupe->titre}{/if}</div>
			{$groupe->description}
                        {if !empty($groupe->tags)}<p>{$groupe->tags}</p>{/if}
			<div class="infos">
			{i18n key="groupe.creation" nb=$groupe->date_creation|datei18n:"date_short" who=""} {user label=$groupe->createur_nom userType=$groupe->createur_infos.type userId=$groupe->createur_infos.id linkAttribs='STYLE="text-decoration:none;"'}
			 - {$groupe->rattachement} - {i18n key="groupe.group.member" pNb=$groupe->inscrits}
			</div>
</div>
{/foreach}

<br clear="all" />
</div>

	{else}

	<i>{i18n key="groupe.noGroup"}</i>

	{/if}

{$reglettepages}
