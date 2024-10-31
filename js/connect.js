jQuery(function($) {

	$('#TB_window .all_trips').change(function(){
		var sel=$("#TB_window .all_trips").val();
	});

});	
	
function BuildButtonSC(){
	var thistrip = 		jQuery("#TB_window .all_trips").val();
	var button_class = 	jQuery("#TB_window .rezbs_btn").val();
	var button_label = 	jQuery("#TB_window .btn_label").val();
	if( button_class == '' ){ button_class = 'rezbs_button'; }
	if( button_label == '' ){ button_label = 'Book Now!'; }
	
	var theShortcode = '[rezbs_button id="' + thistrip + '" label="' + button_label + '" class="' + button_class + '"]';

	window.send_to_editor(theShortcode);
	tb_remove();
}
