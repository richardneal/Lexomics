Ext.onReady(function(){
  
  Ext.ns( 'Scrubber' );

 Scrubber.TextArea = Ext.extend( Ext.form.TextArea, {

    initComponent: function() {
        // create a new Stopword applier inside of this TextArea
        // ! probably useless now
        //this.stopword = new Scrubber.Stopword({});
    },

    listeners: {
        // when a "special" key is fire (eg. TAB, ENTER, F1, ...)
        // this listener is fired
        specialkey: function( t, e ) {
            // t: target of the event, usually the TextArea
            // e: the event, ie. the keypress event which contains
            //    info on what key was pressed
            
            // the theory is to let the user type a TAB and insert a TAB
            // into the textarea, not make the key cursor jump to the
            // next field or link as happens by default
            // the trick is to figure out where the cursor is and 
            // insert the TAB, there is no good Ext or cross-browser
            // way to do this though
            if ( e.getKey == e.TAB )
            {

            }
        }
    },

    // text editing methods, "all" methods for altering the text should
    // be located here, let the TextArea itself handle the changes to
    // itself
    // methods here alter the value of the textarea
    // TODO:
    //  - add a feature that keeps track of the changes to make an
    //    undo feature

    // can be used as handlers for buttons
    // assumes the button has set scope to the textarea to allow
    // 'this' to indicate the textarea

    // toLower, duh
    toLower: function() {
        // toLocaleLowerCase() should remove some locale issues when
        // dealing in unicode in unique areas of the world
        // the alternative is toLowerCase()
        this.setValue( this.getValue().toLocaleLowerCase() );
    },

    // toUpper, duh, and ditto
    toUpper: function() {
        this.setValue( this.getValue().toLocaleUpperCase() );
    },

    // removes whitespace at beginning and end of file/textarea
    trim: function() {
        this.ltrim();
        this.rtrim();
    },

    // removes whitespace at beginning of file/textarea
    // left trim
    ltrim: function() {
        this.replace( /^\s+/ );
    },

    // removes whitespace at end of file/textarea
    // right trim
    rtrim: function() {
        this.replace( /\s+$/ );
    },

    // these functions are designed to be called by custom button
    // handlers because they take arguments

    // replaces reg for rep, if rep is empty of not defined, removes
    // anything that matches reg
    replace: function( reg, rep ) {
        // reg: a RegExp object to match
        // rep: string to replace matches with, acts as regex, that is,
        //      you can use "$1" to replace the match with first capture
        //      group

        // if the replace is a number/string (anything that evaluates
        // to true) or the number 0, let is be, otherwise make it
        // the empty string, that is remove all matches
        rep = rep || rep === 0 ? rep : "";
        this.setValue( this.getValue().replace( reg, rep ) );
    },

    // applies a passed Scrubber.Stopword object to the TextArea
    applyStopwords: function( list ) {
        // list: a Scrubber.Stopword object
        var w = list.getStopwordList(), // get a RegExp-safe list of
                                        // stopwords
            ic = list.getIgnoreCase(),  // are we ignoring case?
            punct = list.getPunct(),    // get the defined puctuation set
            f = ic ? "ig" : "g";        // set RegExp flags based on case
                                        // ignoring

        // iterate through the list of stopwords creating RegExp and 
        // replaces
        for ( var i = 0; i < w.length; i++ )
        {
            var ww = w[i];  // the current words

            // make the pattern:
            // capture all spaces or punctuation at beginning and end
            // of the word, or the start/end of the file
            var p = '(\\s+|^|' + punct + ')' + ww + '(\\s+|$|' + punct + ')';
            var re = new RegExp( p, f );    // RegExp object with pattern
                                            // and apropos flags
            this.replace( re, "$1$2" );     // call replace with replace
                                            // group set to the captures,
                                            // this removes only the word
                                            // and leave punct/spaces
        }
    }
});

Scrubber.Panel = Ext.extend( Ext.Panel, {

    padding: 5, // internal padding, less squish

    layout: 'form', // format it like a 'form' to use relative widths and
                    // anchor features
    // defaults are applied to objects added in 'items'
    defaults: {
        anchor: '100%', // use all horizontal room
        border: false,  // don't show border
        hideLabel: true,// don't show label
        height: '100%'  // use all vertical room
    },
   
    // fires when the component is initialized after any constructor,
    // nothing has yet been added to the panel,
    // 'this' refers to the Scrubber.Panel object
    initComponent: function() {

        // set this's textarea to a new Scubber.TextArea,
        // this is the large field the text appears in
        this.textarea = new Scrubber.TextArea({});
        var sa = this.textarea; // local variable to aid with less typing
        // create a new Scrubber.Toolbar, this defines the menu that 
        // lives at the top of the panel
        /*this.xtoolbar = new Scrubber.Toolbar({
            textarea: sa    // set the textarea so the Toolbar knows what
                            // TeatArea to operate on
        });
        var st = this.xtoolbar;
*/
        //stwd = new Scrubber.Stopword({});

        // function that sets the value of this.textarea to the value
        // in the first parameter to this function
        this.setValue = function( val ) {
            sa.setValue( val );
        };
        
        // call Ext.apply to put config options into this instance of 
        // the Scubber.Panel
        Ext.apply( this, {
            //tbar: st,       // the top toolbar
            items: [sa]  
        });
        // call Ext.Panel's initComponent function with all the arguments
        // passed to this initComponent function call
        Scrubber.Panel.superclass.initComponent.apply( this, arguments );
    }

});
      var sp = new Scrubber.Panel({
        id: 'sp',                   // Ext's internal id
        title: "ScrubberPanel",     // words to show in header
        //renderTo: 'scrubber-panel', // draw the Scrubber to the 
        autoWidth: true,  // use the full width of the user's browser
        border: true // create a border
    });

    Ext.ns("Uploader");
    
    

Scrubber.TextUpload = function() {
  Ext.Ajax.request({
    url : 'callbacks/scrub.php',
    method: 'GET',
    scope: this, // add the scope as the controller
	  params : { action : 'getDate' },
	  success: function ( result, request ) { 
		  Ext.MessageBox.alert('Success', 'Data return from the server: '+ result.responseText); 
	  },
	  failure: function ( result, request) { 
		  Ext.MessageBox.alert('Failed', result.responseText); 
	  }
  });
}

TextManToolbar = Ext.extend( Ext.Toolbar, {
  initComponent: function() {
    var downloadButton = new Ext.menu.Item({
      text: 'Download Scrubbed Text',
      icon: 'icons/disk.png',
      handler: function() {
        form.dom.submit();
      }
});

  var uploadButton = new Ext.menu.Item({
    text: "Upload New Text",
    handler: Scrubber.TextUpload,
    icon: 'icons/book_add.png'
  });

  var udmenu = new Ext.Button({
    text: "Upload/Download",
    menu: {
      items:[ uploadButton, downloadButton ]
            }
  });

  Ext.apply( this, {
    items: [udmenu]
  });
    TextManToolbar.superclass.initComponent.apply( this, arguments );
    }
  });
  
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    ScrubberManToolbar = Ext.extend( Ext.Toolbar, {
      initComponent: function() {
        var scrubber = new Ext.Button({
          text: "Scrub Text",
          handler: Scrubber.ScrubText,
          icon: 'icons/scrub.png'
        });
        Ext.apply (this, {
          items: [scrubber]
        });
        ScrubberManToolbar.superclass.initComponent.apply( this, arguments );
      }
    });

    var viewport = new Ext.Viewport({
        layout: 'border',
        items: [{
            region: 'west',
            id: 'west-panel', // see Ext.getCmp() below
            title: 'Text Manager',
            split: true,
            width: 250,
            minSize: 225,
            maxSize: 400,
            collapsible: true,
            margins: '0 0 0 5',
            layout: {
                type: 'accordion',
                animate: true
            },
            fbar: new TextManToolbar({})
        },
        new Ext.TabPanel({
            region: 'center', // a center region is ALWAYS required for border layout
            items: [{
                contentEl: 'center',
                handler: Scrubber.Textarea,
                title: 'Scrubbed Text',
                closable: false,
                autoScroll: true
            }],
            fbar: new ScrubberManToolbar({})
        })]
    });
});
