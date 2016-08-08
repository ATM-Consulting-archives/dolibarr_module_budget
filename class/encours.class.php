<?php

/**
 * Class TEncours
 */
class TEncours extends TObjetStd {
	public $rowid;
	public $price;
	public $fk_object;
	public $type_object;
	public $date_encours;
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_encours');
		parent::add_champs('date_encours',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_object,type_object',array('type'=>'varchar', 'index'=>true));
		parent::add_champs('price',array('type'=>'float'));
		
        parent::start();
	}
	
	function load_for_date(&$PDOdb, $identifiant=null, $type_object='project', $year, $month) {
		
		$month  = str_pad( $month, 2, '0', STR_PAD_LEFT);
		$date_encours = $year.''.$month.'01';
		$sql = 'SELECT rowid';
		$sql.=' FROM '.MAIN_DB_PREFIX.'sig_encours';
		$sql.=' WHERE 1';
		$sql.=' AND YEAR(date_encours) = '.$year;
		$sql.=' AND MONTH(date_encours) = '.$month;
		if(!empty($identifiant)) {
			$sql.=' AND fk_object = \''.$identifiant.'\'';
			$sql.=' AND type_object = \''.$type_object.'\'';
		}
		
		$PDOdb->Execute($sql);
		$PDOdb->Get_line();
		$this->load($PDOdb, $PDOdb->Get_field('rowid'));
	}
	
	static function load_for_identifiant(&$PDOdb, $identifiant=null, $type_object='project') {
		$sql = 'SELECT rowid';
		$sql.=' FROM '.MAIN_DB_PREFIX.'sig_encours';
		if(!empty($identifiant)) {
			$sql.=' WHERE fk_object = \''.$identifiant.'\'';
			$sql.=' AND type_object = \''.$type_object.'\'';
		}
		
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		$TEncours = array();
		foreach($Tab as $row) {
			$encours=new TEncours;
			$encours->load($PDOdb, $row->rowid);
			$year = date('Y', $encours->date_encours);
			$month = (int) date('m', $encours->date_encours);
			$TEncours['@bymonth'][$year][$month]['object'] = $encours;
			$TEncours['@bymonth'][$year][$month]['price'] += $encours->price;
		}
		return $TEncours;
	}
}