
jQuery(document).ready(function($){

		

	$('input#fontc, input#cloudc, input#nodec').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
			
			$('#'+$(el).attr('id') + 'Area').css('background-color','#'+hex);
		},
		onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		}
	})
	.bind('keyup', function(){
		$(this).ColorPickerSetColor(this.value);
	});
	
	$('input#fontf').change(function(){
		$('#nodeFont').css('font-family', $(this).val() );
	});

	$('button.ajaxSave').click(function(e){
		$('input#ajaxSaveHidden').val( $(this).val() );
	});
	
});