<table class="border" width="100%">
	
	<tr>
		<td> [title.date_eval;strconv=no] </td>
		<td>[evaluation.date_eval;strconv=no]</td>
	</tr>
	<tr>
		<td> [title.fk_user;strconv=no] </td>
		<td>[evaluation.fk_user;strconv=no]</td>
	</tr>
	<tr>
		<td> [title.fk_emploi;strconv=no] </td>
		<td id="td-emploi">[evaluation.fk_emploi;strconv=no]</td>
	</tr>
	[onshow;block=begin;when [view.action]!='create']
	<tr>
		<td> [title.status;strconv=no] </td>
		<td>[evaluation.statusTrad;strconv=no]</td>
	</tr>
	[onshow;block=end]
	
</table>

[onshow;block=begin;when [view.mode]=='edit']
	<p align="center">[buttons.save;strconv=no]</p>
[onshow;block=end]
[onshow;block=begin;when [view.mode]=='create']
	<p align="center">[buttons.save;strconv=no]</p>
[onshow;block=end]
[onshow;block=begin;when [view.mode]=='view']
	<p align="center">[buttons.other;strconv=no] [buttons.delete;strconv=no]</p>
[onshow;block=end]