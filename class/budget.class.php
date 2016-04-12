<?php

/**
 * Class TBudget
 */
class TBudget extends TObjetStd {
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget');
		parent::add_champs('date_debut, date_fin',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_project',array('type'=>'integer', 'index'=>true));
		parent::add_champs('statut',array('type'=>'integer'));
		
		parent::_init_vars('label');
        parent::start();

	}
	
	
}

/**
 * Class TBudgetLine
 */
class TBudgetLine extends TObjetStd {
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget_ligne');
		parent::add_champs('code_compta','type=chaine;');
		parent::add_champs('price','type=integer;');
		
		parent::_init_vars();
        parent::start();

	}
}