<?php

require 'config.php';

dol_include_once("/budget/class/insurance.class.php");
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
	global $user, $conf;
	
	$insurance = new TInsurance;
	$action = GETPOST('action');
	
	switch($action) {
		
		case 'valid':
			$id=(int)GETPOST('id');
			$insurance->load($PDOdb, $id);
			$insurance->statut = 1;
			$insurance->user_valid = $user->id;
			
			$insurance->save($PDOdb);
			
			setEventMessage('Assurance validé');
			_fiche($PDOdb, $insurance);
			
			
			break;
		case 'reject':
			$id=(int)GETPOST('id');
			$insurance->load($PDOdb, $id);
			$insurance->statut = 3;
			$insurance->user_reject = $user->id;
			$insurance->save($PDOdb);
			setEventMessage('Assurance refusé');
			_fiche($PDOdb, $insurance);
			break;
		case 'new':
		
			_fiche($PDOdb, $insurance, 'edit');
			break;
		
		case 'edit':
			$id=(int)GETPOST('id');
			$insurance->load($PDOdb, $id);
			
			_fiche($PDOdb, $insurance, 'edit');
			break;
		
		case 'view':
			$id=(int)GETPOST('id');
			$insurance->load($PDOdb, $id);
			
			_fiche($PDOdb, $insurance);
			break;
			
		case 'save':
			$id=(int)GETPOST('id');
			$insurance->load($PDOdb, $id);
			
			$insurance->set_values($_REQUEST);
			//var_dump($id, $insurance);
			
			foreach ($_REQUEST['TInsuranceLines'] as $code_compta => $data) {
				$insurance->setAmountForCode($code_compta, $data['percentage']);
			}
			
			$insurance->save($PDOdb);
			
			setEventMessage('Sauvegardé avec succès');
			
			header('Location:?action=view&id='.$insurance->getId());
			
			break;
		default :
			_list($PDOdb);
	}
}


function _fiche(&$PDOdb, &$insurance, $mode='view')
{
	global $langs, $conf,$db;
	
	llxHeader('',$langs->trans('Budget'));
	
	$doli_form = new Form($db);
	$TBS=new TTemplateTBS();
	
	dol_fiche_head();
	
	
	dol_include_once('/core/class/html.formprojet.class.php');
	$formProject = new FormProjets($db);
	
	$TForm=new TFormCore($_SERVER['PHP_SELF'].'?action=save','form_edit_insurance','POST');
	$TForm->Set_typeaff($mode);
	
	echo $TForm->hidden('id', $insurance->getId());
	
	echo $TForm->hidden('action', 'save');

	$TLine=$TButton=array();

	if($mode == 'view') {
		$TButton[] = '<a class="butAction" href="?action=list">'.$langs->trans('Liste').'</a>';
	
		if($insurance->statut == 0)$TButton[] = '<a class="butAction" href="?action=valid&id='.$insurance->getId().'">'.$langs->trans('Valider').'</a>';
		if($insurance->statut == 0)$TButton[] = '<a class="butAction" href="?action=reject&id='.$insurance->getId().'">'.$langs->trans('Refuser').'</a>';
		if($insurance->statut == 0)$TButton[] = '<a class="butAction" onclick="return confirm(\'Êtes vous certain ?\')" href="?action=delete&id='.$insurance->getId().'">'.$langs->trans('Delete').'</a>';
		
		$TButton[]='<a class="butAction" href="?action=edit&id='.$insurance->getId().'">'.$langs->trans('Modify').'</a>';
		

	}
	else{
		$TButton[]='<a class="butActionDelete" href="?action=view&id='.$insurance->getId().'">'.$langs->trans('Cancel').'</a>';
		
		$TButton[]=$TForm->btsubmit($langs->trans('Valid'), 'bt_submit');
	}

	$TLine = _get_lines($PDOdb,$TForm, $insurance);

	
	echo $TBS->render('tpl/insurance.fiche.tpl.php',
		array(
			'line'=>$TLine
			,'buttons'=>$TButton
			//,'insurances'=>$TInsurance
		)
		,array(
			'insurance'=>array(
				'label'=>$TForm->texte('','label',$insurance->label, 80,255)
				,'date_debut'=>$TForm->calendrier('','date_debut',$insurance->date_debut)
				,'date_fin'=>$TForm->calendrier('','date_fin',$insurance->date_fin)
				, 'statut'=>$insurance->Tstatut[$insurance->statut]
			)
			,'langs'=>$langs
		)
	);
	
	echo $TForm->end_form();
	
	dol_fiche_end();
		
	llxFooter();
}


function _list(&$PDOdb)
{
	global $langs;
	
	llxHeader('',$langs->trans('ListInsurance'));
	dol_fiche_head();
	
	$r = new TListviewTBS('listI');
	
	$sql = 'SELECT rowid,label,date_debut,date_fin, statut';
	$sql.=' FROM '.MAIN_DB_PREFIX.'sig_insurance ins';
	
	$titre = $langs->trans('list').' '.$langs->trans('insurance');
	$THide = array('rowid');
		
	$insurance = new TInsurance;
		
	echo $r->render($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>$conf->liste_limit
		)
		,'link'=>array(
			'label'=>'<a href="?action=view&id=@rowid@" />'.img_picto('', 'object_label.png').' @label@</a>'
			
		)
		,'translate'=>array(
			'statut'=>$insurance->TStatut
		)
		,'hide'=>$THide
		,'liste'=>array(
			'titre'=> $titre
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'messageNothing'=>"Il n'y a aucun ".$langs->trans('budget')." à afficher"
			,'picto_search'=>img_picto('','search.png', '', 0)
		)
		,'title'=>array(
			'rowid'=>'ID'
			,'date_debut'=>$langs->trans('DateStart')
			,'date_fin'=>$langs->trans('DateEnd')
			,'label'=>$langs->trans('Label')
		)
		,'type'=>array(
			'date_debut'=>'date'
			,'date_fin'=>'date'
		)
		,'orderBy'=>array(
			'date_debut'=>'DESC'
		)
	));
		
	dol_fiche_end();
	llxFooter();
}



function _get_lines(&$PDOdb,&$TForm,&$insurance) {
	global $langs;
	$TBigCateg = TCategComptable::getStructureCodeComptable();
	
	$Tab=array();
	
	$TColor=array(
		'','b7d3e7','ddeaf4','f7fafc','fff'
	);
	foreach($TBigCateg as $label=>$TCateg) {
		$Tab[]=array(
			'code_compta'=>$label
			,'label'=>$TCateg['libelle']
			,'percentage'=>''
			,'color'=>'#c4daeb'
		);
		if(!empty($TCateg['subcategory']))
		{
			foreach($TCateg['subcategory'] as $TSubCateg) {
				$code_compta = $TSubCateg['code_compta'];
				$Tab[]=array(
					'code_compta'=>$code_compta
					,'label'=>$TSubCateg['label']
					,'percentage'=>$TForm->texte('', 'TInsuranceLines['.$code_compta.'][percentage]', $insurance->getAmountForCode($code_compta) , 10,30)
					,'color'=>(!empty($TColor[strlen($code_compta)]) ? '#'.$TColor[strlen($code_compta)] : '#fff')
				);
			}
		}
	}
	
	return $Tab;
}
	
	
