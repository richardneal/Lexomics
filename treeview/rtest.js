//8-24-11 Added code to hide Download XML button when pdf is selected as output type
//8-18-11 Added code to hide download xml button when receiving xml input
//8-12-11 Cleaned up Height and Width slider codes. Added code to hide the sliders when pdf was selected as the output type(Donald)
//8-11-11 Added additional comments Added ability to specify Height and Width(Donald)
//8-10-11 Added download XML button (Donald)

// object that contains arrays with data to populate the comboboxes
// for options to send to R
rjson = {
    metrics: [ 
        "euclidean", 
        "maximum", 
        "manhattan", 
        "canberra", 
        "binary", 
        "minkowski" 
    ],
    methods: [ 
        "average", 
        "ward", 
        "single", 
        "complete", 
        "mcquitty", 
        "median",
        "centroid" 
    ],
    filetypes: [ 
        "pdf", 
        //"svg", 
        "phyloxml" 
    ]
};

Ext.onReady( function() {

    // Ext.get returns the Ext.Element object of the corresponding
    // HTMLElement with the id in the parameter
    //dend = Ext.get('dendro');   // div that is updated with the dendro
    /*resizable = new Ext.Resizable( 'dendro', {
        minWidth: 400,
        minHeight: 400,
        width: 400,
        height: 400,
        resizeChild: true,
        handles: 's e se',
        wrap: true, pinned: true }); resizable.on( 'resize', function( t,w,h,e ) {
        dend.setXY( [ w,h ] );
    });*/
    dend = Ext.get('dendro');   // div that is updated with the dendro

    dend.on( 'DOMSubtreeModified', function( e,t,o ) {
        tt = Ext.fly( t );
        svg = tt.child( 'svg' );

        if ( !svg )
            return;

        dend.setWidth( tt.getWidth() );
        dend.setHeight( tt.getHeight() );
       
    });


    f = Ext.get('filediv');     // the div with the form

	
	phyloType = new Ext.form.RadioGroup({
		xtype: 'radiogroup',
		fieldLabel: 'Layout',
		cls: 'x-check-group-alt',
		hidden: true,
		items: [{boxLabel:'Tree', name:'phyloType', inputValue: 1, checked: true},
				{boxLabel:'Circular',name:'phyloType',inputValue: 2}]
	});


    // hidden textfield that hold the input file type
    hiddentype=new Ext.form.Hidden({
		xtype: 'hidden',
		name:'type',     // used by $_POST['type']
		value: ''        // this is set when a file is chosen to be uploaded
		         // which is under the 'fileselected' listener in the 
			 // declaration of filefield
    });

	// make labels collapsible
	labelsField=new Ext.form.Checkbox({
		xtype: 'checkbox',
		checked: false,
		boxLabel: 'Customize Labels',
		hidden: true,
		listeners: {
			check: function(){
				if (labelsField.getValue() == true)
				{
					labels.show();	
				}
				else labels.hide();
			}
		}
	});


    // field that will be added to the form for uploading the file
    filefield = new Ext.form.FileUploadField({
        fieldLabel: "File",         // text that the user is shown as 
                                    // label of the box
        xtype: 'fileuploadfield',   // indiacates the type of object this
                                    // is
        name: 'file',               // identifier for the server, access
                                    // the file with $_FILES['file']
        anchor: '100%',              // use 100% width availible 
	value: '',
	listeners:{
	    'fileselected': function(){ // when the file is changed
			labelsField.hide();
			labelsField.setValue(false);
			labels.hide();	
		    var form = Ext.getCmp('form'); // get the form
		    var ftype = this.value.split("."); // get the extension	
			hiddentype.setValue(ftype[ftype.length-1]); // sets value for POST
		    if (ftype[ftype.length-1]=='xml'){
                        	// when anything else is selected, hide them
                dendrotitle   .hide();
                methodcombo   .hide();
				labels.hide();
				labelsField.setValue(false);
				labelsField.hide();
                metriccombo   .hide();
				minpow	      .hide();
                typecombo     .hide();
				if(typecombo.getValue() == 'pdf') 
				//the sliders are hidden when pdf is the output type,
				// and switching to using a phyloxml input type stops
                        	// pdf from being the output, so it the output is pdf 
				//when the input type changes the sliders need to be
				// shown
				{
					phyloType     .show();
					xSlider.show();
					ySlider.show();	
				}
				downloadButton.hide();
			}
			else { // change form to deal with .tsv input
				// show the relevent R clustering options
				// when tsv is selected
               	// methodcombo, etc. are in scope via the closure
				labels.hide();
				labelsField.hide();
				labelsField.setValue(false);
				dendrotitle   .show();
				methodcombo   .show();
				metriccombo   .show();
				if (metriccombo.getValue()=='minkowski')
				{	
					minpow.show();
				}
				else minpow.hide();
				typecombo     .show();
				if(typecombo.getValue() == 'pdf')
				//the sliders are hidden when pdf is the output type, 
				//and switching to using a phyloxml input type stops pdf
				// from being the output, so it the output is pdf when
				// the input type changes back to tsv the sliders need 
				//to be rehidden
				{
					phyloType     .hide();
					xSlider.hide();
					ySlider.hide();	
				}
				downloadButton.hide();
			}
		form.doLayout();
	    }
	}
    });

    // comboboxes that contain R clustering options
    // this box is like all the others, see this for comments
    methodcombo = new Ext.form.ComboBox({
        name: 'method',         // name used to identify field, sent
                                // to server in POST with id 'method'
        fieldLabel: "Linkage Method",
        mode: 'local',          // indicate that box is populated by
                                // local array
        store: rjson.methods,   // said local array in rjson object at top
        valueField: 'field1',   // the value in array, ie only elt., 
                                // that is sent to the server as the value
        displayField: 'field1', // value shown in box
        triggerAction: 'all',   // nobody know what this does
        forceSelection: true,   // force value typed into box to be
                                // a value in the array
        value: rjson.methods[0]
    });

    // ditto
    metriccombo = new Ext.form.ComboBox({
        name: 'metric',
        fieldLabel: "Distance Metric",
        mode: 'local',
        store: rjson.metrics,
        valueField: 'field1',
        displayField: 'field1',
        triggerAction: 'all',
        forceSelection: true,
        value: rjson.metrics[0],
	//added functionality to add minkowski power value
	listeners:{
		select: function(g,r,i){
			// g: this
			// r: data record
			// i: index
			var form = Ext.getCmp('form');
			switch(rjson.metrics[i])
			{
				case 'minkowski':
					// create new field to enter p-value	
					minpow.show();
					break;
					
				default: 
					minpow.hide();
					break;
			}
			// rerender form
			form.doLayout(); 
		}
	}
    });

    // ditto
    typecombo = new Ext.form.ComboBox({
        name: 'outputtype',
        fieldLabel: "Clustering Output Type",
        mode: 'local',
        store: rjson.filetypes,
        valueField: 'field1',
        displayField: 'field1',
        triggerAction: 'all',
        forceSelection: true,
        value: rjson.filetypes[0],
		listeners:{
            // listeners are fire when events happen
            select: function( g,r,i) {
                // g: the Combobox, ie 'this'
                // r: the data record returned
                // i: index of the selected item

                // get the form component, could ignore this line
                // and the variable 'form' would still be in closure
                // of this function, this form is local however,
                // this line literally adds nothing of value to the code
                var form = Ext.getCmp( 'form' );

                // determine what to do based upon the filetype to output to
                switch(rjson.filetypes[i])
                {
                    case 'pdf' :
                        // hide the slider and download xml button when pdf is selected
						xSlider.hide();
						phyloType.hide();
						ySlider.hide();	
						downloadButton.hide();
						labels.hide();
						labelsField.hide();
						labelsField.setValue(false);
        				break;
					default :
                        // when anything else is selected show the sliders and download XML button
						xSlider.show();
						phyloType.show();
						ySlider.show();	
						downloadButton.show();
                        break;
                }
                // tell the form to rerender
 				form.doLayout();
	    	}
        }
    });


    // field for name of dendrogram
    dendrotitle = new Ext.form.TextField({
        name: 'title',
        fieldLabel: "Dendrogram Title",
        anchor: '100%',
        value: "Dendrogram",
    });

	// this is the dive where the table will be places... 
    labels = new Ext.form.TextArea({
		name:'labels',
		hidden: true,
		fieldLabel: "Labels (separated by commas)",
		anchor: '100%',
		value:'',
    });

	
	// This is what the php will refer to
	labels2 = new Ext.form.TextArea({
		name:'labels2',
		hidden: true,
	});

    // Field for Minkowski power
    minpow = new Ext.form.NumberField({
		name: 'p',
		fieldLabel: "Minkowski Power",
		anchor: '50%',
		hidden:true,
		value: 2.0,
    });

   //slider for y size (height)
   xSlider = new Ext.Slider({
	width: 200,
	value:800,
        minValue: 400,
	maxValue: 4000,
        fieldLabel: 'Width',
	hidden:true,
	plugins: new Ext.slider.Tip()
   }); 

    //slider for y size (height)
   ySlider = new Ext.Slider({
	width: 200,
	value:800,
        minValue: 400,
	maxValue: 4000,
        fieldLabel: 'Height',
	hidden:true,
	plugins: new Ext.slider.Tip()
   }); 
	

    // the Get Dendro button
    // when clicked, sends an "Ajax" request to the server,
    // simply put, the form is submitted as an HTML form
    getButton = {
        text: "Get Dendro",
        // handler fires when the button is clicked
        handler: function() {
            // remove contents of the dendro div
			//alert(document.getElementById('dendro').innerHTML);
			document.getElementById('dendro').innerHTML="";
			var idstr="";
			var labelstr="";
			var i;
			if(document.getElementById("id_0"))
			{
				for (i=0; i<rowlabels.length; i++){
					idstr="id_";
					idstr=idstr.concat(i);
					if (document.getElementById(idstr).value)
					{
						labelstr=labelstr.concat(document.getElementById(idstr).value);
					}
					else 
					{
						labelstr=labelstr.concat(rowlabels[i]);
					}
					if (i!=(rowlabels.length-1))
					{
						labelstr=labelstr.concat(",");
					}
				}
				labels2.setValue(labelstr);
			}
            // create an Ajax request
            Ext.Ajax.request({
                url: 'runcluster.php',  // url with script
                method: 'POST',         // use POST, reqired for file
                                        // upload
                isUpload: true,         // tell request that file is 
                                        // being uploaded
                form: form.getForm().getEl(),   // get the HTML form 
                                                // element to grab submit
                                                // parameters
                // success fires when runcluster.php is done and has
                // returned a value contained within a page
                success: function( r,o ) {
                    // r: XMLHttpRequest object that can be parsed for 
                    // the value, this object is different in IE,
                    // o: the request parameters

                    // blank the dendro div again
                    //dend.update("")

                    // in Chrome/FireFox/(IE?) to access the returned
                    // json string, must parse the XMLHttpRequest to get
                    // only the text; text/javascript content-type wraps
                    // the entire returned page in <body> tags, this
                    // parse should access only the string we want
                    str = r.responseXML.firstChild.innerText || 
                          r.responseXML.firstChild.textContent;

                    // decode the string into a valid json object that
                    // one can use to access the data
                    json = Ext.decode( str );

                    // json.type is the type of output genereted by the
                    // cluster (ie. svg, phyloxml, jpg, ... )
                    // 'phyloxml' indicates that json.output contains
                    // raw PhyloXML
                    if ( json.type == 'phyloxml' )
                    {
						document.getElementById('container').style.height="auto";
						labels.setValue(json.rowlabels);
						if (hiddentype.getValue()!='xml')
						{
							labelsField.show();
						}
						
                        // use JSPhyloSvg to render the raw XML into
                        // an SVG object in the 'dendro' div on the page
						
						// IF TREE OPTION IS CHOSEN
						type=form.getForm().getValues()['phyloType'];
						if (type==1)
						{
							svgcanvas = new Smits.PhyloCanvas({
                            	phyloxml: json.output,
                        	},'dendro',xSlider.getValue(),ySlider.getValue());
							var bufHeight=ySlider.getValue();
							bufHeight=bufHeight+(0.15*bufHeight);
							$('svg').css("height",bufHeight);
							$('#dendro').css("height",bufHeight);
						}
						else
						{
						// IF CIRCULAR
							svgcanvas = new Smits.PhyloCanvas({
                        		phyloxml: json.output,
                        	},'dendro',xSlider.getValue(),ySlider.getValue(),'circular');
							$('#dendro').css("height",ySlider.getValue());
						}

						rowlabels=json.rowlabels.split(",");
						var i=0;
						var stri = "<table id=\"labelTable\">";
						for (i=0;i<(rowlabels).length;i++)
						{
							stri=stri.concat("<tr>");
							stri=stri.concat("<td>");
							stri=stri.concat(rowlabels[i]);
							stri=stri.concat("</td>");	
							stri=stri.concat("<td><input type=text ");
							stri=stri.concat("id=\"id_");
							stri=stri.concat(i);
							stri=stri.concat("\"/></td>");
							stri=stri.concat("</tr>");
						}
						document.getElementById("x-form-el-ext-comp-1009").innerHTML = stri;


                    }

                    // if the type was svg, just update the 'dendro' div
                    // with the SVG
                    else if ( json.type == 'svg' )
                        dend.update( json.output )
                    // if the type was pdf, do nothing
                    // this method gets the value of the combobox since
                    // nothing is returned in the json 
                    else if ( typecombo.getValue() == 'pdf' )
                        return;
                    // otherwise, something messed up
                    // server error, unexpected type , missing type ...
                    // can't really find out the error 
                    else
                        alert ( "OOPS" );
                }
            });
        }
    };

// the download XML button
    // when clicked, sends an "Ajax" request to the server,
    // simply put, the form is submitted as an HTML form
    downloadButton = new Ext.Button({
        text: "Download XML",
	hidden:true,
        // handler fires when the button is clicked
	handler: function() {
            // remove contents of the dendro div
            dend.update("");

            // create an Ajax request
            Ext.Ajax.request({
                url: 'getXML.php',  // url with script
                method: 'POST',         // use POST, reqired for file
                                        // upload
                isUpload: true,         // tell request that file is 
                                        // being uploaded
                form: form.getForm().getEl(),   // get the HTML form 
                                                // element to grab submit
                                                // parameters
            });
        }
    });

    // create a form to place the buttons and menus
    // this creates and HTML form element deep down in the code
    // which is then submitted to runcluster.php
    form = new Ext.form.FormPanel({ 
        id: 'form', // Ext id to identify the form internally
		frame: true,// gives blueish hue
		title: 'TreeView 1.2', // puts title at top
        width: 500, // width of panel
        padding: 5, // internal padding of the elements in the form,
                    // things are less squished to the edges
        // items puts form components (comboboxes,radiogroups,checkboxes,
        // ...) into the form and in the order they appear in the array
        items:
        [hiddentype,filefield,dendrotitle,methodcombo,metriccombo,minpow,typecombo,xSlider,ySlider,phyloType,labelsField,labels,labels2],
        fbar: [getButton, downloadButton], // add the Get Dendro button and download XML button onto the bottom of
                        // the form in the footer bar
        layout: 'form', // 'form' layout type allows elements to be 
                        // "anchored" to some relative % of the width
                        // of the form 
        renderTo: 'filediv' // place this form component in the 'filediv'
                            // div baked into the index.html page
    });

});
