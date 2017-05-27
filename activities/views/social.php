<div class="container" style="padding-bottom: 20px;">
<div class="col-sm-1"></div>
<div class="col-sm-7">
<?php if (isloggedin()) {?>
<div class="form-group">
<form action="/" id="commentForm">
<textarea class="form-control" rows="5" id="comment" name="comment" placeholder="Comenta">
</textarea>

<input type="hidden" id="key" name="key" value="<?php echo sesskey();?>">
 <br>
 <button name="submit" class="btn btn-primary  pull-right"  value="Comentar">
 	<i class="fa fa-share"></i> 
 	Comentar
 	</button>
 </form>
<?php }else{?>
<p>Para comentar la actividad debes estar logueado. <p>
<?php }?>
</div>	
</div>
</div>

<div id="commentdiv">

</div>
<?php 

if(isset($comments)&& $comments!=null){
	krsort($comments);

	function addComment($comment, $date) {
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
						<button  class="btn btn-default stat-item"> <i
							class="fa fa-thumbs-up icon" onclick="foo()"></i><?=count($comment->likes);?>
						</button>
						<button class="btn btn-default stat-item"> <i
							class="fa fa-thumbs-down icon"></i><?=count($comment->dislikes);?>
						</button>
					</div>
				</div>
				
			</div>
		</div>
	</div>

	<?php
}


foreach($comments as $comment){
	$date=$dias[date('w',$comment->timecreated)]." ".date('d',$comment->timecreated)." de ".$meses[date('n',$comment->timecreated)-1]. " del ".date('Y',$comment->timecreated) ;
		addComment($comment, $date);
 }}?>
<script>


	$("#commentForm").submit(function(event) {

		/* stop form from submitting normally */
		event.preventDefault();

		$.ajax({
			url:"activity.php", //the page containing php script
			type: "GET", //request type
			data: {
				'id': <?php echo $activity->id; ?>,
				'submit':true,
				'sesskey': $("#key").val(),
				'comment': $("#comment").val()
			},
			success:function(result){

				comment = JSON.parse(result);
				printComment(comment.username, comment.date, comment.post, 0, 0);
				$("#comment").val("");
			},
		});

	});


	function printComment(username, date, comment, likes, dislikes){
		html = 
			'<div class="container bootstrap snippet">'+
		'<div class="col-sm-1"></div>'+
		'<div class="col-sm-8">'+
		
			'<div class="panel panel-white post panel-shadow">'+
				'<div class="post-heading">'+
					'<div class="pull-left image">'+
						'<img src="http://bootdey.com/img/Content/user_1.jpg" '+
							'class="img-circle avatar" alt="user profile image">'+
					'</div>'+
					'<div class="pull-left meta">'+
						'<div class="title h5">'+
							'<a href="#"><b>'+ username +'</b></a> comentó esta actividad.'+
						'</div> '+
						'<h6 class="text-muted time">'+ date +'</h6>'+
					'</div>'+
				'</div>'+
				'<div class="post-description">'+
					'<p>'+ comment +'</p>'+
					'<div class="stats">'+
						'<button  class="btn btn-default stat-item"> <i '+
							'class="fa fa-thumbs-up icon" onclick="foo()"></i>'+ likes +
						'</button>'+
						'<button class="btn btn-default stat-item"> <i '+
							'class="fa fa-thumbs-down icon"></i>'+ dislikes +
						'</button>'+
					'</div>'+
				'</div>'+
				
			'</div>'+
		'</div>'+
	'</div>';

		$('#commentdiv').prepend(html);


	}


</script>

