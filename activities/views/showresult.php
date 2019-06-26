<div onClick="location.href='<?= $data->url?>'" style="cursor:pointer;">
	<div class="card">
		<div class="card-header">
			<i class="fa fa-book" aria-hidden="true" title="Género"></i><?=$data->genrename?><h3><?=ucfirst(strtolower($data->title));?></h3>
   			
		</div>
		<div class="card-body">
			<table style="width:100%">
				<tr>
					<td width="100%">
                		<ul>
                			<li><i class="fa fa-pencil-square-o" aria-hidden="true" title="Propósito comunicativo"></i><?=$data->comunicativepurpose?></li>
                			<li><i class="fa fa-bullseye" aria-hidden="true" title="Objetivos de aprendizaje"></i><?=$coursesOA?></li>
                			<li><i class="fa fa-users" aria-hidden="true" title="Audiencia"></i><?= $data->audience?></li>
                			<li><i class="fa fa-clock-o" aria-hidden="true" title="Tiempo estimado"></i><?=$data->estimatedtime?> minutos</li>
                		</ul>
					</td>
				</tr>
				<tr>
					<td colspan="2" width="100%">
	    				<?=$data->description?>
					</td>
				</tr>
			</table>
		</div>
		<div class="card-footer">
			<?=$countcomments?> <i class="fa fa-comment" aria-hidden="true" title="Comentarios"></i> <?=$countvotes?> <i class="fa fa-star-half" aria-hidden="true" title="Votos"></i>
			<?php 
				for ($i=1;$i <= 5;$i++){
					if($i <= $average){
						echo '<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>';
					} else {
						echo '<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
					}
				}
				?>
				<i class="fa fa-user" aria-hidden="true" title="Autor"></i><?= $data->firstname.' '.$data->lastname ?>
		</div>
	</div>
</div>