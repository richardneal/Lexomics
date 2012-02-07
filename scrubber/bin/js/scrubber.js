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
        border: true, // create a border
    });

 
  // define a function that handles errors
  // TODO:
  //  not sure what this should do
  Scrubber.ErrorHandler = function( a, b ) {}

  // give a new namespace, Uploader
Ext.ns( "Uploader" );

// Uploader is designed to be a generic window with a form that uploads
// a file from the user's HD to the server
// all instances must have an Uploader.Form as the 'form' config option
// for upload to be handled properly
Uploader = Ext.extend( Ext.Window, {
    // set some generic information, usually overwitten in call to
    // new Uploader({...})
    id: 'upwin',
    title: 'Upload', 
    width: 500,

    // called after constructor on instantiation
    initComponent: function() {
        // if a form was passed, like is should, use form
        // as main object shown within the window
        if ( this.form )
            Ext.apply( this, {
                items: [ this.form ]
            });
        Uploader.superclass.initComponent.apply( this, arguments );
    }
});

    Scrubber.TextUpload = function() {
    // defines a 'generic' file upload window
    // Uploader type defined below as extension of a window with
    // behavior to act like a window with a submittable form
    // must have an Uploader.Form as the 'form' config option
    var upwin = new Uploader({
        // this form is used to upload the text
        form: new Uploader.Form({
            // action defines the 'action' parameter sent to the server 
            // accessed by $_POST['action'] and acts as the case to
            // a switch statement in action.php
            action: 'uploadtext',
            // success fires on return from the server
            successFn: function( f,a ) {
                // catpure the contents on the json return
                // content-type 'text/javascript' wraps the return
                // in some tags
                var str = f.responseXML.firstChild.innerText || // Ch/Saf
                          f.responseXML.firstChild.textContents;// FF
                var json = Ext.decode( str );   // decode the json object
                                                // from the string
                var results = json.results;     // get the results object
                                                // from the returned json
                                                // object
                var sp = Ext.getCmp( 'sp' );    // get the Scrubber Panel
                                                // component so we can 
                                                // update the textarea
                sp.setValue( results.text );    // use the Panel's
                                                // setValue method to 
                                                // completely replace all
                                                // text in the textarea
                upwin.close();  // close the uploader window
            },
            // failure, not sure if this can fire
            failureFn: function( f,a ) {
                var m = 45;
            }
        })
    });
    upwin.show();   // show the uploader window
  }

