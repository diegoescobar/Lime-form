jQuery(document).ready(function($) {
	$('form[ajax="1"]').each(function(){
		$(this).submit(function(){
			var data = $(this).serializeArray();
			
			form_id = jQuery(this).closest('form').attr('id');
			jQuery('#' + form_id + ' .error').remove();
			
			var form_name = $(this).find('input[type="submit"]').attr('name');
			data.push({name:'name', value:form_name});
			$.ajax({
				  url: "",
				  method: "POST",
				  data: data,
			        success: function(data){
					        	console.log(data);
					        	if (data){
							        	var errors = jQuery.parseJSON(data);
							        	//console.log( errors );
							        	if (errors){
								        	$.each(errors, function(obj, values){
								        		 $('#' + form_id + ' #'+obj).parent('div').parent('div').append( '<span class="error">' + values + '</span>' );
								        	});
							        	}
							        	
					        	}else if(data.length == 0){
					        		$('#' + form_id ).html('<h2>Form Successfully Submitted</h2>');
					        		
					        	}
				          }
				      });

			return false;
		});
	});
	
	$('.datepicker').each(function(){
		var picker_id = $( this ).attr('id')
		 $( '#'+picker_id ).datepicker();
	});
});