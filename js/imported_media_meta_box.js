jQuery(document).ready( function($) {
	$('#narnoo-imported-media-tabs a').click(function(){
		var t = $(this).attr('href');
		$(this).parent().addClass('narnoo-tab-active').siblings('li').removeClass('narnoo-tab-active');
		$('.tabs-panel').hide();
		$(t).show();
		return false;
	});
	
	$('.narnoo_toggle_fields').click(function() {
		if ($(this).hasClass('narnoo_show_all')) {
			$('.narnoo_toggle_fields').removeClass('narnoo_show_all');
			$('.narnoo_show_id_field').hide();
			$('.narnoo_show_all_fields').show();
			$('.narnoo-other-fields').hide();
		} else {
			$('.narnoo_toggle_fields').addClass('narnoo_show_all');
			$('.narnoo_show_id_field').show();
			$('.narnoo_show_all_fields').hide();
			$('.narnoo-other-fields').show();
		}
		return false;
	});
	
	$('.narnoo_media_table').tableDnD({
		dragHandle: 'narnoo_array_index_td'
	});
});
