jQuery(document).ready(function() {
	jQuery('[data-toggle="toggle"]').change(function(){
		jQuery(this).parents().next('.hidden').toggle();
	});
});