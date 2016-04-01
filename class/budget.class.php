<?php

/**
 * Class TBudget
 */
class TBudget extends TObjetStd {
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget');
		parent::add_champs('fk_type_object','type=chaine;');
		parent::add_champs('fk_object','type=integer;');
		
		parent::_init_vars();
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
		parent::add_champs('label','type=chaine;');
		parent::add_champs('price','type=integer;');
		
		parent::_init_vars();
        parent::start();

	}
	
	
}