<h2>Ajout d'une année scolaire</h2>

<h3>Année scolaire</h3>

{if not $ppo->errors eq null}
	<div class="message_erreur">
	  <ul>
	    {foreach from=$ppo->errors item=error}
		    <li>{$error}</li><br \>
	    {/foreach}
	  </ul>
	</div>
{/if}

<form name="grade_creation" id="grade_creation" action="{copixurl dest="|validateGradeCreation"}" method="POST" enctype="multipart/form-data">
  <fieldset>
    <div class="field">
      <label for="dateDebut" class="form_libelle"> Date de début :</label>
      <input type="text" id="dateDebut" name="dateDebut" class="form datepicker" value="{$ppo->grade->dateDebut}" />
      
    </div>
    <div class="field">
      <label for="dateFin" class="form_libelle"> Date de fin :</label>
      <input type="text" id="dateFin" name="dateFin" class="form datepicker" value="{$ppo->grade->dateFin}" />
    </div>
    <div class="field">
      <label for="dateFin" class="form_libelle"> Année courante :</label>
      <input class="form" type="checkbox" name="current" id="current" {if $ppo->grade->current} checked=checked{/if}" />
    </div>
</form>

<ul class="actions">
  <li><input class="button" type="button" value="Annuler" id="cancel" /></li>
	<li><input class="button" type="submit" name="save" id="save" value="Enregistrer" /></li>
</ul>

{literal}
<script type="text/javascript">
//<![CDATA[
  
  jQuery.noConflict();
  
  jQuery(document).ready(function() {

    jQuery('.datepicker').datepicker({
    			showOn: 'button',
    			buttonImage: 'images/calendar.gif',
    			buttonImageOnly: true
    });   
    
    jQuery('.button').button();
  });
  
  jQuery('#cancel').click(function() {
    
    document.location.href={/literal}'{copixurl dest=gestionautonome||manageGrades}'{literal};
  });
  
//]]> 
</script>
{/literal}