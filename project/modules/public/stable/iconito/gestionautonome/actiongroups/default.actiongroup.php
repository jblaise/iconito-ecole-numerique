<?php
/**
* @package  Iconito
* @subpackage Gestionautonome
* @version   $Id: default.actiongroup.php,xxxx $
* @author   xxxxxxx
* @copyright xxxx
* @link     xxxx
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * @author	xxxxx
 */
class ActionGroupDefault extends CopixActionGroup {

	public function beforeAction (){
		_currentUser()->assertCredential ('group:[current_user]');
		                   
    CopixHTMLHeader::addJSLink (_resource ('js/jquery-1.4.2.min.js'));
    CopixHTMLHeader::addJSLink (_resource ('js/jquery-ui-1.8.custom.min.js'));
    CopixHTMLHeader::addJSLink (_resource ('js/jquery.ui.datepicker-fr.js'));
    CopixHTMLHeader::addCSSLink (_resource ('jquery-ui-theme/jquery-ui-1.8.custom.css'));  
		CopixHTMLHeader::addCSSLink (_resource ('styles/module_gestionautonome.css'));
	}
	
	public function processShowTree () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération du noeud ROOT
	  $groupcity = Kernel::getNodeChilds ('ROOT', 0);
	  $groupcity = Kernel::filterNodeList ($groupcity, 'BU_GRVILLE');
    
    $ppo->root = $groupcity[0];
    
    // Y a t-il eu des modifications ?
    $ppo->save = _request ('save', null); 
    
    // Sélection de l'onglet courant
    $ppo->tab = _request ('tab', null);
    
    // Récupération du noeud cible (noeud courant)
    $ppo->targetId   = _request ('nodeId');
    $ppo->targetType = _request ('nodeType');

