<?php

/**
 * Class TEncours
 */
class TEncours extends TObjetStd {
	public $ca;
	public $year;
	public $month;
	public $price;
	public $fk_project;
	public $encours_taux;
	
	function __construct($ca, $taux) {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_encours');
		parent::add_champs('year, month',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_object',array('type'=>'integer', 'index'=>true));
		parent::add_champs('price',array('type'=>'float'));
        parent::start();

		$this->ca 			= $ca;
		$this->encours_taux = $taux;
		
		$this->TResultat 			= array();
		$this->TBudgetLine 			= array();
	}
}