// Uploader.Form is a generic form to use with Uploader when opening
// a window with a form to upload a file, lemma list, stopword list, ...
Uploader.Form = Ext.extend( Ext.form.FormPanel, {

    // default submit
    method: 'POST',             // send the data via POST
    url: 'includes/action.php',          // to action.php
    action: 'noaction',         // with no action at the switch
    waitMsg: "Please hold...",  // display a generic please wait

    // callbacks used on success and failure, not sure if failure 
    // actually does anything
    // defaulted to the empty function, ie. do nothing, should
    // be overwritten in creation of instance
    successFn: Ext.emptyFn,
    failureFn: Ext.emptyFn,

    // generic layout
    // no need to mess with, but can be altered for personal preference
    border: false,
    padding: 5,
    layout: 'form',

    // is a file being uploaded in this FormPanel? yes.
    // this causes the request to change from Ajax to a standard HTML
    // form upload,
    // invisible form objects are added to the DOM
    fileUpload: true,

    // oops not fucntional now, 
    // when EnterSubmit object is added, this will be called when the user
    // hit the RETURN key 
    enterSubmit: function() {
        this.upload();
    },

    // function that will be called when form is submitted via a button
    // or when called
    upload: function() {
        var that = this;

        // if the form is valid, ie. the user defined a validity function
        // I don't know how, though
        if ( this.getForm().isValid() )
        {
            // make rudimentary "Ajax" request
            Ext.Ajax.request({
                url: that.url,          // set the parameters of the
                method: that.method,    // request to those that are 
                waitMsg: that.waitMsg,  // default or user-defined
                success: that.successFn,
                failure: that.failureFn,
                params: {
                    action: that.action // use action as a parameter for
                                        // action.php's switch
                },
                form: that.getForm().getEl(),   // get the HTML form from
                                                // the background
                isUpload: true  // it is a file upload

            });
        }
    },

    initComponent: function() {
        // take opportunity to add generic form pieces
        var fp = this;

        // field for file name
        var namefield = {
            fieldLabel: 'Text Name',
            name: 'textname',
            allowBlank: false
        };

        // form for file itself
        // NOTE: C:\fakepath\... is an HTML5 feature to hide the actual
        // location of the file from the browser/server/shoulder surfers
        var filefield = {
            xtype: 'fileuploadfield',   // define the field to contain a 
                                        // file, this xtype is defined
                                        // in FileUploadField.js
            fieldLabel: 'File',
            name: 'file'
        };

        // apply these fields to the form
        Ext.apply( this, {
            defaults: {
                anchor: '100%',     // defults to use 100% width
                xtype: 'textfield'  // default field to accept text
            },

            // add the fields
            items: [ filefield, namefield ],

            // define the upload button
            buttons: [{
                text: "Upload",
                scope: fp,
                handler: fp.upload  // use the upload function defined
                                    // just above to submit the form/file
            }]
        });
        //Uploader.Form.superclass.initComponent.apply( this, arguments );
    },
  });


  // Toolbar functionality
  TextManToolbar = Ext.extend( Ext.Toolbar, {

    // dynamically generate the buttons
    initComponent: function() {
        // download button, item in menu
        var downloadButton = new Ext.menu.Item({
            text: 'Download Scrubbed Text',
            icon: 'icons/disk.png',
            handler: function() {
                form.dom.submit();
            }
        });

        // upload button, menu item
        var uploadButton = new Ext.menu.Item({
            text: "Upload New Text",
            handler: Scrubber.TextUpload,
            icon: 'icons/book_add.png'
        });

        // create button with menu
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
          icon: 'icons/scrub.png',
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
        // in this instance the TabPanel is not wrapped by another panel
        // since no title is needed, this Panel is added directly
        // as a Container
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
        })],
    });
    // get a reference to the HTML element with id "hideit" and add a click listener to it 
    Ext.get("hideit").on('click', function(){
        // get a reference to the Panel that was created with id = 'west-panel' 
        var w = Ext.getCmp('west-panel');
        // expand or collapse that Panel based on its collapsed property state
        w.collapsed ? w.expand() : w.collapse();
    });
});
/**
// scrubber.js
// defines a Scrubber tool and all necessary components needed
// to make the tool work

// onReady will fire when JavaScript is ready to be run, usually 
// after all the HTML is loaded and parsed
Ext.onReady( function() {
    // setthing this to true is probably for the best
    //Ext.USE_NATIVE_JSON = true;

    // create a new Scrubber.Panel,
    // this short code bit creates all the menus, textarea, windows
    // needed to work
});

// use namespace "Scrubber" so one can willy-nilly use Scrubber. notation
// without causing errors
// this is regarded as standard practice to put you app into its own
// namespace
  var left = new Ext.BoxComponent({
    region: 'left',
    layout: 'border',
    split: true,
    minSize: 100,
    width: 235,
  })

// the Scrubber itself
// new Scrubber.Panel({...}); will give all the tools needed to scrub
// this extends the Ext.Panel component so it will behave like a Panel
// unless options are overridden


// the textarea where the user types and edits their text,
// extends the Ext.form.TextArea component which is just an augmented
// HTML <textarea>

// the toolbar, an augmented Ext.Tooblar, that contains all the menus
// and buttons to automagically editing the TextArea
// when creating an instance of this, a 'textarea' must be assigned
// as a config option to indicate the TextArea to update
Scrubber.Toolbar = Ext.extend( Ext.Toolbar, {

    // method fired when instance of Scrubber.Toolbar is created,
    // ie. when new is used

// a button that defaults to a dropdown menu
Scrubber.Toolbar.DropMenu = Ext.extend( Ext.Button, {

    initComponent: function() {
        
    }

});

// a function that opens a text upload window and defines a form that
// uploads the file

// the file option menu, contains things like open, save, save to divitext
Scrubber.Toolbar.FileMenu = Ext.extend( Scrubber.Toolbar.DropMenu, {
    text: "File Options",   // the text shown on the menu
    menu: new Ext.menu.Menu({   // the menu, by including the 'menu' 
                                // config option, the button is defined
                                // as a dropdown menu
        items: [    // each item is defaulted to an Ext.Button
            // the open button, the handler opens a dialog box with a
            // file input field
            {text: "Upload", handler: Scrubber.TextUpload},
            // save button, does nothing now, eventually saves contents
            // of textarea to user's hard drive
            {text: "Download"}
            // eventually a save to divitext button too, maybe
        ]
    })

});

// 'quick edits' menu, similar to file menu, but generated dynamically
Scrubber.Toolbar.QuickEditMenu = Ext.extend( Scrubber.Toolbar.DropMenu, {
    text: "Quick Edits",

    // initComponent runs after the constructor but before the component
    // is rendered. menu buttons are generated dynamically becuase
    // the functionality is slightly more complex 
    initComponent: function() {
        // get some local (and smaller) variables for 'this' and
        // the textarea the buttons operate on
        var that = this;
        var ta = this.textarea;

        // building the menu, will be added as the 'menu' object in the 
        // call to Ext.apply later
        var menu = new Ext.menu.Menu({
            // default the scope of the button handlers to the textarea
            // so the handlers can use 'this' as a reference to the
            // textarea itself, DOMWindow or something else
            defaults: {
                scope: ta
            },

            // items, each item in the array in an Ext.Button
            items: [{ 
                text: "Force Lowercase",    // text on button
                handler: ta.toLower,        // when the button is clicked
                                            // call the textarea's toLower
                                            // function
                scope: ta   // ensure the scope, not really needed since
                            // scope is already defaulted to 'ta'
            },{
                text: "Force Uppercase",    // ditto
                handler: ta.toUpper,
                scope: ta
            },'-',{     // '-' indicates a horizontal spacer
                text: "Remove Newlines",
                handler: function() {
                    // more complex handler, anonymous function with a 
                    // call to the textarea's replace function replacing
                    // newlines with a single space
                    ta.replace( /(\r\n|\n)/g, " " );
                },
                scope: that // scope set to 'this' so 'this' refers to
                            // the menu currently being built
            },'-',{
                // ditto
                text: "Whitespace to Single Space",
                handler: function() {
                    ta.replace( /\s+/g, " " );
                },
                scope: that
						},'-',{
								// Function that is mapped to strip tags.
								text: "Strip tags",
								menu: new Ext.menu.Menu({
									items: [{
											text: "XML",
											handler: function() {
												Ext.Ajax.request({
													url: 'scrub.php',
													params: { string: "test", type: "xml" },
													method: 'POST',
													success: function (result, request) {
														Ext.MessageBox.alert('The scrubbed data was successfully created');
													},
													failure: function (result, request) {
														Ext.MessageBox.alert('Unfortunately, the data you uploaded could not be scrubbed.');
													}
												})
											}
									}]
								})
            },'-',{
                // Trim is a menu within a menu
                text: "Trim",
                // menu object that defines dropdown functionality
                menu: new Ext.menu.Menu({
                    // items are Ext.Button by default
                    items: [{
                        text: "All",
                        handler: ta.trim,   // handler is the textarea's 
                                            // trim method
                        scope: ta           // scope is set, again, so
                                            // 'this' refers to the 
                                            // textarea
                    },'-',{
                        text: "Left",
                        handler: ta.ltrim,  // ditto
                        scope: ta
                    },{
                        text: "Right",
                        handler: ta.rtrim,  // ditto
                        scope: ta
                    }]
                })
            }]
        });

        // apply the config object, param #2, to this
        Ext.apply( this, {
            menu: menu  // set 'menu' to the local var 'menu'
        });
        // odd call to the parent's initComponent
        Scrubber.Toolbar.QuickEditMenu.superclass.initComponent.apply( this, arguments );
    }

});

Scrubber.Stopword = Ext.extend( Object, {

    words: [],          // array of words to remove
    ignoreCase: true,   // ignore case or not
    wordsText: '',      // text that defines the array

    separator: "\n",    // single char that separates stopwords

    // regex escaped group of all standard ASCII punct
    punct: "[!\"#$%&'()*+,\\-./:;<=>?@[\\\]^_`{|}~]",

    // constructor is called when 'new' is used
    constructor: function( config ) {
        // config: the object of options used as the parameter
        //         to new Scrubber.Stopword({...})
        //         defines things like wordsText and separator

        // set object instances of variables based on the config param
        // if they exist, otherwise use the default
        this.ignoreCase = config.ignoreCase || this.ignoreCase;
        this.separator = config.separator ? config.separator[0] : this.separator[0];
        this.wordsText = config.wordsText || this.wordsText;

        // create the list of words from wordsText
        this.createStopList();
        
    },

    // separates a string based on the defined separator and put
    // into the instance's 'words' array
    createStopList: function() {
        this.words = this.wordsText.split( this.separator );
    },

    // get the unespaced list of words
    getRawStopwordList: function() {
        return this.words;
    },

    // get the list of stopwords but if raw == false, escape each word
    // for safe regex use
    getStopwordList: function( raw ) {
        if ( raw )
            return this.getRawStopwordList();
        else
            return this.regexEscapeList();
    },

    // getters
    getIgnoreCase: function() {
        return this.ignoreCase;
    },

    getPunct: function() {
        return this.punct;
    },

    // make each word in the 'words' array safe for regex, but does not
    // alter the local instance of 'words', returns a new array
    regexEscapeList: function() {
        var a = new Array();
        for ( var i = 0; i < this.words.length; i++ )
        {
            var w = this.words[i];
            // replace any of the grouped chars with a slash that char
            // and push onto new array of safe words
            a.push( w.replace( /([\[\^\$\.\|\?\*\+\(\)\\])/g, "\\$1" ) );
        }
        return a;
    }
});

// stopword window that gives a simple dialog box for the user to
// enter a single word on a line to remove from the text
Scrubber.Stopword.QuickList = Ext.extend( Ext.Window, {
    title: "Quick Stopword List Applier",
    width: 700,
    height: 500,
    layout: 'vbox', // use vbox layout so textarea fills all of window

    initComponent: function() {
        var ta = this.textarea;

        // create a new Ext.form.textArea for words to be typed
        // 'qlta' == Quick List TextArea
        var qlta = new Ext.form.TextArea({
            width: '100%',  // use all horizontal space
            flex: 1         // give vertical priority to this box
        });

        // panel with instructions
        var words = new Ext.Panel({
            // baked html instructions, can make this more complex by 
            // entering HTML
            html: "Enter one word per line to remove from the current text:",
            border: false,  // no border
            width: '100%',  // use all horizontal space
            padding: 2      // little padding so words are not crammped
        });

        // checkbox to ignore case or not
        var ignorecase = new Ext.form.Checkbox({

        });
    
        // the apply button, creates a stopword list based on the text
        // in 'qlta'
        var button = new Ext.Button({
            text: "Apply",
            handler: function() {   // handler in scope of button clic
                // create a new Stopword list object
                var list = new Scrubber.Stopword({
                    wordsText: qlta.getValue(),     // the value of the
                                                    // textarea with the 
                                                    // list of words
                    ignoreCase: ignorecase.getValue()   // get the value
                                                        // of the checkbox
                                                        // to ignorecase
                                                        // or not
                });
                ta.applyStopwords( list );  // use the textarea (main one
                                            // with user's text file) 
                                            // applyStopwords method which
                                            // uses the list to replace 
                                            // each word in this.words 
                                            // with nothing
            }
        });

        // make this window have two things, the directions and the 
        // area to type their list of stopwords
        Ext.apply( this, {
            items: [ 
                words,
                qlta
            ],
            // in the footer, add the ignore case checkbox and apply
            // button
            fbar: ["Ignore Case:", ignorecase, button]
        });
        Scrubber.Stopword.QuickList.superclass.initComponent.apply( this, arguments );

    }

});




});
**/

