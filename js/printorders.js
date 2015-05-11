/**
 * printorders.js
 * 
 * YUI3 based interface for downloading print orders
 */
YUI().use('io', 'json-parse', 'node', 'dump', 'console', 'datatable-mutable', 'panel', 'dd-plugin', function (Y) {

	// Debugging
	// new Y.Console().render();

	// Loading panel for ajax interaction
	var loadingpanel = new Y.Panel({
		srcNode: '#loadingPanel',
		headerContent : '',
		modal: true,
		render: true,
		visible: false,
		centered: true
	});
	loadingpanel.get('boundingBox').setHTML('<img src="'+wwwroot+'/mod/emarking/img/loading.gif" />');

    // Create the datatable with some gadget information.
    var smsField    = Y.one('#sms'),
    	currentExamId=0,
    	currentButton,
        panel;

    result=0;
    // Create the main modal form.
    panel = new Y.Panel({
        srcNode      : '#panelContent',
        headerContent: 'Descargar prueba',
        width        : 350,
        zIndex       : 5,
        centered     : true,
        modal        : true,
        visible      : false,
        render       : true,
        plugins      : [Y.Plugin.Drag]
    });
    
    panel.addButton({
        value  : 'Descargar',
        section: Y.WidgetStdMod.FOOTER,
        action : function (e) {
        	var sms = smsField.get('value');
        	var sms_replace = sms.replace(/\s/g,'');
        	if(sms_replace == ""){
        		alert('Invalid exam id');
        		return false;
        	};
        	var sms_number = isNaN(sms_replace);
        	if(sms_number == true){
        		alert('Invalid exam id, is not number');
        		return false;
        	};
        	
        	 e.preventDefault();
             panel.hide();
             var url = downloadurl+'?sesskey='+sessionkey+'&token=' + smsField.get('value') + '&multi='+multipdfs;
             Y.log(url);
             Y.config.win.open(url);
             // Y.config.win.location.reload();
		}
    });

    panel.addButton({
        value  : 'Cancelar',
        section: Y.WidgetStdMod.FOOTER,
        action : function (e) {
            e.preventDefault();
            panel.hide();
            currentButton.show();
		}
    });

    panel.addButton({
        value  : 'Reenviar código de seguridad',
        section: Y.WidgetStdMod.FOOTER,
        action : function (e) {
            e.preventDefault();
            panel.hide();
            Y.io(downloadurl+'?examid=' + currentExamId + '&sesskey='+sessionkey, callback);
		}
    });

    // When the addRowBtn is pressed, show the modal form.
    Y.all('.downloademarking').on('click', function (e) {
        var url = downloadurl
                +'?examid=' + e.target.getAttribute('examid') 
                + '&sesskey='+sessionkey;
        Y.log(url);
    	// We show the loading panel while we load the whole interface
    	loadingpanel.show();    	
        currentExamId=e.target.getAttribute('examid');
        currentButton = e.target;
        currentButton.hide();
        Y.io(url, callback);
    });

    // Create the io callback/configuration
    var callback = {

        timeout : 20000,

        on : {
            success : function (x,o) {
                Y.log("RAW JSON DATA: " + o.responseText);

                var messages = [];

                // Process the JSON data returned from the server
                try {
                    messages = Y.JSON.parse(o.responseText);
                }
                catch (e) {
                    alert("JSON Parse failed!");
                    return;
                }

                Y.log("PARSED DATA: " + Y.Lang.dump(messages));

                // Use the Node API to apply the new innerHTML to the target
                if(messages['error']=='') {
                	loadingpanel.hide();
                    panel.show();
				} else {
					alert(messages['error']);
                	loadingpanel.hide();
			        currentButton.show();
			    }
            },

            failure : function (x,o) {
            	Y.log(Y.Lang.dump(o));
            	if(o.statusText === 'timeout') {
            		Y.log('Timeout waiting for SMS async call','error');
                    alert('Se agotó el tiempo de espera para enviar el código. Por favor avise al administrador.');
            	} else {
                    alert('Tuvimos problemas de comunicación con el servidor de mensajes celulares. Por favor reintente más tarde.');
            	}
            	loadingpanel.hide();
            }

        }
    };

    
});