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
		
		$this->setChild('TInsuranceLines','fk_insurance');
	}
	
	
	static function getInsurance(&$PDOdb, $fk_project) {
		$sql = "SELECT rowid";
		$sql.=" FROM ".MAIN_DB_PREFIX."sig_insurance";
		if(!is_array($fk_project))
			$sql.=" WHERE fk_project=".$fk_project;
		else
			$sql.=" WHERE fk_project IN (".implode(',', $fk_project).")";
		$sql.=" ORDER BY date_debut ";
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		$TBudget = array();
		foreach($Tab as $row) {
			
			$insurance=new TInsurance();
			$insurance->label; //loader l'objet insurance
			if($byMonth) {
				$year = (int)date('Y', $budget->date_debut);
				$month = (int)date('m', $budget->date_debut);
				if($byMonth == 'ym' ) {
					foreach($budget->TResultat as $code_compta=>$TValues)
					{
						$TBudget[$year][$month][$code_compta]['price'] += $TValues['price'];
					}
				}
				else{
					$TBudget[$month] = $budget;
				}
			}
			else $TBudget[] = $budget;
		}
		return $TBudget;
	}
	
	function getAmountForCode($code_compta) {
		
		foreach($this->TInsuranceLines as &$l) {
			if($l->code_compta == $code_compta) {
				return $l->percentage;
			}
		}
		
	}

	function setAmountForCode($code_compta,$percentage) {
		foreach($this->TInsuranceLines as $k=> &$l) {
			if($l->code_compta == $code_compta) {
				$l->percentage = $percentage;	
				return $k;		
			}
		}
		
		$PDOdb=new stdClass;
		$k = $this->addChild($PDOdb, 'TInsuranceLines');
		
		$this->TInsuranceLines[$k]->code_compta = $code_compta;
		$this->TInsuranceLines[$k]->percentage = $percentage;
		
		return $k;
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
