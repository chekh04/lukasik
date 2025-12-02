<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> Module Installed
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="buttons">
					<a class="btn btn-default" title="" data-toggle="tooltip" href="<?php echo $cancel; ?>" data-original-title="<?php echo $text_cancel; ?>"><i class="fa fa-reply"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $footer; ?>
