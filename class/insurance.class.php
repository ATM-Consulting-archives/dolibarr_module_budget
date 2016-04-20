<?php
    
/**
 * Class TBudget
 */
class TInsurance extends TObjetStd {
		
	public $percentage;
	public $date_debut;
	public $date_fin;
	public $fk_project;
	public $label;
	
	
	
	function __construct(){
		
		parent::set_table(MAIN_DB_PREFIX.'sig_insurance');
		parent::add_champs('date_debut, date_fin',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_project',array('type'=>'integer', 'index'=>true));
		parent::add_champs('percentage',array('type'=>'float'));
		
		parent::_init_vars('label');
		parent::start();
	}
	
	
}



class TInsuranceLines extends TObjetStd{
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_insurance_line');
		parent::add_champs('code_compta');
		parent::add_champs('percentage',array('type'=>'float'));
		parent::add_champs('fk_insurance',array('type'=>'integer', 'index'=>true));
		
		
		parent::_init_vars();
        parent::start();

	}
}
