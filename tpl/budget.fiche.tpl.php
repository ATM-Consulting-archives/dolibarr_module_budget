<script type="text/javascript">
function HandSValues(start=false) {
	var time = 500;
	if(start) time = 0;
	$('#rows').toggle(time);
}
	$(document).ready(function(){
		var mode = '[mode;strconv=no]';
		if(mode == 'view') {
			HandSValues(true)
		}else{
			$('#handsvalues').toggle();
		}
		$('#handsvalues a').on('click',function(){
			HandSValues();
			return false;
		});
	});
</script>
<table class="border" width="100%">
	
	<tr>
		<td> [langs.trans(Label);strconv=no] </td>
		<td>[budget.label;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Project);strconv=no] </td>
		<td>[budget.fk_project;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Status);strconv=no] </td>
		<td>[budget.statut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(DateStart);strconv=no] </td>
		<td>[budget.date_debut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(TauxEncours);strconv=no] </td>
		<td>[budget.encours_taux;strconv=no] %</td>
	</tr>
	<tr style="background:#eaf979;">
		<td> [langs.trans(CA);strconv=no] </td>
		<td>[budget.amount_ca;strconv=no]</td>
	</tr>
	<tr style="background:#eaf9aa;">
		<td> [langs.trans(TotalDepense);strconv=no] </td>
		<td>[budget.amount_depense;strconv=no]</td>
	</tr>
	<tr style="background:#eaf2f8;">
		<td> [langs.trans(TotalMarge);strconv=no] </td>
		<td>[budget.total_marge;strconv=no]</td>
	</tr>
	
	<tr id="handsvalues">
		<td colspan="2" align="center">
			<a href="" class="butAction" style="min-width:50%;">[langs.trans(Voir/Cacher le détail)]</a>
		</td>
	</tr>
	<tr id="rows">
		<td colspan="2">
		<table border="0" class="border" width="100%">
			<tr class="[line.class]" style="background-color: [line.color];">
				<td>[line.code_compta;strconv=no;block=tr]</td>
				<td>[line.label;strconv=no]</td>
				<td>[line.amount;strconv=no]</td>
			</tr>
			
		</table>
			
		</td>
	</tr>
	
</table>

<div class="tabsAction">
	<div class="inline-block divButAction">
	[buttons.val;block=div;strconv=no]
	</div>
</div>

<h2>Historique des budgets du projet</h2>
<table border="0" class="border" width="100%">
			<tr budgets.style>
				<td>[budgets.getNomUrl;strconv=no;block=tr]</td>
				<td>[budgets.get_date(date_debut);strconv=no;]</td>
				<td>[budgets.get_date(date_fin);strconv=no;]</td>
				<td align="right">[budgets.amount;frm=0 000,00]</td>
				<td>[budgets.libStatut;strconv=no]</td>
			</tr>
			
</table>
