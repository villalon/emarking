<div class="container" style="padding-bottom: 20px;">
<div class="col-sm-1"></div>
<div class="col-sm-7">
<?php if (isloggedin()) {

?>
<div class="form-group">
<form action="" method="post">
<textarea class="form-control" rows="5" name="comment">Comenta!</textarea>
 <br><button name="submit" type="submit" class="btn btn-primary  pull-right" ><i class="fa fa-share"></i> Comentar</button>
 </form>
<?php }else{?>
<p>Para comer comentar la actividad debes estar logueado. <p>
<?php }?>
</div>	
</div>
</div>
	<?php 
	if(isset($comments)&& $comments!=null){
		krsort($comments);
foreach($comments as $comment){
	$date=$dias[date('w',$comment->timecreated)]." ".date('d',$comment->timecreated)." de ".$meses[date('n',$comment->timecreated)-1]. " del ".date('Y',$comment->timecreated) ;
?>
	<div class="container bootstrap snippet">
		<div class="col-sm-1"></div>
		<div class="col-sm-8">
		
			<div class="panel panel-white post panel-shadow">
				<div class="post-heading">
					<div class="pull-left image">
						<img src="http://bootdey.com/img/Content/user_1.jpg"
							class="img-circle avatar" alt="user profile image">
					</div>
					<div class="pull-left meta">
						<div class="title h5">
							<a href="#"><b><?=$comment->username;?></b></a> comentó esta actividad.
						</div>
						<h6 class="text-muted time"><?=$date?></h6>
					</div>
				</div>
				<div class="post-description">
					<p><?=$comment->post;?></p>
					<div class="stats">
						<a href="#" class="btn btn-default stat-item"> <i
							class="fa fa-thumbs-up icon"></i><?=count($comment->likes);?>
						</a>
						<a href="#" class="btn btn-default stat-item"> <i
							class="fa fa-thumbs-down icon"></i><?=count($comment->dislikes);?>
						</a>
					</div>
				</div>
				
			</div>
		</div>
	</div>
<?php }}else{?>
<p>Aún no hay comentarios en esta actividad</p>
<?php }?>