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

		$this->setChild('TBudgetLine','fk_budget');

		$this->TStatut = array(
			0=>'Brouillon'
			,1=>'Validé'
			/*,2=>'En attente de validation'*/
			,3=>'Refusé'
		);

	}
	
	function getAmountForCode($code_compta) {
		
		foreach($this->TBudgetLine as &$l) {
			if($l->code_compta == $code_compta) {
				return $l->amount;			
			}
		}
		
	}

	function setAmountForCode($code_compta,$amount) {
		foreach($this->TBudgetLine as &$l) {
			if($l->code_compta == $code_compta) {
				$l->amount = $amount;			
			}
		}
		
		$PDOdb=new stdClass;
		$k = $this->addChild($PDOdb, 'TBudgetLine');
		
		$this->TBudgetLine[$k]->code_compta = $code_compta;
		$this->TBudgetLine[$k]->amount = $amount;
		
	}
	
	static function getBudget(&$PDOdb, $fk_project, $byMonth = false) {
		
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid FROM ".MAIN_DB_PREFIX."sig_budget 
		WHERE fk_project=".$fk_project." AND statut=1 ORDER BY date_debut ");
		
		$TBudget = array();
		foreach($Tab as $row) {
			
			$budget=new TBudget;
			$budget->load($PDOdb, $row->rowid);
			
			if($byMonth)$TBudget[(int)date('m', $budget->date_debut)] = $budget;
			else $TBudget[] = $budget;
		}
		
		return $TBudget;
	}
	
}

/**
 * Class TBudgetLine
 */
class TBudgetLine extends TObjetStd {
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget_ligne');
		parent::add_champs('code_compta');
		parent::add_champs('amount',array('type'=>'float'));
		parent::add_champs('fk_budget',array('type'=>'integer', 'index'=>true));
		
		
		parent::_init_vars();
        parent::start();

	}
}