		return _arPPO ($ppo, 'show_tree.tpl'); 
	}
	
	/**
	 * displayPersonsData (Ajax)
	 *
	 * Récupération des personnes du noeud courant.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processDisplayPersonsData () {

	  $ppo = new CopixPPO ();                                       
	  
	  $id   = _request ('nodeId', null);
	  $type = _request ('nodeType', null);
	  
		echo CopixZone::process ('gestionautonome|PersonsData', array ('nodeId' => $id, 'nodeType' => $type));
    
    return _arNone ();
	}
	
	/**
	 * updateTreeActions (Ajax)
	 *
	 * Récupération des actions disponibles pour le noeud courant.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processUpdateTreeActions () {

	  $ppo = new CopixPPO ();
	  
	  $id   = _request ('nodeId', null);
	  $type = _request ('nodeType', null);
	  
		echo CopixZone::process ('gestionautonome|TreeActions', array ('nodeId' => $id, 'nodeType' => $type));
    
    return _arNone ();
	}
	
	/**
	 * createCity
	 *
	 * Création d'une ville.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processCreateCity () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->parentId    = _request ('nodeId', null);
	  $ppo->parentType  = _request ('nodeType', null);
	  
	  // La création d'une ville n'est possible qu'à partir d'un groupe de ville
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType) || $ppo->parentType != 'BU_GRVILLE') {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => 'Création d\'une ville');
	  
	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'create_city.tpl');
	}
	
	/**
	 * validateCityCreation
	 *
	 * Validation du formulaire de création de ville.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processValidateCityCreation () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->parentId    = _request ('id_parent', null);
	  $ppo->parentType  = _request ('type_parent', null);
	  
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // DAO
	  $cityDAO   = _ioDAO ('kernel|kernel_bu_ville');
	  
    // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => 'Création d\'une ville');

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");    
    
    // Récupération des paramètres
    $cityName  = _request ('nom', null); 
    $ppo->city = _record ('kernel|kernel_bu_ville');

    $ppo->city->nom           = trim ($cityName);
    $ppo->city->canon         = Kernel::createCanon ($cityName);
    $ppo->city->id_grville    = $ppo->parentId;
    $ppo->city->date_creation = CopixDateTime::timestampToYYYYMMDDHHIISS (time ());
    
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->city->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->city->id_grville) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.unknownError');
    }
    
    if (!empty ($ppo->errors)) {
      
      return _arPPO ($ppo, 'create_city.tpl');
    }
      
    // Insertion de la ville
    $cityDAO->insert ($ppo->city);
    
    $ppo->nodeId   = $ppo->city->id_vi;
		$ppo->nodeType = 'BU_VILLE';

		return _arPPO ($ppo, array ('template' => 'create_success.tpl', 'mainTemplate' => null));
  }
  
  /**
	 * updateCity
	 *
	 * Edition d'une ville.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processUpdateCity () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // DAO
	  $cityDAO   = _ioDAO ('kernel_bu_ville');
	  
	  // Récupération de la ville
	  $ppo->city = $cityDAO->get ($ppo->nodeId);
	  
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $ppo->city->nom);
	  
	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'update_city.tpl');
	}
	
	/**
	 * validateCityUpdate
	 *
	 * Validation du formulaire d'édition de ville.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processValidateCityUpdate () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $cityDAO = _ioDAO ('kernel_bu_ville');
	  
	  $ppo->nodeId   = _request ('id_node', null);
		$ppo->nodeType = _request ('type_node', null);
		
	  // Récupération des paramètres
	  if (!$ppo->city = $cityDAO->get ($ppo->nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $ppo->city->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
      
    // Récupération des paramètres
    $cityName = _request ('name', null);
    
    $ppo->city->nom   = trim ($cityName);
    $ppo->city->canon = strtolower (trim ($cityName));

    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->city->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    
    if (!empty ($ppo->errors)) {
  	  
      return _arPPO ($ppo, 'update_city.tpl');
    }
      
    $cityDAO->update ($ppo->city);
		
		return _arPPO ($ppo, array ('template' => 'update_success.tpl', 'mainTemplate' => null));
	}
	
	/**
	 * deleteVille
	 *
	 * Suppression d'une ville.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processDeleteCity () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $nodeId   = _request ('nodeId', null);
	  $nodeType = _request ('nodeType', null);
	  
	  $cityDAO       = _ioDAO ('kernel_bu_ville');
	  $schoolDAO     = _ioDAO ('kernel|kernel_bu_ecole');
	  $classDAO      = _ioDAO ('kernel|kernel_bu_ecole_classe');
	  $classLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');
	  
	  if ($nodeType != 'BU_VILLE' || !$city = $cityDAO->get ($nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Récupération des écoles de la ville
	  $schools = $schoolDAO->getByCity ($nodeId);
	  
	  foreach ($schools as $school) {

	    // Récupération des classes de la ville
	    $classes = $classDAO->getBySchool ($school->numero);
	    
	    foreach ($classes as $class) {

	      // Récupération des associations classe-niveau
	      $classLevels = $classLevelDAO->getByClass ($class->id);
	      
	      foreach ($classLevels as $classLevel) {
	        
	        // Suppression des associations classe-niveau
  	      $classLevelDAO->delete ($classLevel->classe, $classLevel->niveau);
	      }
	      
	      // Suppression de la classe
	      $classDAO->delete ($class->id);
	    }
	    
	    // Suppression de l'école
	    $schoolDAO->delete ($school->numero);
	  }
	  
	  // Suppression de la ville 
  	$cityDAO->delete ($city->id_vi);
		
		return _arPPO ($ppo, 'update_success.tpl');
	}
	
	/**
	 * createEcole
	 *
	 * Création d'une école.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processCreateSchool () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->parentId    = _request ('parentId', null);
	  $ppo->parentType  = _request ('parentType', null);
	  
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType) || $ppo->parentType != 'BU_VILLE') {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $ppo->types = array ('Maternelle', 'Elémentaire', 'Primaire');
	  
	  $city = _ioDAO ('kernel_bu_ville')->get ($ppo->parentId);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $ppo->parentId, 'nodeType' => $ppo->parentType)));
	  $breadcrumbs[] = array('txt' => 'Création d\'une école');
	  
	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'create_school.tpl');
	}
	
	/**
	 * validateSchoolCreation
	 *
	 * Validation du formulaire de création d'une école.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processValidateSchoolCreation () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->parentId   = _request ('id_parent', null);
	  $ppo->parentType = _request ('type_parent', null);
	  
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
    
    // DAO
    $schoolDAO = _ioDAO ('kernel_bu_ecole');
       
    $city = _ioDAO ('kernel_bu_ville')->get ($ppo->parentId);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $ppo->parentId, 'nodeType' => $ppo->parentType)));
	  $breadcrumbs[] = array('txt' => 'Création d\'une école');

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    // Récupération des paramètres
    $ppo->school = _record ('kernel_bu_ecole');
    
    $ppo->school->type      = _request ('type', null);
    $ppo->school->nom       = trim (_request ('nom', null));
    $ppo->school->id_ville  = $ppo->parentId;

    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->school->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    
    if (!empty ($ppo->errors)) {
      
      $ppo->types = array ('Maternelle', 'Elémentaire', 'Primaire');

      return _arPPO ($ppo, 'create_school.tpl');
    }
    
    $schoolDAO->insert ($ppo->school);
    
    $ppo->nodeId    = $ppo->school->numero;
    $ppo->nodeType  = 'BU_ECOLE';
		
		return _arPPO ($ppo, array ('template' => 'create_success.tpl', 'mainTemplate' => null));
	}
	
	/**
	 * updateSchool
	 *
	 * Edition d'une école.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processUpdateSchool () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  
    $schoolDAO  = _ioDAO ('kernel_bu_ecole');
    
	  if (!$ppo->school = $schoolDAO->get ($ppo->nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $ppo->cityNames = array ();
	  $ppo->cityIds   = array ();
	  
	  // Récupération des villes pour select
    $cityDAO = _ioDAO ('kernel_bu_ville');
	  $cities  = $cityDAO->findAll ();
	  
    foreach ($cities as $city) {
      
      $ppo->cityNames[] = $city->nom;
      $ppo->cityIds[]   = $city->id_vi;
    }
    
    // Liste des types d'école
	  $ppo->types = array ('Maternelle', 'Elémentaire', 'Primaire');
	  
	  $city = _ioDAO ('kernel_bu_ville')->get ($ppo->nodeId);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $ppo->school->nom);
	  
	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'update_school.tpl');
	}
	
	/**
	 * validateSchoolUpdate
	 *
	 * Validation du formulaire d'édition d'une école.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processValidateSchoolUpdate () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_node', null);
	  $ppo->nodeType = _request ('type_node', null);
	  
	  // DAO
	  $schoolDAO = _ioDAO ('kernel_bu_ecole');
	  
	  // Récupération de l'école
	  if (!$ppo->school = $schoolDAO->get ($ppo->nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
 
    $city = _ioDAO ('kernel_bu_ville')->get ($ppo->nodeId);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $ppo->school->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    // Récupération des paramètres
    $ppo->school->type      = _request ('type', null);
    $ppo->school->nom       = trim (_request ('nom', null));
    $ppo->school->id_ville  = _request ('ville', null);
		
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->school->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    
    if (!empty ($ppo->errors)) {
      
      $ppo->cityNames = array ();
  	  $ppo->cityIds   = array ();

  	  // Récupération des villes pour select
      $cityDAO = _ioDAO ('kernel_bu_ville');
  	  $cities  = $cityDAO->findAll ();

      foreach ($cities as $city) {

        $ppo->cityNames[] = $city->nom;
        $ppo->cityIds[]   = $city->id_vi;
      }
      
      $ppo->types = array ('Maternelle', 'Elémentaire', 'Primaire');
      
      return _arPPO ($ppo, 'update_school.tpl');
    }
      
    $schoolDAO->update ($ppo->school);
    
		return _arPPO ($ppo, array ('template' => 'update_success.tpl', 'mainTemplate' => null));
	}
	
	public function processDeleteSchool () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);

	  $schoolDAO     = _ioDAO ('kernel_bu_ecole');
	  $classDAO      = _ioDAO ('kernel|kernel_bu_ecole_classe');
	  $classLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');

	  if ($ppo->nodeType != 'BU_ECOLE' || !$school = $schoolDAO->get ($ppo->nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Récupération des classes de l'école
	  $classes = $classDAO->getBySchool ($school->numero);
	    
	  foreach ($classes as $class) {

	    // Récupération de l'association classe-niveau
	    $classLevels = $classLevelDAO->getByClass ($class->id);
	    
	    foreach ($classLevels as $classLevel) {
	      
	      $classLevelDAO->delete ($classLevel->classe, $classLevel->niveau);
	    }
	      
	    // Suppression de la classe
	    $classDAO->delete ($class->id);
	  }
	    
	  // Suppression de l'école
	  $schoolDAO->delete ($school->numero);

		return _arPPO ($ppo, 'update_success.tpl');
	}
	
	/**
	 * createClass
	 *
	 * Création d'une classe.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processCreateClass () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->parentId   = _request ('parentId', null);
	  $ppo->parentType = _request ('parentType', null);
	  
	  // Récupération des paramètres
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType) || $ppo->parentType != 'BU_ECOLE') {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
		
		// Récupération des niveaux de classe
		$classLevelDAO = _ioDAO ('kernel_bu_classe_niveau');     
    $levels  = $classLevelDAO->findAll ();
    
    $ppo->levelNames = array ();
		$ppo->levelIds   = array ();
		
    foreach ($levels as $level) {
      
      $ppo->levelNames[] = $level->niveau_court;
      $ppo->levelIds[]   = $level->id_n;
    }
    
    // Récupération des types de classe
    $classTypeDAO = _ioDAO ('kernel_bu_classe_type');
    $types        = $classTypeDAO->findAll ();
    
    $ppo->typeNames = array ();
    $ppo->typeIds   = array ();
    
    foreach ($types as $type) {
      
      $ppo->typeNames[] = $type->type_classe;
      $ppo->typeIds[]   = $type->id_tycla;
    }

    $school = _ioDAO ('kernel_bu_ecole')->get ($ppo->parentId);
    $city = _ioDAO ('kernel_bu_ville')->get ($school->id_ville);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $school->nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $school->numero, 'nodeType' => 'BU_ECOLE')));
	  $breadcrumbs[] = array('txt' => 'Création d\'une classe');

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'create_class.tpl');
	}
	
	/**
	 * validateClassCreation
	 *
	 * Validation du formulaire de création d'une classe.
	 * @author	xxxxx
	 * @since	xxxx
	 * 
	 */
	public function processValidateClassCreation () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->parentId   = _request ('id_parent', null);
	  $ppo->parentType = _request ('type_parent', null);
	  
	  if (is_null ($ppo->parentId) || is_null ($ppo->parentType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
    
    // DAO
    $schoolClassDAO       = _ioDAO ('kernel_bu_ecole_classe');
    $schoolClassLevelDAO  = _ioDAO ('kernel_bu_ecole_classe_niveau');
    
    $ppo->levels  = _request ('levels', null);
    $ppo->type    = _request ('type', null);
   
    $school = _ioDAO ('kernel_bu_ecole')->get ($ppo->parentId);
    $city = _ioDAO ('kernel_bu_ville')->get ($school->id_ville);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $school->nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $school->numero, 'nodeType' => 'BU_ECOLE')));
	  $breadcrumbs[] = array('txt' => 'Création d\'une classe');

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    // Insertion de la classe
    $ppo->class = _record ('kernel_bu_ecole_classe');

    $ppo->class->ecole        = $ppo->parentId;
    $ppo->class->nom          = trim (_request ('nom', null));
    $ppo->class->annee_scol   = Kernel::getAnneeScolaireCourante ()->id_as;
    $ppo->class->is_validee   = 1;
    $ppo->class->is_supprimee = 0;

    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->class->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    
    if (!$ppo->levels) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.selectLevel');
    }
    
    if (!empty ($ppo->errors)) {
      
      // Récupération des niveaux de classe
  		$classLevelDAO = _ioDAO ('kernel_bu_classe_niveau');     
      $levels  = $classLevelDAO->findAll ();
      
      $ppo->levelNames = array ();
      $ppo->levelIds   = array ();
      
      foreach ($levels as $level) {

        $ppo->levelNames[] = $level->niveau_court;
        $ppo->levelIds[]   = $level->id_n;
      }

      // Récupération des types de classe
      $classTypeDAO   = _ioDAO ('kernel_bu_classe_type');
      $types     = $classTypeDAO->findAll ();
      
      $ppo->typeNames = array ();
      $ppo->typeIds   = array ();
      
      foreach ($types as $type) {

        $ppo->typeNames[] = $type->type_classe;
        $ppo->typeIds[]   = $type->id_tycla;
      }

      return _arPPO ($ppo, 'create_class.tpl');
    }

    $schoolClassDAO->insert ($ppo->class);
    
    // Insertion des affectations classe-niveau
    $newSchoolClassLevel = _record ('kernel_bu_ecole_classe_niveau');

    foreach ($ppo->levels as $level) {
      
      $newSchoolClassLevel->classe = $ppo->class->id;
      $newSchoolClassLevel->niveau = $level;
      $newSchoolClassLevel->type   = $ppo->type;

      $schoolClassLevelDAO->insert ($newSchoolClassLevel);
    }	
    
    $ppo->nodeId   = $ppo->class->id;
		$ppo->nodeType = 'BU_CLASSE';
		
		return _arPPO ($ppo, array ('template' => 'create_success.tpl', 'mainTemplate' => null));
	}
	
	public function processUpdateClass () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Récupération de la classe
	  $classDAO = _ioDAO ('kernel_bu_ecole_classe');
	  $ppo->class = $classDAO->get ($ppo->nodeId);

    // Récupération des niveaux de la classe
    $schoolClassLevelDAO = _ioDAO ('kernel|kernel_tree_claniv');
    $schoolClassLevels = $schoolClassLevelDAO->getByClasse ($ppo->nodeId);

    $ppo->levels = array ();
    
    foreach ($schoolClassLevels as $ecn) {
      
      $ppo->levels[] = $ecn->niveau;
      $ppo->type = $ecn->type;
    }

    // Récupération des écoles pour le sélecteur
    $schoolDAO = _ioDAO ('kernel_bu_ecole');
	  $schools = $schoolDAO->findAll ();
	  
	  $ppo->schoolNames = array ();
	  $ppo->schoolIds   = array ();
	  
    foreach ($schools as $school) {
      
      $ppo->schoolNames[] = $school->nom;
      $ppo->schoolIds[]  = $school->numero;
    }
    
    // Récupération des niveaux de classe
    $classLevelDAO = _ioDAO ('kernel_bu_classe_niveau');
	  $levels = $classLevelDAO->findAll ();
	  
	  $ppo->levelNames = array ();
	  $ppo->levelIds   = array ();
	  
    foreach ($levels as $level) {
      
      $ppo->levelNames[] = $level->niveau_court;
      $ppo->levelIds[]   = $level->id_n;
    }
    
    // Récupération des types de classe pour le sélecteur
    $classTypeDAO = _ioDAO ('kernel_bu_classe_type');
    $types = $classTypeDAO->findAll ();
    
    $ppo->typeNames = array ();
    $ppo->typeIds   = array ();
    
	  foreach ($types as $type) {
      
      $ppo->typeNames[] = $type->type_classe;
      $ppo->typeIds[]   = $type->id_tycla;
    }
    
    $school = _ioDAO ('kernel_bu_ecole')->get ($ppo->class->ecole);
    $city   = _ioDAO ('kernel_bu_ville')->get ($school->id_ville);
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $school->nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $school->numero, 'nodeType' => 'BU_ECOLE')));
	  $breadcrumbs[] = array('txt' => $ppo->class->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
		
		return _arPPO ($ppo, 'update_class.tpl');
	}
	
	public function processValidateClassUpdate () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_node', null);
	  $ppo->nodeType = _request ('type_node', null);
	  
	  $classDAO = _ioDAO ('kernel|kernel_bu_ecole_classe');
	  $schoolClassLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');
	  
	  // Récupération de l'école
	  if (!$ppo->class = $classDAO->get ($ppo->nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }

    $school = _ioDAO ('kernel_bu_ecole')->get ($ppo->class->ecole);
    $city   = _ioDAO ('kernel_bu_ville')->get ($school->id_ville);
    
	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $city->id_vi, 'nodeType' => 'BU_VILLE')));
	  $breadcrumbs[] = array('txt' => $school->nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $school->numero, 'nodeType' => 'BU_ECOLE')));
	  $breadcrumbs[] = array('txt' => $ppo->class->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    $ppo->class->ecole  = _request ('ecole', null);
    $ppo->class->nom    = trim (_request ('nom', null));

    // Affectations classe-niveau
    $schoolClassLevel = _record ('kernel|kernel_bu_ecole_classe_niveau');

    $ppo->levels = _request ('niveaux', null);
    $ppo->type   = _request ('type', null);
    
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->class->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    
    if (!$ppo->levels) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.selectLevel');
    }
    
    if (!empty ($ppo->errors)) {

      // Récupération des niveaux de la classe
      $schoolClassLevelDAO = _ioDAO ('kernel|kernel_tree_claniv');
      $schoolClassLevels = $schoolClassLevelDAO->getByClasse ($ppo->nodeId);

      $ppo->levels = array ();
      
      foreach ($schoolClassLevels as $ecn) {

        $ppo->levels[] = $ecn->niveau;
        $ppo->type = $ecn->type;
      }

      // Récupération des écoles pour le sélecteur
      $schoolDAO = _ioDAO ('kernel_bu_ecole');
  	  $schools = $schoolDAO->findAll ();
      
      $ppo->schoolNames = array ();
      $ppo->schoolIds   = array ();
      
      foreach ($schools as $school) {

        $ppo->schoolNames[] = $school->nom;
        $ppo->schoolIds[]  = $school->numero;
      }

      // Récupération des niveaux de classe
      $classLevelDAO = _ioDAO ('kernel_bu_classe_niveau');
  	  $levels = $classLevelDAO->findAll ();

      $ppo->levelNames = array ();
      $ppo->levelIds   = array ();
      
      foreach ($levels as $level) {

        $ppo->levelNames[] = $level->niveau_court;
        $ppo->levelIds[]   = $level->id_n;
      }

      // Récupération des types de classe pour le sélecteur
      $classTypeDAO = _ioDAO ('kernel_bu_classe_type');
      $types = $classTypeDAO->findAll ();

      $ppo->typeNames = array ();
      $ppo->typeIds   = array ();
      
  	  foreach ($types as $type) {

        $ppo->typeNames[] = $type->type_classe;
        $ppo->typeIds[]   = $type->id_tycla;
      }

      return _arPPO ($ppo, 'update_class.tpl');
    }
    
    // Suppression des anciennes affectations
    $oldSchoolClassLevels = $schoolClassLevelDAO->getByClass ($ppo->class->id);
    
    foreach ($oldSchoolClassLevels as $oldSchoolClassLevel) {
      
      $schoolClassLevelDAO->delete ($oldSchoolClassLevel->classe, $oldSchoolClassLevel->niveau);
    }
    
    // Insertions des nouvelles
    foreach ($ppo->levels as $level) {
      
      $newSchoolClassLevel->classe = $ppo->class->id;
      $newSchoolClassLevel->niveau = $level;
      $newSchoolClassLevel->type   = $ppo->type;

      $schoolClassLevelDAO->insert ($newSchoolClassLevel);
    }
    
    $classDAO->update ($ppo->class);
		
		$ppo->targetId   = $ppo->class->id;
		$ppo->targetType = 'BU_CLASSE';

		return _arPPO ($ppo, array ('template' => 'update_success.tpl', 'mainTemplate' => null));
	}
	
	public function processDeleteClass () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $nodeId   = _request ('nodeId', null);
	  $nodeType = _request ('nodeType', null);

	  $classDAO      = _ioDAO ('kernel|kernel_bu_ecole_classe');
	  $classLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');

	  if ($nodeType != 'BU_CLASSE' || !$class = $classDAO->get ($nodeId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Récupération de l'association classe-niveau
    $classLevels = $classLevelDAO->getByClass ($class->id);
    
    foreach ($classLevels as $classLevel) {
      
      $classLevelDAO->delete ($classLevel->classe, $classLevel->niveau);
    }
    
    // TODO : Suppression des affectations élève
	  
	  // Suppression de la classe
	  $classDAO->delete ($class->id);
		
		return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processCreatePersonnel () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('parentId', null);
	  $ppo->nodeType = _request ('parentType', null);
	  $ppo->role     = _request ('role', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
		
		$ppo->genderNames = array ('Homme', 'Femme');
    $ppo->genderIds = array ('0', '1');
    
		$roleDAO = _ioDAO ('kernel_bu_personnel_role');
    $ppo->roleName = $roleDAO->get ($ppo->role)->nom_role;

	  $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);

	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => 'Création d\'un '.$ppo->roleName);


	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");

		return _arPPO ($ppo, 'create_personnel.tpl');
	}
	
	public function processValidatePersonnelCreation () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_parent', null);
	  $ppo->nodeType = _request ('type_parent', null);
	  $ppo->role     = _request ('role', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($ppo->role)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }

		_classInclude ('kernel|Tools');
		
		// DAO
    $personnelDAO       = _ioDAO ('kernel_bu_personnel');
    $personnelEntiteDAO = _ioDAO ('kernel_bu_personnel_entite');
    $dbUserDAO          = _ioDAO ('kernel|kernel_copixuser');
    $dbLinkDAO          = _ioDAO ('kernel|kernel_bu2user2');
    $roleDAO = _ioDAO ('kernel_bu_personnel_role');
    
    $ppo->genderNames = array ('Homme', 'Femme');
    $ppo->genderIds = array ('0', '1');
    
    $ppo->roleName = $roleDAO->get ($ppo->role)->nom_role;

	  $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);

	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => 'Création d\'un '.$ppo->roleName);


	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");    
        
    // Enregistrement kernel_bu_personnel
    $ppo->personnel = _record ('kernel_bu_personnel');
                                   
    $ppo->personnel->nom         = trim (_request ('nom', null));
    $ppo->personnel->prenom1     = trim (_request ('prenom1', null));    
    $ppo->personnel->date_nais   = CopixDateTime::dateToyyyymmdd(_request ('date_nais', null));
    $ppo->personnel->id_sexe     = _request ('gender', null);
    
    $ppo->login                  = _request ('login', null);
    $ppo->password               = _request ('password', null);
    
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->personnel->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->personnel->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    if (!$ppo->login) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeLogin');
    }
    if (!$ppo->password) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typePassword');
    }
    elseif (!Kernel::checkPasswordFormat ($ppo->password)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.passwordFormat');
    }
    if (!Kernel::isLoginAvailable ($ppo->login)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.loginNotAvailable');
    }
    if (is_null($ppo->personnel->id_sexe)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.selectGender');
    }
   
    if (!empty ($ppo->errors)) {
  	  
  	  $ppo->personnel->date_nais = _request ('date_nais', null);
  	   
      return _arPPO ($ppo, 'create_personnel.tpl');
    }
    
    $personnelDAO->insert ($ppo->personnel);
    
    // Récupération du type_user et du type_ref                             
    switch ($ppo->nodeType) {
			case 'BU_GRVILLE' :
        $type_ref  = 'GVILLE';
        $type_user = 'USER_VIL';
				break;
		  case 'BU_VILLE' :
        $type_ref  = 'VILLE';
        $type_user = 'USER_VIL';
  			break;
  		case 'BU_ECOLE' :
        $type_ref  = 'ECOLE';
        if ($ppo->role == '3') {
          $type_user = 'USER_ADM';
        }                         
        else {
          $type_user = 'USER_ENS';
        }
    		break;		
    	case 'BU_CLASSE' :
        $type_ref  = 'CLASSE';
        $type_user = 'USER_ENS';
      	break;
		}
		
    // Enregistrement dbuser
    $dbuser = _record ('kernel|kernel_copixuser');

    $dbuser->login_dbuser    = $ppo->login;
    $dbuser->password_dbuser = md5 ($ppo->password);
    $dbuser->email_dbuser    = '';
    $dbuser->enabled_dbuser  = 1;
    
    $dbUserDAO->insert ($dbuser);
    
    // Enregistrement kernel_link_bu2user
    $dbLink = _record ('kernel|kernel_bu2user2');

    $dbLink->user_id = $dbuser->id_dbuser;
    $dbLink->bu_type = $type_user;
    $dbLink->bu_id   = $ppo->personnel->numero;
    
    $dbLinkDAO->insert ($dbLink);                                    
    
    // Enregistrement kernel_bu_personnel_entite
    $newPersonnelEntite = _record ('kernel_bu_personnel_entite');
    
		$newPersonnelEntite->id_per    = $ppo->personnel->numero; 
		$newPersonnelEntite->reference = $ppo->nodeId;
		$newPersonnelEntite->type_ref  = $type_ref;
		$newPersonnelEntite->role      = $ppo->role;
		
		$personnelEntiteDAO->insert ($newPersonnelEntite);
		
		$session = _sessionGet ('modules|gestionautonome|createAccount');
		if (!$session || !is_array ($session)) {
		  
		  $session = array();
		}
		
		$node_infos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, false);
		
		$session[$type_user.'-'.$ppo->personnel->numero][0] = array(
		  'nom'       => $ppo->personnel->nom,
			'prenom'    => $ppo->personnel->prenom1,
			'login'     => $ppo->login,
			'password'  => $ppo->password,
			'bu_type'   => $type_user,
			'bu_id'     => $ppo->personnel->numero,
			'type_nom'  => Kernel::Code2Name($type_user),
			'node_nom'  => Kernel::Code2Name($ppo->nodeType)." ".$node_infos['nom'],
		);
		
		_sessionSet ('modules|gestionautonome|createAccount', $session);
		
		$sessionId = $type_user.'-'.$ppo->personnel->numero;

		$urlReturn = CopixUrl::get ('gestionautonome||showAccountListing', array ('sessionId' => $sessionId, 'nodeId' => $ppo->nodeId, 'nodeType' => $ppo->nodeType));
		
		return new CopixActionReturn (COPIX_AR_REDIRECT, $urlReturn);
	}
	
	public function processShowAccountListing () {
	                                                                        
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  
	  // Récupération des informations des comptes créés
	  $session           = _sessionGet ('modules|gestionautonome|createAccount');
	  $ppo->sessionId    = _request ('sessionId', null);
	  $ppo->sessionDatas = $session[$ppo->sessionId]; 
    
    // Récupération du format de sortie demandé
	  if( !_request ('format') || trim (_request ('format')) == '' ) {
	    
			$format = "default";
		} 
		else {
		  
			$format = _request('format');
		} 
		
		// Sortie suivant le format demandé
		$tplResult = & new CopixTpl ();
		$tplResult->assign ('sessionDatas', $ppo->sessionDatas);
		
	  switch ($format) {
			case 'default':
				return _arPPO ($ppo, 'account_listing.tpl');
			case 'html':
			  $result = $tplResult->fetch ('account_listing_html.tpl');
			  return _arContent ($result, array ('filename'=>'Logins-'.date('YmdHi').'.html', 'content-disposition'=>'attachement', 'content-type'=>CopixMIMETypes::getFromExtension ('.html')));
			  break;
			case 'csv':
			  $result = $tplResult->fetch ('account_listing_csv.tpl');
			  return _arContent ($result, array ('filename'=>'Logins-'.date('YmdHi').'.csv', 'content-disposition'=>'attachement', 'content-type'=>CopixMIMETypes::getFromExtension ('.csv')));
			  break;
		}
	}
	
	public function processUpdatePersonnel () {
	
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $personnelId   = _request ('personnelId', null);
	  $ppo->type     = _request ('type', null);
	  $ppo->role     = _request ('role', null);
	  
	  // DAO
	  $personnelDAO  = _ioDAO ('kernel_bu_personnel');
	  $dbuserDAO     = _ioDAO ('kernel|kernel_copixuser');
	  $roleDAO       = _ioDAO ('kernel_bu_personnel_role');
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || !$ppo->personnel = $personnelDAO->get ($personnelId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
    
    $ppo->genderNames = array ('Homme', 'Femme');
    $ppo->genderIds = array ('0', '1');
    
    $ppo->personnel->date_nais = CopixDateTime::yyyymmddToDate ($ppo->personnel->date_nais);
    
    $ppo->personName = $ppo->personnel->nom.' '.$ppo->personnel->prenom1;
	  $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);

	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => $ppo->personName.' ('.Kernel::Code2Name ($ppo->type).')');


	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
	                                                                               
	  // Récupération du compte dbuser
	  $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($personnelId, $ppo->type);

		return _arPPO ($ppo, 'update_personnel.tpl');
	}
	
	public function processValidatePersonnelUpdate () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_node', null);
	  $ppo->nodeType = _request ('type_node', null);
	  $personnelId   = _request ('id_personnel', null);
	  $ppo->type     = _request ('type', null);
	  
	  $personnelDAO  = _ioDAO ('kernel_bu_personnel');
	  $dbuserDAO     = _ioDAO ('kernel|kernel_copixuser');
	  
	  if (!$ppo->personnel = $personnelDAO->get ($personnelId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $ppo->personName = $ppo->personnel->nom.' '.$ppo->personnel->prenom1;
	  $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);

	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => $ppo->personName.' ('.Kernel::Code2Name ($ppo->type).')');

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");     

    $ppo->personnel->nom         = trim (_request ('nom', null));
    $ppo->personnel->prenom1     = trim (_request ('prenom1', null));
    $ppo->personnel->date_nais   = _request ('date_nais', null);
    $ppo->personnel->id_sexe     = _request ('gender', null);
    $ppo->personnel->date_nais   = CopixDateTime::dateToyyyymmdd (_request ('date_nais', null)); 
    
    $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($personnelId, $ppo->type);
   
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->personnel->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->personnel->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }

    if (!empty ($ppo->errors)) {
      
      $ppo->genderNames = array ('Homme', 'Femme');
      $ppo->genderIds = array ('0', '1'); 
      
      $ppo->personnel->date_nais = _request ('date_nais', null);
      
      return _arPPO ($ppo, 'update_personnel.tpl');
    }
    
    $personnelDAO->update ($ppo->personnel);
        
    $newPassword = _request ('password', null);
    if ($ppo->account->password_dbuser != md5 ($newPassword)) {
      
      $ppo->account->password_dbuser = md5 ($newPassword);
      $dbuserDAO->update ($ppo->account);
    }

		return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processRemovePersonnel () {
	  
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $personnelId   = _request ('personnelId', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($personnelId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $type = substr ($ppo->nodeType, 3); 
	  if ($type == 'GRVILLE') {
	    
	    $type = 'GVILLE';
	  }
	  
	  $personLinkDAO = _ioDAO ('kernel|kernel_bu_personnel_entite');
	  $personLink = $personLinkDAO->getByIdReferenceAndType ($personnelId, $ppo->nodeId, $type);
	  
	  if ($personLink) {
	    
	    $personLinkDAO->delete ($personnelId, $ppo->nodeId, $type);
	  }
	  
	  return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processDeletePersonnel () {
	  
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $personnelId   = _request ('personnelId', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($personnelId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $personnelDAO = _ioDAO ('kernel|kernel_bu_personnel');
	  $personnelDAO->delete ($personnelId);

	  $personnelLinkDAO = _ioDAO ('kernel|kernel_bu_personnel_entite');
	  $links = $personnelLinkDAO->getById ($personnelId);
	  
	  foreach ($links as $link) {                 
	    
	    $personnelLinkDAO->delete ($link->id_per);
	  }
	  
	  return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processCreateStudent () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->nodeId   = _request ('parentId', null);
	  $ppo->nodeType = _request ('parentType', null);
	  
	  // Récupération des paramètres
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Remise à zéro des sessions tmp
	  _sessionSet ('modules|gestionautonome|tmpAccount', null);
    
    // Récupération des niveaux de la classe
    $classSchoolLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');
    $classLevelDAO       = _ioDAO ('kernel_bu_classe_niveau');
    
    $classSchoolLevels   = $classSchoolLevelDAO->getByClass ($ppo->nodeId);
    
    $ppo->levelNames = array ();
    $ppo->levelIds   = array ();
    
    foreach ($classSchoolLevels as $classSchoolLevel) {
      
      $level             = $classLevelDAO->get ($classSchoolLevel->niveau);
      $ppo->levelNames[] = $level->niveau_court;
      $ppo->levelIds[]   = $level->id_n;
    }
    
    $ppo->genderNames = array ('Garçon', 'Fille');
    $ppo->genderIds = array ('0', '1');
    
    // Compteur responsable
    $ppo->cpt = 1;

	  $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);

	  // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => 'Création d\'un élève');


	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
	  
		return _arPPO ($ppo, 'create_student.tpl');
	}
	
	public function processValidateStudentCreation () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";    
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_parent', null);
	  $ppo->nodeType = _request ('type_parent', null);
	  $ppo->level    = _request ('level', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }

		_classInclude ('kernel|Tools');
		
    $studentDAO              = _ioDAO ('kernel_bu_eleve');
    $studentRegistrationDAO  = _ioDAO ('kernel|kernel_bu_ele_inscr');
    $studentAdmissionDAO     = _ioDAO ('kernel_bu_eleve_admission');
    $studentAssignmentDAO    = _ioDAO ('kernel_bu_eleve_affectation');
    $dbuserDAO               = _ioDAO ('kernel|kernel_copixuser'); 
    $dbLinkDAO               = _ioDAO ('kernel_link_bu2user');
    $classDAO                = _ioDAO ('kernel_bu_ecole_classe');    
        
    // Création de l'élève
    $ppo->student = _record ('kernel_bu_eleve');
                            
    $ppo->student->numero       = '';                        
    $ppo->student->nom          = trim (_request ('student_lastname', null));
    $ppo->student->prenom1      = trim (_request ('student_firstname', null));
    $ppo->student->id_sexe      = _request ('gender', null);
    $ppo->student->date_nais    = CopixDateTime::dateToyyyymmdd(_request ('student_birthdate', null));
    $ppo->student->flag         = 0;
    $ppo->student->date_nais    = CopixDateTime::dateToyyyymmdd (_request ('date_nais', null));
    $ppo->student->ele_last_update = CopixDateTime::timestampToYYYYMMDDHHIISS (time ());
    
    $ppo->login                 = _request ('student_login', null);
    $ppo->password              = _request ('student_password', null); 
    
    $ppo->resp_on               = _request ('person_in_charge', null);
    
    $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);
    
    // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => 'Création d\'un élève');


	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->student->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->student->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    if (is_null($ppo->student->id_sexe)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.selectGender');
    }
    if (!$ppo->login) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeLogin');
    }
    elseif (!Kernel::isLoginAvailable ($ppo->login)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.loginNotAvailable');
    }
    if (!$ppo->password) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typePassword');
    }
    elseif (!Kernel::checkPasswordFormat ($ppo->password)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.passwordFormat');
    }
    
    if (!empty ($ppo->errors)) {
      
      // Récupération des niveaux de la classe
      $classSchoolLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');
      $classLevelDAO       = _ioDAO ('kernel_bu_classe_niveau');
      
      $classSchoolLevels   = $classSchoolLevelDAO->getByClass ($ppo->nodeId);

      $ppo->levelNames = array ();
      $ppo->levelIds   = array ();
      
      foreach ($classSchoolLevels as $classSchoolLevel) {

        $level              = $classLevelDAO->get ($classSchoolLevel->niveau);
        $ppo->levelNames[]  = $level->niveau_court;
        $ppo->levelIds[]    = $level->id_n;
      }
      
      $ppo->genderNames = array ('Garçon', 'Fille');
      $ppo->genderIds = array ('0', '1');
  	   
      $ppo->student->date_nais    = _request('student_birthdate', null);
      
      return _arPPO ($ppo, 'create_student.tpl');
    }

    $studentDAO->insert ($ppo->student);
    
    // Création du compte dbuser
    $dbuser = _record ('kernel|kernel_copixuser');

    $dbuser->login_dbuser    = $ppo->login;
    $dbuser->password_dbuser = md5 ($ppo->password);
    $dbuser->email_dbuser    = '';
    $dbuser->enabled_dbuser  = 1;
    
    $dbuserDAO->insert ($dbuser);
    
    // Création du link bu2user
    $dbLink = _record ('kernel_link_bu2user');

    $dbLink->user_id = $dbuser->id_dbuser;
    $dbLink->bu_type = 'USER_ELE';
    $dbLink->bu_id   = $ppo->student->idEleve;
    
    $dbLinkDAO->insert ($dbLink);

    // Récupération des inscriptions de l'élève pour passage current 0
    $studentRegistrations = $studentRegistrationDAO->getByStudent ($ppo->student->idEleve); 
    foreach ($studentRegistrations as $studentRegistration) {
      
      $studentRegistration->current = 0;
      $studentRegistrationDAO->update ($studentRegistration);
    }
    
    // Inscription de l'élève dans l'école
    $studentRegistration = _record ('kernel|kernel_bu_ele_inscr');
    
    $studentRegistration->inscr_eleve                   = $ppo->student->idEleve;
    $studentRegistration->inscr_annee_scol              = Kernel::getAnneeScolaireCourante ()->id_as;
    $studentRegistration->inscr_date_preinscript        = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentRegistration->inscr_date_effet_preinscript  = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentRegistration->inscr_date_inscript           = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentRegistration->inscr_date_effet_inscript     = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentRegistration->inscr_etablissement_refus     = 0;
    $studentRegistration->inscr_id_niveau               = 0;
    $studentRegistration->inscr_id_typ_cla              = 0;
    $studentRegistration->inscr_vaccins_aj              = 0;
    $studentRegistration->inscr_attente                 = 0;
    $studentRegistration->inscr_derogation_dem          = 0;
    $studentRegistration->inscr_temporaire              = 0;
    $studentRegistration->inscr_current_inscr           = 1;

    $studentRegistrationDAO->insert ($studentRegistration);

    // Admission de l'élève dans l'école
    $studentAdmission = _record ('kernel_bu_eleve_admission');
    
    $studentAdmission->eleve          = $ppo->student->idEleve;
    $studentAdmission->etablissement  = $classDAO->get ($ppo->nodeId)->ecole;
    $studentAdmission->annee_scol     = Kernel::getAnneeScolaireCourante ()->id_as;
    $studentAdmission->id_niveau      = $ppo->level;
    $studentAdmission->etat_eleve     = 1;
    $studentAdmission->date           = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentAdmission->date_effet     = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentAdmission->code_radiation = '';
    $studentAdmission->previsionnel   = '';
    
    $studentAdmissionDAO->insert ($studentAdmission);
    
    // Affectation de l'élève dans les classes
    $studentAssignment = _record ('kernel_bu_eleve_affectation');
    
    $studentAssignment->eleve           = $ppo->student->idEleve;
    $studentAssignment->annee_scol      = Kernel::getAnneeScolaireCourante ()->id_as;
    $studentAssignment->classe          = $ppo->nodeId;
    $studentAssignment->niveau          = $ppo->level;
    $studentAssignment->dateDebut       = CopixDateTime::timestampToYYYYMMDD (time ());
    $studentAssignment->current         = 1;
    $studentAssignment->previsionnel_cl = 0;

    $studentAssignmentDAO->insert ($studentAssignment);
    
    $type_user = 'USER_ELE';
    
    $node_infos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, false);
		
		// Mise en session des comptes créés : 0 -> élève / x -> responsables
		$session = _sessionGet ('modules|gestionautonome|createAccount');
		if (!$session || !is_array ($session)) {
		  
		  $session = array();
		}
		
		$session[$type_user.'-'.$ppo->student->idEleve][0] = array(
		  'nom'       => $ppo->student->nom,
			'prenom'    => $ppo->student->prenom1,
			'login'     => $ppo->login,
			'password'  => $ppo->password,
			'bu_type'   => $type_user,
			'bu_id'     => $ppo->student->idEleve,
			'type_nom'  => Kernel::Code2Name($type_user),
			'node_nom'  => Kernel::Code2Name($ppo->nodeType)." ".$node_infos['nom'],
		);
		                                                                      
		// Récupérations des infos temporaires en session et ajout aux sessions
		$tmpSession = _sessionGet ('modules|gestionautonome|tmpAccount');                                                                
		if ($tmpSession && is_array ($tmpSession)) {
		  
		  $personDAO       = _ioDAO ('kernel_bu_responsable');
      $personLinkDAO   = _ioDAO ('kernel_bu_responsables');
      
		  if ($personSessions = $tmpSession[$ppo->nodeType.'-'.$ppo->nodeId]) {
		    
		    $cptPerson = 1;

  		  foreach ($personSessions as $personSession) {

          // Ajout du responsable en session seulement si la création du dbuser est possible
          if (Kernel::isLoginAvailable ($personSession['login'])) {
            
            // Création du responsable
            $ppo->person = _record ('kernel_bu_responsable');

            $ppo->person->nom        = trim ($personSession['nom']);
            $ppo->person->prenom1    = trim ($personSession['prenom']);
            $ppo->res_id_par         = $personSession['id_par'];

    		    $personDAO->insert ($ppo->person);

            // Création de l'association personne->rôle
            $newPersonLink = _record ('kernel_bu_responsables');

        		$newPersonLink->id_beneficiaire   = $ppo->student->idEleve; 
        		$newPersonLink->type_beneficiaire = 'eleve';
        		$newPersonLink->id_responsable    = $ppo->person->numero;
        		$newPersonLink->type              = 'responsable';
        		$newPersonLink->auth_parentale    = '0';
        		$newPersonLink->id_par            = $personSession['id_par'];

        		$personLinkDAO->insert ($newPersonLink);

        		// Création du compte dbuser
            $dbuser = _record ('kernel|kernel_copixuser');

            $dbuser->login_dbuser    = $personSession['login'];
            $dbuser->password_dbuser = md5 ($personSession['password']);
            $dbuser->email_dbuser    = '';
            $dbuser->enabled_dbuser  = 1;

            $dbuserDAO->insert ($dbuser);

            // Création du link bu2user
            $dbLink = _record ('kernel_link_bu2user');

            $dbLink->user_id = $dbuser->id_dbuser;
            $dbLink->bu_type = 'USER_RES';
            $dbLink->bu_id   = $ppo->person->numero;

            $dbLinkDAO->insert ($dbLink);
            
            // Mise en session du responsable
            $session[$type_user.'-'.$ppo->student->idEleve][$cptPerson] = array(
        		  'nom'       => $personSession['nom'],
        			'prenom'    => $personSession['prenom'],
        			'login'     => $personSession['login'],
        			'password'  => $personSession['password'],
        			'bu_type'   => 'USER_RES',
        			'bu_id'     => $ppo->student->idEleve,
        			'type_nom'  => Kernel::Code2Name('USER_RES'),
        			'node_nom'  => Kernel::Code2Name($ppo->nodeType)." ".$node_infos['nom'],
        		);

        		$cptPerson++;
          }
        }
		  }
		}
		
		// Mise en session de l'élève et des responsables
		_sessionSet ('modules|gestionautonome|createAccount', $session);
		
		// Remise à zéro des sessions tmp
		_sessionSet ('modules|gestionautonome|tmpAccount', null);
		
		$sessionId = $type_user.'-'.$ppo->student->idEleve;

		$urlReturn = CopixUrl::get ('gestionautonome||showAccountListing', array ('sessionId' => $sessionId, 'nodeId' => $ppo->nodeId, 'nodeType' => $ppo->nodeType));
		
		return new CopixActionReturn (COPIX_AR_REDIRECT, $urlReturn);
	}
	
	public function processUpdateStudent () {
	  
	  $ppo = new CopixPPO ();                                       

	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $studentId     = _request ('studentId', null);
	  
	  $studentDAO    = _ioDAO ('kernel_bu_eleve');
	  $dbuserDAO     = _ioDAO ('kernel|kernel_copixuser');
	  $classDAO      = _ioDAO ('kernel|kernel_bu_ecole_classe');
	  $schoolDAO     = _ioDAO ('kernel|kernel_bu_ecole');
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || !$ppo->student = $studentDAO->get ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
    
    $ppo->student->date_nais = CopixDateTime::yyyymmddToDate ($ppo->student->date_nais);
    
    $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($studentId, 'USER_ELE');
    
    $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);
    
    // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => $ppo->student->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
	  
	  $ppo->genderNames = array ('Garçon', 'Fille');
    $ppo->genderIds = array ('0', '1');
	  
	  return _arPPO ($ppo, 'update_student.tpl');
	}
	
	public function processValidateStudentUpdate () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";

	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('id_node', null);
	  $ppo->nodeType = _request ('type_node', null);
	  $studentId     = _request ('id_student', null);
	  
	  $studentDAO = _ioDAO ('kernel_bu_eleve');
	  $dbuserDAO  = _ioDAO ('kernel|kernel_copixuser');
	  
	  if (!$ppo->student = $studentDAO->get ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }

    $nodeInfos = Kernel::getNodeInfo ($ppo->nodeType, $ppo->nodeId, true);
    
    // Breadcrumbs
	  $breadcrumbs=array();
	  $breadcrumbs[] = array('txt' => 'Gestion de la structure scolaire', 'url' => CopixUrl::get('gestionautonome||showTree'));
	  if (isset ($nodeInfos['ALL']->vil_id_vi)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->vil_nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->vil_id_vi, 'nodeType' => 'BU_VILLE')));
	  }
	  elseif (isset ($nodeInfos['ALL']->eco_id_ville)) {
	    
	    $city = _ioDAO('kernel_bu_ville')->get ($nodeInfos['ALL']->eco_id_ville);
	    $breadcrumbs[] = array('txt' => $city->nom, 'url' => CopixUrl::get('gestionautonome||updateCity', array ('nodeId' => $nodeInfos['ALL']->eco_id_ville, 'nodeType' => 'BU_VILLE')));
	  }
	  if (isset ($nodeInfos['ALL']->eco_numero)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->eco_nom, 'url' => CopixUrl::get('gestionautonome||updateSchool', array ('nodeId' => $nodeInfos['ALL']->eco_numero, 'nodeType' => 'BU_ECOLE')));
	  }
	  if (isset ($nodeInfos['ALL']->cla_id)) {
	    
	    $breadcrumbs[] = array('txt' => $nodeInfos['ALL']->cla_nom, 'url' => CopixUrl::get('gestionautonome||updateClass', array ('nodeId' => $nodeInfos['ALL']->cla_id, 'nodeType' => 'BU_CLASSE')));
	  }
	  
	  $breadcrumbs[] = array('txt' => $ppo->student->nom);

	  $ppo->breadcrumbs = Kernel::PetitPoucet($breadcrumbs," &raquo; ");
    
    $ppo->student->numero       = '';                        
    $ppo->student->nom          = trim (_request ('nom', null));
    $ppo->student->prenom1      = trim (_request ('prenom1', null));
    $ppo->student->id_sexe      = _request ('gender', null);
    $ppo->student->date_nais    = CopixDateTime::dateToyyyymmdd (_request ('date_nais', null));
    $ppo->student->flag         = 0;
    $ppo->student->ele_last_update = CopixDateTime::timestampToYYYYMMDDHHIISS (time ());
     
    $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($studentId, 'USER_ELE');

    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->student->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->student->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    
    if (!empty ($ppo->errors)) {
            
      // Récupération des niveaux de la classe
      $classSchoolLevelDAO = _ioDAO ('kernel|kernel_bu_ecole_classe_niveau');
      $classLevelDAO       = _ioDAO ('kernel_bu_classe_niveau');
      
      $classSchoolLevels   = $classSchoolLevelDAO->getByClass ($ppo->nodeId);

      $ppo->levelNames = array ();
      $ppo->levelIds   = array ();
      
      foreach ($classSchoolLevels as $classSchoolLevel) {

        $level              = $classLevelDAO->get ($classSchoolLevel->niveau);
        $ppo->levelNames[]  = $level->niveau_court;
        $ppo->levelIds[]    = $level->id_n;
      }
      
      $ppo->student->date_nais = _request ('date_nais', null);
      
      $ppo->genderNames = array ('Garçon', 'Fille');
      $ppo->genderIds = array ('0', '1');
  	   
      return _arPPO ($ppo, 'update_student.tpl');
    }
      
    $studentDAO->update ($ppo->student);
		
		// Modification du password dbuser si différent
		$newPassword = _request ('password', null);
    if ($ppo->account->password_dbuser != md5 ($newPassword)) {
      
      $ppo->account->password_dbuser = md5 ($newPassword);
      $dbuserDAO->update ($ppo->account);
    }
    
		$ppo->targetId   = $ppo->nodeId;
		$ppo->targetType = $ppo->nodeType;

		return _arPPO ($ppo, array ('template' => 'update_success.tpl', 'mainTemplate' => null));
	}
	
	
	public function processRemoveStudent () {
	  
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $studentId     = _request ('studentId', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $assignmentDAO = _ioDAO ('kernel|kernel_bu_ele_affect');
	  $assignment    = $assignmentDAO->getByStudentAndClass ($studentId, $ppo->nodeId);
	  
	  $assignmentDAO->delete ($assignment->affect_id);
	  
	  // TODO : Radiation de l’élève, avec une ligne en plus dans la table eleve_admission (avec etat_eleve = 3 : radiation)
	  
	  return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processDeleteStudent () {
	  
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $studentId     = _request ('studentId', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $studentDAO    = _ioDAO ('kernel_bu_eleve');
	  $assignmentDAO = _ioDAO ('kernel|kernel_bu_ele_affect');
	  
	  // Récupération des affectations de l'élève
	  $assignments = $assignmentDAO->getByStudent ($studentId);
	  
	  foreach ($assignments as $assignment) {
	    
	    $assignmentDAO->delete ($assignment->affect_id);
	  }
	  
	  $studentDAO->delete ($studentId);
	  
	  // TODO : suppression des inscriptions / affectations / assignments
	  
	  return _arPPO ($ppo, 'update_success.tpl');
	}
	
	public function processCreatePersonInCharge () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $studentId     = _request ('studentId', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $studentDAO    = _ioDAO ('kernel|kernel_bu_ele'); 
	  $cityDAO       = _ioDAO ('kernel_bu_ville');  
	  $countryDAO    = _ioDAO ('kernel_bu_pays');  
	  $parentLinkDAO = _ioDAO ('kernel_bu_lien_parental');
	  $situationDAO  = _ioDAO ('kernel_bu_situation_familiale');
	  $pcsDAO        = _ioDAO ('kernel_bu_pcs');     
	  $dbuserDAO     = _ioDAO ('kernel|kernel_copixuser');
	  	  
	  // Récupération de l'élève  
	  $ppo->student = $studentDAO->get ($studentId);
	  
    // Récupération des relations
	  $parentLinks = $parentLinkDAO->findAll ();
	  
	  $ppo->linkNames = array ();
	  $ppo->linkIds   = array ();
	  
	  foreach ($parentLinks as $parentLink) {

      $ppo->linkNames[] = $parentLink->parente;
      $ppo->linkIds[]   = $parentLink->id_pa;
    }
    
    $ppo->genderNames = array ('Homme', 'Femme');
    $ppo->genderIds = array ('0', '1');
    
    $ppo->studentAccount = $dbuserDAO->getUserByBuIdAndBuType ($studentId, 'USER_ELE');
	  
		return _arPPO ($ppo, 'create_person_in_charge.tpl');	  
	}
	
	public function processValidatePersonInChargeCreation () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId    = _request ('id_node', null);
	  $ppo->nodeType  = _request ('type_node', null);
	  $ppo->studentId = _request ('id_student', null);
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($ppo->studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
    $personDAO       = _ioDAO ('kernel_bu_responsable');
    $personLinkDAO   = _ioDAO ('kernel_bu_responsables');
    $dbuserDAO       = _ioDAO ('kernel|kernel_copixuser');
    $dbLinkDAO       = _ioDAO ('kernel|kernel_bu2user2');  
        
    // Création de la personne
    $ppo->person = _record ('kernel_bu_responsable');
                        
    $ppo->person->nom        = trim (_request ('nom', null));
    $ppo->person->prenom1    = trim (_request ('prenom1', null));
    $ppo->person->id_sexe    = _request('gender', null);
    $ppo->res_id_par         = _request ('id_par', null);
    
    $ppo->login     = _request ('login', null);
    $ppo->password  = _request ('password', null);
       
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->person->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->person->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    if (!$ppo->login) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeLogin');
    }
    elseif (!Kernel::isLoginAvailable ($ppo->login)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.loginNotAvailable');
    }
    if (!$ppo->password) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typePassword');
    }
    elseif (!Kernel::checkPasswordFormat ($ppo->password)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.passwordFormat');
    }
    
    if (!empty ($ppo->errors)) {
      
      // Récupération de l'élève
  	  $studentDAO = _ioDAO ('kernel|kernel_bu_ele');
  	  $ppo->student = $studentDAO->get ($ppo->studentId);
  	  
      // Récupération des relations
  		$parentLinkDAO = _ioDAO ('kernel_bu_lien_parental');
  	  $parentLinks = $parentLinkDAO->findAll ();

  	  $ppo->linkNames = array ();
  	  $ppo->linkIds   = array ();

  	  foreach ($parentLinks as $parentLink) {

        $ppo->linkNames[] = $parentLink->parente;
        $ppo->linkIds[]   = $parentLink->id_pa;
      }
      
      $ppo->genderNames = array ('Homme', 'Femme');
      $ppo->genderIds = array ('0', '1');
      
      return _arPPO ($ppo, 'create_person_in_charge.tpl');
    }
    
    $personDAO->insert ($ppo->person);
    
    // Création du compte dbuser
    $dbuser = _record ('kernel|kernel_copixuser');

    $dbuser->login_dbuser    = $ppo->login;
    $dbuser->password_dbuser = md5 ($ppo->password);
    $dbuser->email_dbuser    = '';
    $dbuser->enabled_dbuser  = 1;
    
    $dbuserDAO->insert ($dbuser);
    
    // Création du link bu2user
    $dbLink = _record ('kernel_link_bu2user');

    $dbLink->user_id = $dbuser->id_dbuser;
    $dbLink->bu_type = 'USER_RES';
    $dbLink->bu_id   = $ppo->person->numero;
    
    $dbLinkDAO->insert ($dbLink);

    // Création de l'association personne->rôle
    $newPersonLink = _record ('kernel_bu_responsables');

		$newPersonLink->id_beneficiaire   = $ppo->studentId; 
		$newPersonLink->type_beneficiaire = 'eleve';
		$newPersonLink->id_responsable    = $ppo->person->numero;
		$newPersonLink->type              = 'responsable';
		$newPersonLink->auth_parentale    = '0';
		$newPersonLink->id_par            = $ppo->res_id_par;
				
		$personLinkDAO->insert ($newPersonLink);
		
		return _arPPO ($ppo, array ('template' => 'create_person_in_charge_success.tpl', 'mainTemplate' => null));  
	}
	
	public function processUpdatePersonInCharge () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId   = _request ('nodeId', null);
	  $ppo->nodeType = _request ('nodeType', null);
	  $studentId     = _request ('studentId', null);
	  $personId      = _request ('personId', null);
	  
	  $personDAO       = _ioDAO ('kernel_bu_responsable');
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($studentId) || !$ppo->person = $personDAO->get ($personId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }

	  // Récupération de l'élève
	  $studentDAO   = _ioDAO ('kernel|kernel_bu_ele');
	  $ppo->student = $studentDAO->get ($studentId);
	  
	  $dbuserDAO    = _ioDAO ('kernel|kernel_copixuser');
	  $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($studentId, 'USER_ELE');
	  
    // Récupération des liens parentaux
		$parentLinkDAO = _ioDAO ('kernel_bu_lien_parental');
	  $parentLinks   = $parentLinkDAO->findAll ();
	  
	  $ppo->linkNames = array ();
	  $ppo->linkIds   = array ();
	  
	  foreach ($parentLinks as $parentLink) {

      $ppo->linkNames[] = $parentLink->parente;
      $ppo->linkIds[]   = $parentLink->id_pa;
    }
    
    // Récupération du lien responsable-élève
    $res2eleDAO   = _ioDAO ('kernel|kernel_bu_res2ele');
    $ppo->res2ele = $res2eleDAO->getByPersonAndStudent ($personId, $studentId); 

		return _arPPO ($ppo, 'update_person_in_charge.tpl');
	}
	
	public function processValidatePersonInChargeUpdate () {
	  
	  $ppo = new CopixPPO (); 
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  // Récupération des paramètres
	  $ppo->nodeId    = _request ('id_node', null);
	  $ppo->nodeType  = _request ('type_node', null);
	  $ppo->studentId = _request ('id_student', null);
	  $personId       = _request ('id_person', null);
	  
	  $personDAO      = _ioDAO ('kernel_bu_responsable');
	  
	  if (is_null ($ppo->nodeId) || is_null ($ppo->nodeType) || is_null ($ppo->studentId) || !$ppo->person = $personDAO->get ($personId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Récupération du lien responsable-élève
    $res2eleDAO = _ioDAO ('kernel|kernel_bu_res2ele');
    $ppo->res2ele = $res2eleDAO->getByPersonAndStudent ($personId, $ppo->studentId);

    $ppo->person->nom         = trim (_request ('nom', null));
    $ppo->person->prenom1     = trim (_request ('prenom1', null));
    $ppo->person->date_nais   = _request ('date_nais', null);
    $res_id_par               = _request ('id_par', null);
    
    // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->person->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->person->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    
    if (!empty ($ppo->errors)) {
      
      // Récupération de l'élève
  	  $studentDAO = _ioDAO ('kernel|kernel_bu_ele');
  	  $ppo->student = $studentDAO->get ($ppo->studentId);
       
      $dbuserDAO = _ioDAO ('kernel|kernel_copixuser');
  	  $ppo->account = $dbuserDAO->getUserByBuIdAndBuType ($ppo->studentId, 'USER_ELE');

      // Récupération des liens parentaux
  		$parentLinkDAO = _ioDAO ('kernel_bu_lien_parental');
  	  $parentLinks = $parentLinkDAO->findAll ();

  	  $ppo->linkNames = array ();
  	  $ppo->linkIds   = array ();

  	  foreach ($parentLinks as $parentLink) {

        $ppo->linkNames[] = $parentLink->parente;
        $ppo->linkIds[]   = $parentLink->id_pa;
      }

      return _arPPO ($ppo, 'update_person_in_charge.tpl');
    }
    
    $personDAO->update ($ppo->person);
    
    if ($ppo->res2ele->res2ele_id_par != $res_id_par) {
      
      $ppo->res2ele->res2ele_id_par = $res_id_par;
      $res2eleDAO->update ($ppo->res2ele);
    }

		return _arPPO ($ppo, array ('template' => 'create_person_in_charge_success.tpl', 'mainTemplate' => null));
	}
	
	// AJAX
	public function processRemovePersonInCharge () {
	  
	  $ppo = new CopixPPO ();
	  
	  $personId   = _request ('personId', null);
	  $studentId  = _request ('studentId', null);
	  
	  if (is_null ($personId) || is_null ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  // Suppression de l'affectation du responsable
	  $personInChargeLinkDAO = _ioDAO ('kernel|kernel_bu_res2ele');
	  $personInChargeLink    = $personInChargeLinkDAO->getByPersonAndStudent ($personId, $studentId);
	  
	  $personInChargeLinkDAO->delete ($personInChargeLink->res2ele_id_rel);
	  
	  // Récupération des responsables de l'élève
	  $personsInChargeDAO = _ioDAO ('kernel|kernel_bu_res');
	  $ppo->persons       = $personsInChargeDAO->getByStudent ($studentId);

    return _arPPO ($ppo, array ('template' => '_persons_in_charge.tpl', 'mainTemplate' => null));
	}
	
	public function processDeletePersonInCharge () {
	  
	  $ppo = new CopixPPO ();
	  
	  $personId   = _request ('personId', null);
	  $studentId  = _request ('studentId', null);
	  
	  if (is_null ($personId) || is_null ($studentId)) {
	    
	    return CopixActionGroup::process ('generictools|Messages::getError',
  			array ('message'=> "Une erreur est survenue.", 'back'=> CopixUrl::get('gestionautonome||showTree')));
	  }
	  
	  $personsInChargeDAO = _ioDAO ('kernel|kernel_bu_res');
    $personInChargeLinkDAO = _ioDAO ('kernel|kernel_bu_res2ele');
	  
	  // Récupération des affectations du responsable
	  $assignments = $personInChargeLinkDAO->getByPerson ($personId);
	  
	  foreach ($assignments as $assignment) {
	    
	    $personInChargeLinkDAO->delete ($assignment->res2ele_id_rel);
	  }
	  
	  $personsInChargeDAO->delete ($personId);
	  
	  // Récupération des responsables de l'élève
	  $ppo->persons = $personsInChargeDAO->getByStudent ($studentId);
	  
	  return _arPPO ($ppo, array ('template' => '_persons_in_charge.tpl', 'mainTemplate' => null));
	} 
	
	// AJAX
	public function processPersonInChargeCreation () {
	  
	  $ppo->nodeId          = _request ('nodeId', null);
	  $ppo->nodeType        = _request ('nodeType', null);

	  $ppo->person->nom     = _request ('nom', null);
	  $ppo->person->prenom1 = _request ('prenom1', null);
	  $ppo->person->sexe    = _request ('sexe', null);
		$ppo->person->id_par  = _request ('parId', null);
		
	  $ppo->account->login    = _request ('login', null);
	  $ppo->account->password = _request ('password', null);
	  
	  $ppo->cpt = _request('cpt', null);
 
	  // Récupération des relations    
	  $parentLinkDAO = _ioDAO ('kernel_bu_lien_parental'); 
	  $parentLinks = $parentLinkDAO->findAll ();

	  $ppo->linkNames = array ();
	  $ppo->linkIds   = array ();

	  foreach ($parentLinks as $parentLink) {

      $ppo->linkNames[] = $parentLink->parente;
      $ppo->linkIds[]   = $parentLink->id_pa;
    }

    // Récupérations des sexes
    $genderDAO = _ioDAO ('kernel_bu_sexe');
    $genders = $genderDAO->findAll ();

    foreach ($genders as $gender) {

      $ppo->genderNames[] = $gender->sexe;
      $ppo->genderIds[] = $gender->id_s;
    }
    
    $session = _sessionGet ('modules|gestionautonome|tmpAccount');
		if (!$session || !is_array ($session)) {
		  
		  $session = array();
		}
		
		if (!isset ($session[$ppo->nodeType.'-'.$ppo->nodeId])) {
		  
		  $ppo->personsInSession = null;
		}
		else {
		  
		  $ppo->personsInSession = $session[$ppo->nodeType.'-'.$ppo->nodeId];
		}
    
	  // Traitement des erreurs
    $ppo->errors = array ();
    
    if (!$ppo->person->nom) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeName');
    }
    if (!$ppo->person->prenom1) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeFirstName');
    }
    if (!$ppo->account->login) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeLogin');
    }
    if (!$ppo->account->password) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typePassword');
    }
    elseif (!Kernel::checkPasswordFormat ($ppo->account->password)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.passwordFormat');
    }
    if (!Kernel::isLoginAvailable ($ppo->account->login)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.loginNotAvailable');
    }
    
    if (!empty ($ppo->errors)) {

      return _arPPO ($ppo, array ('template' => '_create_person_in_charge.tpl', 'mainTemplate' => null));
    }  
    

		$session[$ppo->nodeType.'-'.$ppo->nodeId][$ppo->cpt] = array(
		  'nom'       => $ppo->person->nom,
			'prenom'    => $ppo->person->prenom1,
			'id_par'    => $ppo->person->id_par,
			'login'     => $ppo->account->login,
			'password'  => $ppo->account->password,
		);
		
		_sessionSet ('modules|gestionautonome|tmpAccount', $session);

		$ppo->person  = null;
		$ppo->account = null;
		
		$ppo->personsInSession = $session[$ppo->nodeType.'-'.$ppo->nodeId];
		if (!$ppo->personsInSession || !is_array ($ppo->personsInSession)) {
		  
		  $ppo->personsInSession = array();
		}
		
		if ($ppo->cpt) {

      $ppo->cpt++;                
    }                                
    else {

      $ppo->cpt = 1;
    }
		
		return _arPPO ($ppo, array ('template' => '_create_person_in_charge.tpl', 'mainTemplate' => null));
	}
	
	public function processManageGrades () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion des années scolaires";
	  
	  // Récupérations des années scolaires
    $gradesDAO = _ioDAO ('kernel_bu_annee_scolaire');
	  $ppo->grades = $gradesDAO->findAll ();
	  
	  return _arPPO ($ppo, 'manage_grades.tpl');
	}
	
	public function processCreateGrade () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion des années scolaires";
	  
	  return _arPPO ($ppo, 'create_grade.tpl');
	}
	
	public function processValidateGradeCreation () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion des années scolaires";
	  
	  $gradesDAO = _ioDAO ('kernel_bu_annee_scolaire');
	  
	  $ppo->grade = _record ('kernel_bu_annee_scolaire');

    $dateDebut                  = _request ('dateDebut', null);
    $dateFin                    = _request ('dateFin', null);
                                                            
    $ppo->grade->id_as          = substr($dateDebut, 6, 10);
	  $ppo->grade->annee_scolaire = substr($dateDebut, 6, 10).'-'.substr($dateFin, 6, 10);
	  $ppo->grade->dateDebut      = CopixDateTime::dateToyyyymmdd($dateDebut);
	  $ppo->grade->dateFin        = CopixDateTime::dateToyyyymmdd($dateFin);
	  if (_request ('current', null) == 'on') {
      
      $ppo->grade->current = 1;
    }                    
    else {
      
      $ppo->grade->current = 0;
    }
	  
    if (!$ppo->grade->dateDebut) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeStartDate');
    }
    if (!$ppo->grade->dateFin) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.typeEndDate');
    }
    if ($gradesDAO->get ($ppo->grade->id_as)) {
      
      $ppo->errors[] = CopixI18N::get ('gestionautonome.error.gradeAlreadyExist');
    }
    
    if (!empty ($ppo->errors)) {
      
      $ppo->grade->dateDebut    = CopixDateTime::yyyymmddToDate($ppo->grade->dateDebut);
  	  $ppo->grade->dateFin      = CopixDateTime::yyyymmddToDate($ppo->grade->dateFin);
  	  
      return _arPPO ($ppo, 'create_grade.tpl');
    }
    
    if ($ppo->grade->current == 1) {
      
      $currentGrade = Kernel::getAnneeScolaireCourante ();
      $currentGrade->current = 0;
    }
    
    $gradesDAO->insert ($ppo->grade); 
    
    // Récupérations des années scolaires
    $gradesDAO = _ioDAO ('kernel_bu_annee_scolaire');
	  $ppo->grades = $gradesDAO->findAll ();

	  return _arPPO ($ppo, 'manage_grades.tpl');
	}
	
	public function processSetCurrentGrade () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion des années scolaires";
	  
	  $gradeId = _request ('gradeId', null);
	  
	  $gradesDAO = _ioDAO ('kernel_bu_annee_scolaire');
	  $grades = $gradesDAO->findAll ();
	  
	  foreach ($grades as $grade) {
	    
	    $grade->current = 0;
	    $gradesDAO->update ($grade);
	  }
	  
	  $newCurrentGrade = $gradesDAO->get ($gradeId);
	  $newCurrentGrade->current = 1;
	  $gradesDAO->update ($newCurrentGrade);
	  
	  // Récupérations des années scolaires
    $gradesDAO = _ioDAO ('kernel_bu_annee_scolaire');
	  $ppo->grades = $gradesDAO->findAll ();
	  
	  return _arPPO ($ppo, 'manage_grades.tpl');
	}
	
	public function processAddExistingPersonnel () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->nodeId   = _request ('parentId', null);
	  $ppo->nodeType = _request ('parentType', null);
	  $ppo->role     = _request ('role', null);
	  
	  $type = substr ($ppo->nodeType, 3);
	  if ($type == 'GRVILLE') {
	    
	    $type = 'GVILLE'; 
	  }                                                       
	   
	  $personDAO = _ioDAO ('kernel|kernel_bu_personnel');
	  $ppo->persons = $personDAO->getPersonnelForAssignment ($ppo->nodeId, $type, $ppo->role);
	  
	  return _arPPO ($ppo, 'add_existing_personnel.tpl');
	}
	
	public function processValidateExistingPersonsAdd () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->nodeId   = _request ('id_node', null);
	  $ppo->nodeType = _request ('type_node', null);
	  $ppo->role     = _request ('role', null);
	  $ppo->personIds = _request ('personIds', null);

	  $type = substr ($ppo->nodeType, 3);  
	  if ($type == 'GRVILLE') {
	    $type = 'GVILLE';
	  }
	  
	  $personEntityDAO = _ioDAO ('kernel_bu_personnel_entite');    
        
	  foreach ($ppo->personIds as $personId) {
	    
	    $newPersonEntity = _record ('kernel_bu_personnel_entite');
	    
	    $newPersonEntity->id_per = $personId;
  	  $newPersonEntity->reference = $ppo->nodeId;
  	  $newPersonEntity->type_ref = $type;
  	  $newPersonEntity->role = $ppo->role;

  	  $personEntityDAO->insert ($newPersonEntity);
	  }
	  
	  $personDAO    = _ioDAO ('kernel|kernel_bu_personnel');
	  $ppo->persons = $personDAO->getPersonnelForAssignment ($ppo->nodeId, $type, $ppo->role);
	  $ppo->save    = 1;
	  
	  return _arPPO ($ppo, 'add_existing_personnel.tpl');
	}
	
	public function processAddMultipleStudents () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->nodeId   = _request ('parentId', null);
  	$ppo->nodeType = _request ('parentType', null);
	  
	  return _arPPO ($ppo, 'add_multiple_students.tpl');
	}
	
	public function processValidateMultipleStudentsAdd () {
	  
	  $ppo = new CopixPPO ();
	  
	  $ppo->TITLE_PAGE = "Gestion de la structure scolaire";
	  
	  $ppo->nodeId   = _request ('id-parent', null);
  	$ppo->nodeType = _request ('type-parent', null);
  	$ppo->liste    = _request ('liste', null);
  	

  	return _arNone ();
	  
	  return _arPPO ($ppo, 'add_multiple_students_listing.tpl');
	}
	
	// AJAX
	public function processGenerateLogin () {
	                              
	  $users_infos ['nom']    = _request ('lastname', null);
	  $users_infos ['prenom'] = _request ('firstname', null); 
	  $users_infos ['type']   = _request ('type', null);
                                             
	  echo Kernel::createLogin ($users_infos); 
	  
	  return _arNone ();
	}
	
	// AJAX
	public function processGeneratePassword () {
	  
	  echo Kernel::createPasswd ();
	  
	  return _arNone ();
	}
}