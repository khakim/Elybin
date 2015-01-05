<?php
  $v 	= new ElybinValidasi();
  $tbp = new ElybinTable('elybin_posts');
  
  $id = $v->sql($_GET['id']);
  $p = $v->sql($_GET['p']);

  // find the tag
  $tbtg = new ElybinTable('elybin_tag');
  $ctag = $tbtg->GetRow('tag_id', $id);
  if($ctag == 0){
	header('location: 404.html');exit;
  }else{
	$tag = $tbtg->SelectWhere('tag_id', $id,'','')->current();
  }
  
  // count post
  $cpost = $tbp->GetRowCustom("`tag` LIKE '%".$tag->tag_id."%' AND `status` = 'publish'");
  if($cpost == 0 OR $id == ''){
	header('location: 404.html');exit;
  }
  
  // pager
  if(!empty($p)){
  	$page = $p;
  	$postposition = ($page-1)*$op->posts_per_page;
  	$post = $tbp->SelectCustom("SELECT * FROM", "WHERE `tag` LIKE '%".$tag->tag_id."%' AND `status` = 'publish' AND `tag` <> '' ORDER BY `post_id` DESC LIMIT $postposition, $op->posts_per_page");
  }else{
  	$page = 1;
    $post = $tbp->SelectCustom("SELECT * FROM", "WHERE `tag` LIKE '%".$tag->tag_id."%' AND `status` = 'publish' AND `tag` <> ''  ORDER BY `post_id` DESC LIMIT 0, $op->posts_per_page");
  }  
  
  // include
  include_once 'header.php';
  include_once 'menu.php';
?>

    <!-- Main Content -->
    <div class="container">
		<div class="clearfix form-group-margin" style="margin-top: 40px;"></div><!-- margin -->
		<?php 
		// show if search result no zero
		if($cpost == 0){
		?>
		<div class="clearfix form-group-margin margin-t"></div>
		<div class="row">
			<div class="col-md-9">
				<h4>No data found.</h4>
			</div>
			<?php include("sidebar.php"); ?>
		</div>
		<?php
		}else{
		?>
		<div class="clearfix form-group-margin margin-t"></div>
		<div class="row">
			<div class="col-md-9">
				<h4 class="pull-right"><?php echo $cpost; ?> post found that contain tag <i class="text-dash"><?php echo $tag->name; ?></i>.</h4>
			</div>
		</div>
        <div class="row">
			<div class="col-md-9">
				<?php
					foreach ($post as $p) {
						// user 
						$tbu = new ElybinTable('elybin_users');
						$user = $tbu->SelectWhere('user_id',$p->author,'','')->current()->fullname;
						$user_id = $tbu->SelectWhere('user_id',$p->author,'','')->current()->fullname;

						//comment
						$tbc = new ElybinTable('elybin_comments');
						$comment = $tbc->GetRow('post_id',$p->post_id,'','');

						// category 
						$tbca = new ElybinTable('elybin_category');
						$category = $tbca->SelectWhere('category_id',$p->category_id,'','')->current();
						
						// tag
						$tag2 = $p->tag;
						if($tag2 !== ''){
							$tag2 = explode(",", $tag2);
							$ctag2 = count($tag2);
							
							if($ctag >= 3) $tag2 = array_slice($tag2, 0, 3);
						}else{
							$ctag = 0;
						}

						//content
						$content = substr(strip_tags(html_entity_decode($p->content)),0,500);
						
						// date 
						$date = explode("-", $p->date);
						$monthpfx = date("M", mktime(0,0,0,$date[1],1,2000));
						
				?>
				<!-- post -->
				<div class="col-md-2">
					<div class="circle-date">
					    <span class="day-prefix">Writed</span>
						<span class="day"><?php echo $date[2]?></span>
						<span class="slash"></span> 
						<span class="month"><?php echo $date[1]?></span>
						<span class="month-prefix"><?php echo $monthpfx?></span>
						<span class="fa fa-calendar"></span>
					</div>
				</div>
				<div class="col-md-10">
					<div class="post-preview">
						<a href="post-<?php echo $p->post_id?>-<?php echo $p->seotitle?>.html">
							<h2 class="post-title">
								<?php echo $p->title; ?>
							</h2>
						 </a>	
						<p class="post-meta"><i class="fa fa-user"></i>&nbsp;Posted by <em><?php echo $user?></em><?php if($comment>0){ ?> got <?php echo $comment?> comments<?php } ?><span class="pull-right hidden-xs"><?php echo time_elapsed_string($p->date.$p->time)?>&nbsp;<i class="fa fa-clock-o"></i></span></p>
						<?php
							if($p->image !== ''){
						?>
						<img src="elybin-file/post/<?php echo $p->image?>" class="img-responsive img-rounded" alt="<?php echo $p->title?>">
						<?php } ?>
						<p class="post-subtitle">
							<?php echo $content; ?>...
						</p>
						

						<?php
						if($ctag > 0){
						?>
						<p class="post-meta">
							<i class="fa fa-tag"></i>&nbsp;&nbsp;
							<?php
								
								foreach ($tag2 as $t) {
									$tbt = new ElybinTable('elybin_tag');
									$tags = $tbt->SelectWhere('tag_id',$t,'','')->current();
							?>
							<a href="tag-<?php echo $t?>-1-<?php echo $tags->seotitle?>.html" class="label bg-light"><?php echo $tags->name?></a> 
							<?php
								}
							if($ctag >= 5){
							?>
							<a class="label bg-light">...</a>
							<?php } ?> 
						</p>
						<?php } ?>
						<div class="row">
							<div class="col-md-2 pull-right">
								<a href="post-<?php echo $p->post_id?>-<?php echo $p->seotitle?>.html" class="btn btn-default pull-right">More &rarr;</a>	
							</div>
							<div class="col-md-10">
								<hr>
							</div>
						</div>
					</div>
				</div>
				<!-- ./post -->
				<?php } ?>
			</div>
			<!-- .col-md-9 / ./post-container-->
			<?php
			if($cpost > 0){
			?>
			<div style="height: 90px;"></div>
			<?php } ?>
<?php
  include 'sidebar.php';
?>
        </div>
        <!-- .row -->
		<?php } ?>
    </div>

    <?php
		$muchpage = ceil($cpost/$op->posts_per_page);
		if($muchpage > 0){
	?>
    <hr>
	<!-- Pager -->
	<div class="pager">
		<h3><?php echo strtoupper($lg_page)?></h3>
		<ul>
			<?php
				if($page > 1){
			?>
			<li><a href="tag-<?php echo $id ?>-<?php echo $page-1?>-<?php echo $tag->seotitle ?>.html" alt="Next"><i class="fa fa-angle-left"></i></a></li>
			<?php } ?>
			<?php
				for($i=1; $i<=$muchpage; $i++){
					if($i == $page){
						$ds = ' class="disabled"';
					}else{
						$ds = '';
					}
			?>
			<li<?php echo $ds?>><a href="tag-<?php echo $id ?>-<?php echo $i?>-<?php echo $tag->seotitle ?>.html" alt="<?php echo $i?>"><?php echo $i?></a></li>
			<?php } ?>
			<?php
				if($page < $muchpage){
			?>
			<li><a href="tag-<?php echo $id ?>-<?php echo $page+1?>-<?php echo $tag->seotitle ?>.html" alt="Next"><i class="fa fa-angle-right"></i></a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
<?php
  include "footer.php";
?>
