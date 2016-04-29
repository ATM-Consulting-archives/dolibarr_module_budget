<?php
require '../config.php';

dol_include_once("/budget/class/budget.class.php");
dol_include_once("/budget/class/encours.class.php");
dol_include_once('/sig/class/categorie_comptable.class.php');

if(empty($conf->sig->enabled)) exit('SIGrequire');

// Contrôle d'accès
if (!($user->admin || $user->rights->budget->read)) {
    accessforbidden();
}

$langs->load('budget@budget');

$PDOdb=new TPDOdb;

// Get parameters
_action($PDOdb);

function _action(&$PDOdb) {
	global $user, $conf,$langs;
	
	$encours = new TEncours;
	$action = GETPOST('action');
	
	switch($action) {
		
		case 'update_encours':
			$identifiant=GETPOST('identifiant');
			$type_object = GETPOST('type_object');
			$month = (int) GETPOST('month');
			$year = (int) GETPOST('year');
			$price = GETPOST('price');
			
			$encours->load_for_date($PDOdb, $identifiant, $type_object, $year, $month);
			
			$date_encours = strtotime(date($year.'-'.$month.'-01'));
			$encours->fk_object = $identifiant;
			$encours->type_object = $type_object;
			$encours->price = $price;
			$encours->date_encours = $date_encours;
			$encours->save($PDOdb);
			break;
		case 'delete_encours':
			$identifiant=GETPOST('identifiant');
			$type_object = GETPOST('type_object');
			$month = (int) GETPOST('month');
			$year = (int) GETPOST('year');
			$price = GETPOST('price');
			
			$encours->load_for_date($PDOdb, $identifiant, $type_object, $year, $month);
			
			$encours->delete($PDOdb);
			break;
	}
}
			
?>