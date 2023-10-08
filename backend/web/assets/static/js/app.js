/*
send form data via ajax and return the data to callback function 
*/
function send_form( name , func )
{
	var url = $('#'+name).attr('action');
	
	var params = {};
	$.each( $('#'+name).serializeArray(), function(index,value) 
	{
		params[value.name] = value.value;
	});
	
	
	$.post( url , params , func );	
}

/*
send form data via ajax and show the return content to pop div 
*/

function send_form_pop( name )
{
	return send_form( name , function( data ){ show_pop_box( data ); } );
}

/*
send form data via ajax and show the return content in front of the form 
*/
function send_form_in( name )
{	
	return send_form( name , function( data ){ set_form_notice( name , data ) } );
}


function set_form_notice( name , data )
{
	data = '<span class="label label-default">' + data + '</span>';
	
	if( $('#form_'+name+'_notice').length != 0 )
	{
		$('#form_'+name+'_notice').html(data);
	}
	else
	{
		var odiv = $( "<div class='form_notice'></div>" );
		odiv.attr( 'id' , 'form_'+name+'_notice' );
		odiv.html(data);
		$('#'+name).prepend( odiv );
	} 
	
}


function show_pop_box( data , popid )
{
	if( popid == undefined ) popid = 'lp_pop_box'
	//console.log($('#' + popid) );
	if( $('#' + popid).length == 0 )
	{
		var did = $('<div><div id="' + 'lp_pop_container' + '"></div></div>');
		did.attr( 'id' , popid );
		did.css( 'display','none' );
		$('body').prepend(did);
	} 
	
	if( data != '' )
		$('#lp_pop_container').html(data);
	
	var left = ($(window).width() - $('#' + popid ).width())/2;
	
	$('#' + popid ).css('left',left);
	$('#' + popid ).css('display','block');
}

function hide_pop_box( popid )
{
	if( popid == undefined ) popid = 'lp_pop_box'
	$('#' + popid ).css('display','none');
}

function layer_change( name , min , max )
{
	var li = $('.'+name).first();
	var now = 0;

	if( typeof li.data('tid') == 'undefined' )
		now = min+1;
	else now = parseInt( li.data('tid') ) + 1;

	if( now > max ) now = min;
	
	li.data( 'tid' , now );
	li.find('img').first().attr('src','static/image/avatar/'+name+'_'+ now +'.png');	

	$('#down_button').css('display','none');
	$('#share_button').css('display','none');
}

function print(link)
{
	html2canvas( $('#theface') , 
	{
  		onrendered: function(canvas) 
  		{
    		//document.body.appendChild(canvas);
    		$('#down_button').attr( 'href' , canvas.toDataURL() ) ;
    		$('#down_button').attr( 'download' , 'myjobdeer.png' ) ;
    		$('#down_button').css('display','inline-block');

  		}
  	});
}

function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    form.setAttribute("target", "_blank");

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}




/* post demo
$.post( 'url&get var'  , { 'post':'value'} , function( data )
{
	var data_obj = jQuery.parseJSON( data );
	console.log( data_obj  );
	
	if( data_obj.err_code == 0  )
	{
					
	}
	else
	{
		
	}	
} );

*/