$(document).ready(function(){
		Command: toastr[info]('Nuevo post!', 'MURO:')
		toastr.options = {
		  'closeButton': false,
		  'debug': false,
		  'progressBar': false,
		  'positionClass': 'toast-top-center',
		  'onclick': null,
		  'showDuration': '300',
		  'hideDuration': '1000',
		  'timeOut': '5000',
		  'extendedTimeOut': '1000',
		  'showEasing': 'swing',
		  'hideEasing': 'linear',
		  'showMethod': 'fadeIn',
		  'hideMethod': 'fadeOut'
		}
	});

$('#chatFrame').load(function(){
	console.log("chat inició!");
	toastr.info('chat iniciado!');
});

