// rtest.js
// using ExtJS 4

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
	distances: [
		"Kruskal-Wallis",
		"Absolute-Distance"
	]
};

// detect the page is loaded and DOM is ready for UI
Ext.onReady( function() {

    // field that will be added to the form for uploading the file
    filefield = new Ext.form.FileUploadField({
        fieldLabel: "File",         // text that the user is shown as 
                                    // label of the box
        xtype: 'fileuploadfield',   // indiacates the type of object this
                                    // is
        name: 'file',               // identifier for the server, access
                                    // the file with $_FILES['file']
		anchor: '100%',             // use 100% width availible 
		allowBlank:false,			// cannot submit form unless
									//   a file is given
		value: ''					// blank to start
    });


    // number of desired clades that the dendrogram should be
    // cut in to
    clades = new Ext.form.NumberField({
		name: 'clades',				// $_POST['clades'] in php
		fieldLabel: "Clades",		// label in GUI
		anchor: '50%',				// relative size of filed in GUI
		minValue: '1',				// minimum value that can be input
		value: '2'					// 2 by default
    });

    // number of levels of kruskal-wallis stats to process
    levels = new Ext.form.NumberField({
		name: 'levels',				// $_POST['levels'] in php
		fieldLabel: "Levels",		// label in GUI
		anchor: '50%',				// relative size of field in GUI
		minValue: '1',				// minimum value that can be input
		value: '1'					// 1 by default
    });

	dataset = new Ext.form.TextField({
		name: 'dataset',			// $_POST['outfile'] in php
		fieldLabel: "Data Set Name",	//label in GUI
		anchor: '100%',				// relative size of field in GUI
		value: ''					// if left empty, this becomes
									//  dist-output in PHP
	}); //outfile


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

	distancecombo = new Ext.form.ComboBox({
		name: 'distance',
		fieldLabel: "Measurement",
		mode: 'local',
		store: rjson.distances,
		valueField: 'field1',
		displayField: 'field1',
		triggerAction: 'all',
		forceSelection: true,
		value: rjson.distances[0]
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
		// automatically show minkowski power value when minkowski
		//   is chosen, and hide otherwise
		listeners:{
			select: function()
			{
				var form = Ext.getCmp('form');
				switch(metriccombo.getValue())
				{
					case 'minkowski':
						// create new field to enter p-value	
						minpow.show();
						break;
						
					default: 
						minpow.hide();
						break;
				}
				// rerender form... I don't think this actually does anything
				form.doLayout(); 
			} // select
		} // listeners
    }); // metriccombo

    // Field for Minkowski power
    minpow = new Ext.form.NumberField({
		name: 'p',				// $_POST['p'] in PHP
		fieldLabel: "Minkowski Power", // label in GUI
		anchor: '50%',			// relative size in GUI
		hidden:true,			// hidden to start
		hideTrigger:true,		// no arrows
		value: 2.0,				// 2 by default
    }); // minpow

    // the Get Dendro button
    // when clicked, sends an "Ajax" request to the server,
    // simply put, the form is submitted as an HTML form
    getButton = {
        text: "Go",
        // handler fires when the button is clicked
        handler: function() {
            // create an Ajax request
            //Ext.Ajax.request({
			this.up('form').getForm().submit({
                url: 'run.php',  		// php script that calls the R code
                method: 'POST',         // use POST, reqired for file
                                        // upload
                isUpload: true,         // tell request that file is 
                                        // being uploaded
            }); // Ext.Ajax.request
        } // handler
    }; // getButton

    // create a form to place the buttons and menus
    // this creates and HTML form element deep down in the code
    // which is then submitted to runcluster.php
    form = new Ext.form.FormPanel({ 
        id: 'form', // Ext id to identify the form internally
		frame: true,// gives blueish hue
		title: 'topWord', // puts title at top
        width: 400, // width of panel
        padding: 5,	// internal padding of the elements in the form,
                    // things are less squished to the edges
        // items puts form components (comboboxes,radiogroups,checkboxes,
        // ...) into the form and in the order they appear in the array
        items:
        [filefield,distancecombo,methodcombo,metriccombo,minpow,clades,levels,dataset],
        fbar: [getButton], 	// add the getButton onto the bottom of
                        	// the form in the footer bar
        layout: 'anchor', 	// 'anchor' layout type allows elements to be 
                        	// "anchored" to some relative % of the width
                        	// of the form 
        renderTo: 'filediv' // place this form component in the 'filediv'
                            // div baked into the index.html page
    });
});
