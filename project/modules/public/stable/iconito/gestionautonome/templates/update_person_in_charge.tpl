<h2>Modification d'un responsable</h2>

<p>Ce formulaire vous permet de modifier le responsable d'un élève.</p>

<h3>Eleve</h3>

<div class="field">
  <label for="student_name"> Nom :</label>
  <span>{$ppo->student->ele_nom}</span>
</div>

<div class="field">
  <label for="student_firstname"> Prénom :</label>
  <span>{$ppo->student->ele_prenom1}</span>
</div>

<div class="field">
  <label for="student_login"> Login :</label>
  <span>{$ppo->account->login_dbuser}</span>
</div>

<h3>Responsable</h3>

{if not $ppo->errors eq null}
	<div class="message_erreur">
	  <ul>
	    {foreach from=$ppo->errors item=error}
		    <li>{$error}</li><br \>
	    {/foreach}
	  </ul>
	</div>
{/if}

<form name="person_update" id="person_update" action="{copixurl dest="|validatePersonInChargeUpdate"}" method="POST" enctype="multipart/form-data">
  <fieldset>
    <input type="hidden" name="id_node" id="id-node" value="{$ppo->nodeId}" />
    <input type="hidden" name="type_node" id="type-node" value="{$ppo->nodeType}" />
    <input type="hidden" name="id_student" id="id-student" value="{$ppo->student->ele_idEleve}" />
    <input type="hidden" name="id_person" id="id-person" value="{$ppo->person->numero}" />

    <div class="field">
      <label for="nom" class="form_libelle"> Nom :</label>
      <input class="form" type="text" name="nom" id="nom" value="{$ppo->person->nom}" />
    </div>
    
    <div class="field">
      <label for="prenom1" class="form_libelle"> Prénom :</label>
      <input class="form" type="text" name="prenom1" id="prenom1" value="{$ppo->person->prenom1}" />
    </div>

    <div class="field">
      <label for="id_par" class="form_libelle"> Relation avec l'élève :</label>
      <select class="form" name="id_par" id="id_par">
        {html_options values=$ppo->linkIds output=$ppo->linkNames selected=$ppo->res2ele->res2ele_id_par}
  	  </select>
    </div>
  </fieldset>
  
  <ul class="actions">
    <li><input class="button" type="button" value="Annuler" id="cancel" /></li>
  	<li><input class="button" type="submit" name="save" id="save" value="Enregistrer" /></li>
  </ul>
</form>

{literal}
<script type="text/javascript">
//<![CDATA[
  
  jQuery.noConflict();
  
  jQuery(document).ready(function(){
 	
 	  jQuery('.button').button();
  });
  
  jQuery('#cancel').click(function() {
    
    document.location.href={/literal}'{copixurl dest=gestionautonome||updateStudent nodeId=$ppo->nodeId nodeType=$ppo->nodeType studentId=$ppo->student->ele_idEleve notxml=true}'{literal};
  });
//]]> 
</script>
{/literal}