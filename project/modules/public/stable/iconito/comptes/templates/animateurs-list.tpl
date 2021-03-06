<p class="right"><a href="{copixurl dest="comptes|animateurs|new"}" class="button button-add">{i18n key="comptes.menu.new_animateur" noEscape=1}</a></p>

{if $ppo->animateurs}
	<table class="viewItems liste comptes_animateurs comptes_animateurs_list">
	<tr>
		<th class="liste_th">Identifiant</th>
		<th class="liste_th">Nom</th>
		<th class="liste_th">Pr&eacute;nom</th>
		<th class="liste_th extraSmall" ><img src="{copixresource path="img/comptes/comptes_animateurs_connexion.png"}"/></th>
		<th class="liste_th extraSmall"><img src="{copixresource path="img/comptes/comptes_animateurs_tableaubord.png"}"/></th>
		<th class="liste_th extraSmall"><img src="{copixresource path="img/comptes/comptes_animateurs_gestioncomptes.png"}"/></th>
		<th class="liste_th extraSmall"><img src="{copixresource path="img/comptes/comptes_animateurs_visibleannuaire.png"}"/></th>
		
		<th class="liste_th">Groupes de villes</th>
		<th class="liste_th">Groupes d'&eacute;coles</th>
		
		<th class="liste_th">Actions</th>
	</tr>
	{assign var=index value=0}
	{foreach from=$ppo->animateurs item=animateur name=animateurs}
	    <tr class="{if $index%2 eq 0}odd{else}even{/if}">
			<td>{$animateur->user_infos.login}</td>
			<td>{$animateur->user_infos.nom}</td>
			<td>{$animateur->user_infos.prenom}</td>
			
			<td class="center">{if $animateur->can_connect}X{/if}</td>
			<td class="center">{if $animateur->can_tableaubord}X{/if}</td>
			<td class="center">{if $animateur->can_comptes}X{/if}</td>
			<td class="center">{if $animateur->is_visibleannuaire}X{/if}</td>
			<td>
				{foreach from=$animateur->regroupements->grvilles item=grville name=grvilles}{if ! $smarty.foreach.grvilles.first}, {/if}{$grville->nom}{/foreach}
			</td>
			<td>
				{foreach from=$animateur->regroupements->grecoles item=grecole name=grecoles}{if ! $smarty.foreach.grecoles.first}, {/if}{$grecole->nom}{/foreach}
			</td>
			    
			<td><a class="button button-update" href="{copixurl dest="comptes|animateurs|edit" user_type=$animateur->user_type user_id=$animateur->user_id}">modifier</a></td>
		</tr>
		{assign var=index value=$index+1}
	{/foreach}
	</table>
{else}
	<i>Aucun animateur...</i>
{/if}
