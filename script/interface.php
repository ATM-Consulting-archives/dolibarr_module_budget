<?php
require '../config.php';

dol_include_once("/budget/class/budget.class.php");
dol_include_once("/budget/class/encours.class.php");
dol_include_once('/sig/class/categorie_comptable.class.php');

if(empty($conf->sig->enabled)) exit('SIGrequire');

// Contrôle d'accès
if (!($user->admin || $user->rights->budget->read) || empty($user->rights->budget->encours->edit)) {
    accessforbidden();
}

$langs->load('budget@budget');

$PDOdb=new TPDOdb;

// Get parameters
_action($PDOdb);

function _action(&$PDOdb) {
	global $user, $conf,$langs;
	
	$encours = new TEncours;
	$put = GETPOST('put');
	
	switch($put) {
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
			
			setEventMessages('encours_validate', array());
			break;
		case 'delete_encours':
			$identifiant=GETPOST('identifiant');
			$type_object = GETPOST('type_object');
			$month = (int) GETPOST('month');
			$year = (int) GETPOST('year');
			$price = GETPOST('price');
			
			$encours->load_for_date($PDOdb, $identifiant, $type_object, $year, $month);
			
			$encours->delete($PDOdb);
			setEventMessages('encours_deleted', array());
			break;
	}
}
			
?>