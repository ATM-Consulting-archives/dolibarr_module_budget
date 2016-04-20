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
		
		/*case 'valid':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 1;
			$budget->user_valid = $user->id;
			
			$budget->save($PDOdb);
			
			setEventMessage('Budget validé');
			_fiche($PDOdb, $budget);
			
			
			break;
		case 'reject':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 3;
			$budget->user_reject = $user->id;
			$budget->save($PDOdb);
			setEventMessage('Budget refusé');
			_fiche($PDOdb, $budget);
			break;
		case 'reopen':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 0;
			$budget->save($PDOdb);
			
			_fiche($PDOdb, $budget);
			break;*/
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
				$insurance->setAmountForCode($code_compta, $data['amount']);
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
	
		if($budget->statut == 0)$TButton[] = '<a class="butAction" href="?action=valid&id='.$insurance->getId().'">'.$langs->trans('Valider').'</a>';
		if($budget->statut == 0)$TButton[] = '<a class="butAction" href="?action=reject&id='.$insurance->getId().'">'.$langs->trans('Refuser').'</a>';
		
		if($budget->statut > 0)$TButton[] = '<a class="butAction" href="?action=reopen&id='.$insurance->getId().'">'.$langs->trans('Reopen').'</a>';
		else $TButton[]='<a class="butAction" href="?action=edit&id='.$insurance->getId().'">'.$langs->trans('Modify').'</a>';
		
		$select_project = _get_project_link($insurance->fk_project);
	}
	else{
		$TButton[]='<a class="butActionDelete" href="?action=view&id='.$insurance->getId().'">'.$langs->trans('Cancel').'</a>';
		
		$TButton[]=$TForm->btsubmit($langs->trans('Valid'), 'bt_submit');
		
		
		ob_start();
		$formProject->select_projects(-1,$insurance->fk_project, 'fk_project');
		$select_project =ob_get_clean();
	}

	$TLine = _get_lines($PDOdb,$TForm, $insurance);

	//$TInsurance = TBudget::getInsurance($PDOdb, $insurance->fk_project,false, '0,1,3');
	
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
				,'fk_project'=>$select_project
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
	
	$sql = 'SELECT rowid,label,date_debut,date_fin,fk_project,percentage';
	$sql.=' FROM '.MAIN_DB_PREFIX.'sig_insurance ins';
	
	$titre = $langs->trans('list').' '.$langs->trans('insurance');
	$THide = array('rowid');
		
	$budget = new TInsurance;
		
	echo $r->render($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>$conf->liste_limit
		)
		,'link'=>array(
			'label'=>'<a href="?action=view&id=@rowid@" />'.img_picto('', 'object_label.png').' @label@</a>'
			
		)
		,'translate'=>array(
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
		
		,'eval'=>array(
			'fk_project'=>'_get_project_link(@val@)'
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
	
	$TCode = TCategComptable::getAllCodeComptable();
	
	$Tab=array();
	
	$TColor=array(
		'fff','f7fafc','eaf2f8','ddeaf4','d0e2ef','c4daeb','b7d3e7'
	);
	
	foreach($TCode as $code_compta=>$label) {
			$Tab[]=array(
				'code_compta'=>$code_compta
				,'label'=>$label
				,'percentage'=>$TForm->texte('', 'TInsuranceLines['.$code_compta.'][percentage]', $insurance->getAmountForCode($code_compta) , 10,30)
				,'color'=>(!empty($TColor[strlen($code_compta)]) ? '#'.$TColor[strlen($code_compta)] : '#fff')
			);
		
		
	}
	
	return $Tab;
}



function _get_project_link($fk_project) {
	global $db,$conf,$langs,$user;
	
	dol_include_once('/projet/class/project.class.php');
	
	$projet=new Project($db);
	if($projet->fetch($fk_project)>0) {
		return $projet->getNomUrl(1);
	}
	else{
		return 'N/A';
	}
	
	
}