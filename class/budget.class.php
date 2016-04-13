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
		parent::add_champs('statut,user_valid,user_reject',array('type'=>'integer'));
		parent::add_champs('amount',array('type'=>'float'));
		
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
	
	function save(&$PDOdb) {
		
		$this->amount = 0;
		
		foreach($this->TBudgetLine as &$l) {
			
			$this->amount += $l->amount; 	
		}
		
		parent::save($PDOdb);
	}
	
	function libStatut() {
		
		return $this->TStatut[$this->statut];
		
	}
	
	function getNomUrl($picto=1) {
		$url = '<a href="'.dol_buildpath('/budget/budget.php?action=view&id='.$this->getId(),1).'" />'.($picto ? img_picto('', 'object_label.png').' ' : '').$this->label.'</a>';
		
		return $url;
	}
	
	function getAmountForCode($code_compta) {
		
		foreach($this->TBudgetLine as &$l) {
			if($l->code_compta == $code_compta) {
				return $l->amount;			
			}
		}
		
	}

	function setAmountForCode($code_compta,$amount) {
		foreach($this->TBudgetLine as $k=> &$l) {
			if($l->code_compta == $code_compta) {
				$l->amount = $amount;	
				return $k;		
			}
		}
		
		$PDOdb=new stdClass;
		$k = $this->addChild($PDOdb, 'TBudgetLine');
		
		$this->TBudgetLine[$k]->code_compta = $code_compta;
		$this->TBudgetLine[$k]->amount = $amount;
		
		return $k;
	}
	
	static function getEncours(&$TReport, &$TDate, &$TBudget) {
		$ca_mois=0;
		
		$TValues=array();
		$TValues[1] = $TValues[0] = array('total'=>' - ','values'=>array());
		
		$encours_mois_m1 = 0;
		
		foreach($TDate as $year => $TMonth) {
			foreach ($TMonth as $iMonth => $month) {
				
				if(!empty($TBudget[$year][$iMonth])) {
						$TValues[0]['values'][] =$TValues[1]['values'][] = array(
											 	'value'=>' - '
											 	,'year'=>$year
											 	,'month'=>$iMonth
											 	,'budget'=>true
											 	 ,'class'=>'budget'
											);
				}

				$encours = 0;
				
				if(!empty($TReport['monthly']['CA'][$year][$iMonth]['price'])) {
					
					$ca = $TReport['real']['CA']['price'];
					$ca_mois = $TReport['monthly']['CA'][$year][$iMonth]['price'];
					
					$encours = $ca - $ca_mois;
				}
				$TValues[0]['values'][] = array(
					'value'=>$encours_mois_m1
					,'month'=>$month
					,'encours'=>true
					 ,'class'=>'month'
				);

				$TValues[1]['values'][] = array(
					'value'=>$encours
					,'month'=>$month
					,'encours'=>true
					 ,'class'=>'month'
				);
				
				$encours_mois_m1 = -$encours;
			}
		}
		
		return $TValues ;
		
	}
	
	static function getBudget(&$PDOdb, $fk_project, $byMonth = false, $statut = 1) {
		
		$Tab = $PDOdb->ExecuteAsArray("SELECT rowid FROM ".MAIN_DB_PREFIX."sig_budget 
		WHERE fk_project=".$fk_project." AND statut IN (".$statut.") ORDER BY date_debut ");
		
		$TBudget = array();
		foreach($Tab as $row) {
			
			$budget=new TBudget;
			$budget->load($PDOdb, $row->rowid);
			
			if($byMonth) {
				
				if($byMonth == 'ym' ) {
					$TBudget[(int)date('Y', $budget->date_debut)][(int)date('m', $budget->date_debut)] = $budget;
				}
				else{
					$TBudget[(int)date('m', $budget->date_debut)] = $budget;	
				}
				
			}
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