<div class="container">
	<a href="<?= $activityUrl?>">
		<center>
			<div style="text-align: left;">

				<div class="row card ">

					<div class="col-xs-4 col-md-3 card-body" style="text-align: left">
						<br>
						<p><?=$coursesOA?>
						<hr>
					    Propósito Comunicativo: <?=$data->comunicativepurpose?><br>
					    Audiencia: <?= $data->audience?><br>
					    Tiempo estimado: <?=$data->estimatedtime?><br>
						
						
						<p>Creado por: <?php echo $userobject->firstname.' '.$userobject->lastname ?>.</p>
					</div>

					<div class="col-xs-1 col-sm-1 col-md-1 col-lg-1"
						style="border-left: 1px solid #cccccc;"></div>


					<div class="col-xs-7 col-md-8 single-result-detail clearfix"
						style="text-align: left">
						<div id="descripcion" class="card-body">

							<span style="font-size: 16px;">Género: <?=$genre->name?></span>
							<h3 class="title_result">
								<b><?=ucfirst(strtolower($data->title));?></b>
							</h3>
							<br>
							<p><?=$data->description?></p>
						</div>

						<div class="row" style="text-align: left">

							<div class="result_list">
								<span
									class="glyphicon glyphicon-comment" aria-hidden="true"
									style="margin-left: 10px;">&ensp;<?=$countcomments?> Comentario(s)&ensp;</span>
							</div>
							<div class="result_list">
								<?php 
								
								for ($i=1;$i <= 5;$i++){
									
									if($i <= $average){
										echo '<span class="glyphicon glyphicon-star" aria-hidden="true"> </span>';
									}
									else {
										echo '<span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span>';
									}
								}
								
								?>
								<span class="glyphicon glyphicon-ok" aria-hidden="true"
									style="margin-left: 10px;"> <?=$countvotes?> voto(s)</span>
							</div>

						</div>

					</div>
				</div>

			</div>
		</center>
	</a>
</div>