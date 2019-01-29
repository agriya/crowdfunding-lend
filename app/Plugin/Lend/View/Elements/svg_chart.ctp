<script src="<?php echo Router::url('/',true). 'js/libs/svg/svg.js';?>"  type="text/javascript"></script>
<div class="col-md-8 text-center svg-projects-width">	
	<?php $url = Router::url(array('controller' => "lends", 'action' => 'lend_svg', 'ext' => 'svg', 'admin' => true), true); ?>
	<object data="<?php echo $url; ?>" type="image/svg+xml" height="180" id="svgObject"></object>
</div>