// diviText is a graphical text segmentation tool for use in text mining.
//     Copyright (C) 2011 Amos Jones and Lexomics Research Group
// 
//     This program is free software: you can redistribute it and/or modify
//     it under the terms of the GNU General Public License as published by
//     the Free Software Foundation, either version 3 of the License, or
//     (at your option) any later version.
// 
//     This program is distributed in the hope that it will be useful,
//     but WITHOUT ANY WARRANTY; without even the implied warranty of
//     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//     GNU General Public License for more details.
// 
//     You should have received a copy of the GNU General Public License
//     along with this program.  If not, see <http://www.gnu.org/licenses/>.

/*onunload = function() {
    return confirm( "Are your sure you want to leave?" + 
             " Have you downloaded your texts yet?" );
}*/

Ext.onReady( function() {

    // load quick tips -- no idea what it does, but is alway included ???
    Ext.QuickTips.init();

    // new ChunkViewer for the right-top panel
    var cpupdater = new ChunkViewer({
        title: 'Chunk Viewer',
        id: 'cpupdater',
        border: true,
        autoWidth: false,   // do not use autoWidth, breaks layout
        cpid: 'cp'          // Ext identifier for the CutterPanel a few
                            // lines lower
    });

    // the cutter!
    var cutter    = new DiviCutter({
        id: 'cp',
        updater: cpupdater, // ref to the chunkviewer defined above
        renderTo: 'cutter-panel',   // render to 'cutter-panel' DIV baked
                                    // into index.php
        border: false,
        autoScroll: true,

        // array of hex colors to color each chunk in defined order
        colors: [ '#eecd9c', '#eee9bc', '#d0ddd0', '#ddc4a6' ]
    });

    // the viewport, this takes control over the entire page, all things
    // baked into index.php are hidden and shown if thing (ex. DIV tag
    // with id as parameter)
    DiviViewport  = new Ext.Viewport({

        // layout config
        layout: 'border',   // border allows for the east, center, west,
                            // ... resiable pieces

        // items
        // each item is a Ext.Panel by default and must contain
        // a 'region' config option, 'center' region MUST be defined
        items: [
            // first is a Box that holds the header baked into the page
            // region is north
            new Ext.BoxComponent({
                region: 'north',
                contentEl: 'header'
            }),
            // south region is also baked into the page in the 
            // 'footer' DIV
            new Ext.BoxComponent({
                region: 'south',
                contentEl: 'footer'
            }),

            // panels
            // 'west' is the text manager
            {
                title: "Text Manager",
                region: 'west',
                split: true,    // panel is split so it can be resizable

                minSize: 100,   // minimum width
                width: 235,     // starting width

                layout: 'fit',  // force items to use 100% of the area

                // sub item is a Panel that holds the TextViewer tree
                // and footer with Upload/Download and Merge buttons
                items: [{
                    layout: 'fit',
                    border: false,
                    // item is the TextViewer tree
                    items: [ new TextViewer({id:'text-viewer'}) ]
                }],
                fbar: new TextManToolbar({})    // U/D and Merge buttons
                                                // in an Ext.Toolbar that
                                                // that is put on the 
                                                // bottom of the west cmp
            },
            {
                // east region is the split panels that contain the 
                // chunk viewer and the simple/advance cutter 
                region: 'east',
                split: true,    // split os east/center is resizable
                border: false,

                minSize: 100,
                width: 200,
                
                layout: 'border',   // border layout so we can split 
                                    // the chunk viewer and the s/a 
                                    // cutter panels

                items: [
                    {  
                        // first item is the chunk viewer that resides
                        // on top of the east side of the screen
                        title: 'Chunk Viewer',
                        split: true,        // split so it can be 
                                            // resizable
                        region: 'center',   // must have center with
                                            // border layout
                        layout: 'vbox',     // vbox layout so the CV will
                                            // fill the area on resize
                                            // and use whole area
                        layoutConfig: {
                            align: 'stretch',   // stretch to width
                            pack: 'start'       // start filling items
                                                // from top
                        },
                        items: [
                            // use the updater ref put in the cutter obj
                            // to populate this area, must stick a flex 
                            // into the object so it can have highest
                            // priority for vertical fill
                            Ext.apply( cutter.updater, {flex:1} )
                        ]
                    }, 
                    {    
                        // bottom half of east is the auto cutters
                        title: 'Cutting Tools',
                        height: 400,    // defualt height 
                        region: 'south',
                        split: true,    // split so resizable
                        layout: 'fit',  // use 100% of area for items
                        items:[ 
                            // AutoCutter is accordian panel 
                            // tell it what cutter panel to apply auto
                            // cutters to
                            new AutoCutter({
                                cutterPanel: cutter
                            }) 
                        ]
                    }
                ]
            },
            {
                // center is the cutter, the #!
                // stick it in a panel so the top bar can be updated with
                // the currently edited text
                id: 'cutter-panel-panel',   // Ext id
                title: 'Visual Cutter -- No Text',

                region: 'center',   // must have 'center'
                split: true,        // split for resizablility

                layout: 'fit',      // fit so CP takes 100% of area

                // cutter is the item
                items: [ cutter ]
            }
        ]

    });

    // call reset so the initial colors in CV and CP can be populated
    cutter.reset();
});

// the diviText implementation of the CutterPanel type,
// this defines diviText specific features like submitting a chunkset
// and loading a default text
DiviCutter = Ext.extend( CutterPanel, {
    // define textData to start as nothing, this is what is displayed 
    // in the cutting region
    textData: '',

    // gets the text of defaultText.txt to display initially, this is
    // called in initComponent
    getDefaultText: function() {
        var cp = this;  // get the cutterpanel, ie. this

        // create Ajax request to get defaultText.txt,
        // the request simply dumps the contents of the file into a var
        Ext.Ajax.request({
            method: 'POST',         // POST or GET will work, not sending
                                    // data anyway
            url: 'defaultText.txt', // the file to which the request is
                                    // made
        
            // success is called when the Ajax request returns data from
            // server
            success: function(r,o) {
                // set the text of the CP to what is returned from the 
                // request (param #1), set no textid (param #2) so the
                // DiviCutter knows to prevent the user from being able
                // to save chunksets of the default text
                cp.newText( r.responseText, null );

                // get the footer toolbar, where the user save chunksets,
                // and disable it, visual deterent only
                var ftb = cp.getFooterToolbar();
                if ( ftb )
                    ftb.disable();
            }
        });
    },

    // called when the Submit button in the footer is clicked, when
    // the user ants to save a chunkset
    finalize: function( name ) {
        // name: name of the chunkset
        // 'this' is the DiviCutter itself

        var textid = this.tid;  // get id of text in CP

        // encode the array of spaces as an JSON array,
        // looks something like:
        // "[1,2,12,56,89,100]"
        var spacestr = Ext.encode( this.getSpaces() );

        // really prevent chunkset from being saved
        // if the default text is in the CP, or no name was given
        // as an argument
        if ( !textid || !name )
            return;

        // visually disable the footer while saving a chunkset
        var ftb = this.getFooterToolbar();
        if ( ftb )
            ftb.disable();

        // create Ajax request to submit spaces, chunkset name, and text
        // id in order to chunk the text
        Ext.Ajax.request({
            url: 'modules/texts/chunk.php',
            method: 'POST',

            // set params to be sent via POST to the server,
            // in PHP read as $_POST['foo'], where 'foo' is the left side
            // of the colon in this list, ie. 'name', 'textid', 'spaces'
            params: {
                name: name,         // chunkset name
                textid: textid,     // internal id of text
                spaces: spacestr    // JSON array string
            },

            // success fired when JSON response has success = true param
            // {
            //   success:true,
            //   othervar: "Something in a string.",
            //   ...: ...
            // }
            
            // success is when text is chunked successfully, nothing much
            // to do
            success: function( r, o ) {
                // r: response object, XMLHttpRequest in (Not IE)
                // o: params of the request itself, ie. above

                // reload the text viewer so it's contents reflect the
                // addition of a new chunkset
                // expand the top node of the text viewer since by 
                // default it will close
                var tv = Ext.getCmp( 'text-viewer' );
                tv._reload();
                tv.getRootNode().expand( true );

                // let user be able to access the footer to save more 
                // chunksets, and remove chunkset name from field
                if ( ftb )
                {
                    ftb.enable();
                    ftb.get( 'chunkset-name' ).setValue( '' );
                }
            },
            // open an error report window
            failure: function( r, o ) {
                var response = Ext.decode( r.responseText );
                var errors = response.errors;
                // param #1: array of errors
                // param #2: title of error window
                report_errors( errors, "Chunking Error" );
            }
        }); // end Ajax.request

    }, // end finalize

    // fires when new DiviCutter({...}) is called
    initComponent: function() {
        // first thing, load default text
        this.getDefaultText();

        // get the CP, ie. this
        var cp = this;

        // field for new chunkset names, make it an EnterField so
        // RETURN keypress submits, in theory, does NOT work that way
        var namefield = new EnterField({
            id: 'chunkset-name',            // Ext id of field
            fieldLabel: "Chunkset Name",    // NOT applicable since not 
                                            // in Ext.form.FormPanel
            xtype: 'textfield',             // make the field accept text
            anchor: '50%',                  // 50% width
            allowBlank: false               // force something in the box
        });

        // enterSubmit, required by EnterField to handle RETURN keypress
        // also handler for "Save Chunkset" button
        function enterSubmit() {
            // this refers to the local scope, that is, the scope
            // given to the handler
            // name param of finalize() is the value of the text in 
            // field with chunkset name
            this.finalize( namefield.getValue() );
        };

        // toolbar at bottom of CP for chunkset name entry/saving/reset
        // refered to as FTB in many places, accessed by 
        // cp.getFooterToolbar()
        var chunkerbar = new Ext.Toolbar({
            disabled: true, // disbale the bar be default

            // items are Ext.Buttons by default if an object,
            // strings represent some text to place in the footer
            items: [
                "Chunkset Name:",   // label for box
                namefield,          // box for chunkset name
            {
                // save chunset button
                text: "Save Chunkset",
                icon: 'icons/page_white_stack_add.png',
                handler: enterSubmit,   // handler is enterSubmit just 
                                        // above
                scope: cp               // make handler have scope of the
                                        // CutterPanel                       
            },{
                // reset all chunks made in CP
                text: "Reset",
                icon: 'icons/arrow_undo.png',
                handler: cp.reset,  // function defined by CutterPanel 
                                    // type to remove all spaces
                scope: cp           // scope of the CutterPanel
            }]
        });

        // add the toolbar as the footer toolbar of the CutterPanel
        Ext.apply( this, {
            fbar: chunkerbar
        });
        DiviCutter.superclass.initComponent.apply( this, arguments );
    }


});

// == Auto Cutter Builders ============
// lower left accordion thing

// a sample cutter type, be it Auto or Simple, or any future auto cutter,
// each is a decendent of this type.
// this simply defines a form with a Cut button, what fields are in the 
// form, are decided by the cutter type.
CutterType = Ext.extend( Ext.form.FormPanel, {
    border: false,      // no border
    labelAlign: 'top',  // put field labels on top of box/slider
    layout: 'form',     // form layout so 'anchor' is applicable
    defaults: {
        anchor: '100%', // default field to 100% width
        border: false   // no border on 
    },

    // required function for RETURN keypress
    enterSubmit: function( ) {
        // get the form and submit it if valid
        var form = this.getForm();

        if ( form.isValid() )
        {
            // callCut is defined by the functionality of the autocutter
            this.callCut( form.getValues() );
        }
    },

    initComponent: function() {
        // add a Cut button for all decendent types
        Ext.apply( this, {
            buttons: [
                {
                    formBind: true,         // button is bound to the 
                                            // form so values in the form
                                            // are submitted to ...
                    text: 'Cut',
                    icon: 'icons/cut.png',
                    
                    // when Cut is clicked, fire enterSubmit
                    handler: function() {
                        var fp = this.ownerCt.ownerCt,  // get the form,
                            form = fp.getForm();        // longwinded,
                                                        // see **
                        fp.enterSubmit();
                    }
                    //,scope:this   // **, maybe ??
                }
            ]
        });
        CutterType.superclass.initComponent.apply( this, arguments );
    }
});

// type of field that submits a form when RETURN is pressed
EnterField = Ext.extend( Ext.form.Field, {
    enableKeyEvents: true,  // enable specialKey listener
    xtype: 'textfield',     // default field to text field
    listeners: {
        // fires when keys like RETURN, TAB, F1, F2... are pressed
        specialKey: function( field, e ) {
            // if the key is ENTER/RETURN, call enterSubmit which is 
            // defined by the form that contains an EnterField item
            if ( e.getKey() == e.ENTER )
            {
                var fp = this.ownerCt;  // get the form, the parent of
                                        // the field
                fp.enterSubmit();
            }
        }
    }
});

// the simple cutter form panel, a form by nature
SimpleCutter = Ext.extend( CutterType, {

    initComponent: function() {
        // apply the field to enter the number of chunks
        Ext.apply( this, {
            items: [
                // only item is an EnterField that accepts only numbers
                new EnterField({
                    xtype: 'numberfield',   // accept numbers
                    fieldLabel: 'Chunks',
                    name: 'chunks',         // submit the value in this 
                                            // field with the name 
                                            // 'chunks'
                    allowNegative: false    // force positive
                })
            ]
        });
        SimpleCutter.superclass.initComponent.apply( this, arguments );
    },

    // called by the CutterType method enterSubmit()
    callCut: function( v ) {
        // v: an object with the values in the form, in this case,
        //    an object with one key, chunks, and accompanying value,
        //    the number entered by the user
        
        // this.cutterPanel is a ref to the cutter panel that must be
        // included in new SimpleCutter({...,cutterPanel:cp,...});
        // pass the number of chunks to oneParamSpacer
        this.cutterPanel.oneParamSpacer( new Number( v.chunks ) );

        // alter the name of the chunkset with default name: ###_chunks
        var csname = Ext.getCmp( 'chunkset-name' );
        csname.setValue( v.chunks + "_chunks" );
    }
});

// pretty much ditto from SimpleCutter
AdvancedCutter = Ext.extend( CutterType, {
    initComponent: function() {
        Ext.apply( this, {
            items: [
                // number of words in chunk field
                new EnterField({
                    xtype: 'numberfield',
                    fieldLabel: 'Size',
                    name: 'size',
                    allowNegative: false
                }),

                // slider for last proportion size
                new Ext.form.SliderField({
                    fieldLabel: 'Last Proportion',
                    name: 'last',
                    minValue: .01,
                    maxValue: 1,
                    increment: .01,
                    decimalPrecision: 2,    // force .## when showing tip
                    value: .5               // defualt value of half
                })
            ]
        });
        AdvancedCutter.superclass.initComponent.apply( this, arguments );
    },

    callCut: function( v ) {
        // v: object with values
        // {
        //   size: ##,
        //   last: .##
        // }
        
        // the CP's threeParamSpacer method takes the number of words per
        // chunk, shift size (if 0, will be set to param #1), and 
        // last proportion ( 0.01 <= last <= 1.00 )
        this.cutterPanel.threeParamSpacer( 
                new Number( v.size ), 0, new Number( v.last ) );

        // default the chunkset name to #size#_words_#last#
        var csname = Ext.getCmp( 'chunkset-name' );
        csname.setValue( v.size + "_words_" + v.last );
    }
});

// this defines the accoridion in the lower right
// call to new AutoCutter({...}) must contain cutterPanel config option
// which get propagated to child CutterTypes
AutoCutter = Ext.extend( Ext.Container, {
    border: false,  // no border
    height: 400,    // default the height
   
    // accordion layout does the + button on options and opens/closes
    // child panel types
    layout: 'accordion',
    layoutConfig: {
        animate: true   // force animation of panel open/close
    },

    initComponent: function() {
        // get the cutter panel that the auto cutters operate on
        var cp = this.cutterPanel;

        // add items and defaults dynamically
        Ext.apply( this, {
            defaults: {
                padding:5   // default so auto cutters have a little room
            },

            // items are Ext.Panel by default
            // accordion layout provides nifty open/close layout 
            items: [
                { 
                    // Advanced Cutter
                    // ditto
                    title: 'Cut by Chunk Size',
                    border: false,
                    items: [ new AdvancedCutter({
                        cutterPanel: cp
                    }),{
                        contentEl: 'help-advanced',
                        border: false
                    }]
                },{   // Simple CUtter
                    title: 'Cut by # of Chunks',
                    border: false,
                    
                    // items are Ext.Container by default
                    items: [ new SimpleCutter({ // add a simple cutter 
                                                // first
                        cutterPanel: cp         // required to propegate
                    }),{
                        // second thing is Container for Simple help text
                        // that is baked into index.php
                        contentEl: 'help-simple',   // DIV in index.php
                                                    // with id 
                                                    // 'help-simple'
                        border: false
                    }]
                },{
                    // last panel holds help text for Visual Cutter 
                    title: 'Visual Cutter - Info',
                    border: false,
                    contentEl: 'help-visual'
                }
            ]
        });
        AutoCutter.superclass.initComponent.apply( this, arguments );
    }
});

// == TextViewer ======================
// viewer and uploader for texts

// textviewer is an extended tree panel type that displays a list of 
// uploaded texts and related chunksets,
// contains methods for selecting and deleting texts
// root node is predefined and says "Uploaded Library"
//   subnodes are the user's texts with their name text id
//     nodes off of texts represent chunksets, these nodes have cs name,
//     cs id, and the array of spaces that define the chunkset
// json data returned from server builds the tree each time a change
// occurs (when _reload()) is called
TextViewer = Ext.extend( Ext.tree.TreePanel, {
    border: false,
   
    // root node, everything in the tree hangs off of this
    root: {
        id: 'texts',
        text: 'Uploaded Library',
        icon: 'icons/package.png',
        expanded: true
    },

    // use this url to get data
    dataUrl: 'modules/texts/gettexts.php',

    // function that will be called when a text or chunkset is selected
    selectText: function( id, spaces ) {
        // id: the id of the text selected
        // spaces: array of spaces that define a chunkset, optional, used
        //   when selecting a chunkset
        
        // wait box
        var waiter = Ext.Msg.wait( "Please wait while building text.", 
            "Loading Text" );

        // get the cutterpanel based on the Ext id, hacky
        var cp = Ext.getCmp( 'cp' );
        var ftb = cp.getFooterToolbar();    // and the footer

        // if the footer was retreived, disable it while loading a new
        // text
        if ( ftb )
            ftb.disable();

        // get the cutter panel wrapper panel that will display the
        // name of the text being viewed
        var cpp = Ext.getCmp( 'cutter-panel-panel' );

        // if the id of the text in the cutter panel is the same as the
        // selected text, there is no need to get the text from the
        // server
        if ( cp.tid == id )
        {
            // however, if a chunkset is selected, load the space array
            if ( spaces )
            {
                // set spaces, and update the chunk viewer
                cp.setSpaces( spaces );
                cp.updateTable();
            }

            // enable the footer and hide the message box
            ftb.enable();
            waiter.hide();
            return;
        }

        // if here, we need to get the text from the server, make Ajax 
        // request 
        Ext.Ajax.request({
            url: 'modules/texts/gettext.php',
            method: 'POST',

            // one param needed is the id of the text
            params: {
                textid: id
            },

            // fired on successful return from the server
            success: function( r, o ) {
                // r: the request response object
                // o: the request object

                // decode the response text into json
                var response = Ext.decode( r.responseText );
                var text = response.text;       // get text data
                var textname = response.name;   // get text name
                var size = response.size;       // get # of bytes of text
                var errors = response.errors;   // get any errors
               
                // if text exists, it ought to, set the new text
                if ( text )
                {
                    // use newText of CP to put the words in the string 
                    // 'text' into the CP and give it the textid
                    cp.newText( text, id );

                    // set the title of the wrapper panel to reflect
                    // the change in text
                    cpp.setTitle( "Visual Cutter -- " + textname );

                    // if chunkset selected, used the passed spaces to 
                    // cut the text
                    if ( spaces )
                    {
                        cp.setSpaces( spaces );
                        cp.updateTable();
                    }

                    // this should not run
                    if ( !cp.canFit() )
                    {
                        waiter.hide();

                        if ( ftb )
                            ftb.enable();
                        Ext.Msg.alert( "Text Too Big",
                            "Text is too big to fit in Visual" +
                            " Cutter Panel, but you can use the Auto" +
                            " Cutters in the lower right." );
                        cpp.setTitle( "Visual Cutter -- No Text -- " + 
                            textname );
                    }
                }
                else
                    // if text was null, report any server errors
                    report_errors( errors, "Text Retrival Error" );

                // hide the wait message
                waiter.hide();

                // enable the footer
                if ( ftb )
                    ftb.enable();
            },
            failure: function( r, o ) {
                // same args as success
                var response = Ext.decode( r.responseText );
                var errors = response.errors;

                // use report_errors to display the server's list
                // of error messages
                report_errors( errors, "Text Retrival Error" );

                // enable the footer since nothing happened
                if ( ftb )
                    ftb.enable();
            }
        });
    },

    // deletes a text based on the id
    deleteText: function( id ) {
        // id: id of text

        // send request to server to delete the text
        Ext.Ajax.request({
            url: 'modules/file/removetext.php',
            method: 'POST',
            params: {
                textid: id  // send the id of the text to delete
            },
            success: function( r, o ) {
                // same as ever
                var response = Ext.decode( r.responseText );

                // get the text viewer tree
                var tv = Ext.getCmp( 'text-viewer' );

                // and the cutter panel
                var cp = Ext.getCmp( 'cp' );

                // if the id deleted is the same as the currently viewed
                // text, remove from cutter
                if ( id == cp.tid )
                    cp.newText( '', null );

                // reload the tree and expand the root nod to show texts
                tv._reload();
                tv.getRootNode().expand();
            },
            failure: function( r, o ) {
                // report errors in message box
                var response = Ext.decode( r.responseText );
                var errors = response.errors;

                report_errors( errors, "Text Removal Error" );
            }
        });

    },

    // deletes a chunkset based on the ids of the chunkset and text
    deleteChunkset: function( tid, csid ) {
        Ext.Ajax.request({
            url: 'modules/texts/unchunk.php',
            method: 'POST',
            params: {
                textid: tid,    // id of text
                csid: csid      // id of chunkset to delete
            },
            success: function( r, o ) {
                // reload the tree
                var response = Ext.decode( r.responseText );
                var tv = Ext.getCmp( 'text-viewer' );
                var cp = Ext.getCmp( 'cp' );
                tv._reload();
                tv.getRootNode().expand();
            },
            failure: function( r, o ) {
                // report errors
                var response = Ext.decode( r.responseText );
                var errors = response.errors;

                report_errors( errors, "Chunkset Removal Error" );
            }
        });

    },

    // simple function call to refresh the tree
    _reload: function() {
        this.getLoader().load( this.root );
    },

    initComponent: function() {
        Ext.apply( this, {

            defaults: {
                expanded: true,
                border: false
             }

        });
        TextViewer.superclass.initComponent.apply( this, arguments );
    },

    // listeners, functions are fired on action
    listeners: {
        // on click of a node, select the text/chunkset
        click: function( n, e ) {
            // n: node on tree
            //    n.attributes has all the server defined info about
            //    the node
            // e: mouse click event

            // if the node is not the root node, and the node is a 'text'
            // node, select the text with the node's tid attribute
            if ( n != this.getRootNode() && n.attributes.type == 'text' )
                this.selectText( n.attributes.tid );

            // if it is a chunkset, get the text id from the parent node
            // and spaces attribute of the clicked node
            else if ( n.attributes.type == 'chunkset' )
                this.selectText( n.parentNode.attributes.tid, 
                    n.attributes.spaces );
        },

        // on right-click of a node, show menu for text/chunkset
        contextmenu: function( n, e ) {
            // n: node on tree
            //    n.attributes has all the server defined info about
            //    the node
            // e: mouse click event
            
            // this doesn't quite work, and I'm sick of trying.
            // it works once, and then subsequent right-clicks cause
            // buttons to merge, odd, and a PITA
            var c, t = n.getOwnerTree();    // get the tree, t
            var type = n.attributes.type;   // get chunkset or text

            // set the menu, c, to the correct type
            // functions return a new Ext.menu.Menu object
            if ( type == 'chunkset' )
                c = t.contextChunksetMenu();
            else if ( type == 'text' )
                c = t.contextTextMenu();
            else
                return;

            // set the context of the menu
            c.contextNode = n;

            // show the menu at the location of the right-click
            c.showAt( e.getXY() );
        }
    },

    // function that returns the context menu when right-click a text
    contextTextMenu: function() {
        // new Menu
        return new Ext.menu.Menu({
            // items are Ext.menu.Item
            items:[{
                id: 'delete',       // id of button, to determine action
                text: 'Remove Text' // text shown on button
            },{
                id: 'show',
                text: 'Show Text'
            }],

            listeners: {
                // fires on click of menu item
                itemclick: function( item ) {
                    // item: the menu option

                    // get the affected node
                    var n = item.parentMenu.contextNode;

                    // get the tree of the node
                    var t = n.getOwnerTree();

                    // choose the right action, based on the item clicked
                    switch ( item.id )
                    {
                        case 'delete' : 
                            t.deleteText( n.attributes.tid );
                            break;

                        case 'show' :
                            t.selectText( n.attributes.tid );
                            break;
                    }
                }
            }
        });
    },

    // ditto of contextTextMenu above
    contextChunksetMenu: function() {
        return new Ext.menu.Menu({
            items:[{
                id: 'show',
                text: 'Show Chunkset'
            },{
                id: 'delete',
                text: 'Remove Chunkset'
            }],

            listeners: {
                itemclick: function( item ) {
                    var n = item.parentMenu.contextNode;
                    var t = n.getOwnerTree();
                    switch ( item.id )
                    {
                        case 'delete' : 
                            t.deleteChunkset( n.parentNode.attributes.tid, 
                                n.attributes.tid );
                            break;

                        case 'show' :
                            t.selectText( n.parentNode.attributes.tid, 
                                n.attributes.spaces );
                            break;
                    }
                }
            }
        });
    }
});

// == Uploader ========================
// upload a new text

// function that acts as handler to Upload Text button
// opens a new window with upload form
Uploader = function() {
    // create new window and show it
    var win = new UploaderWindow({id:'uploader-window'});
    win.show();
}

// extension of a Ext.Window type that has a form 
UploaderWindow = Ext.extend( Ext.Window, {
    title: "Text Uploader",
    modal: true,    // window has sole focus, nothing can be done in bg
    width: 500,    
    initComponent: function() {
        // add the form to the window
        Ext.apply( this, {
            items: [ new UploaderForm({}) ]
        });
        UploaderWindow.superclass.initComponent.apply( this, arguments );
    }
});

// upload text specific form with fields for file and name of text
UploaderForm = Ext.extend( Ext.form.FormPanel, {
    border: false,
    
    padding: 5,
    layout: 'form', // enable field labels and anchor 

    fileUpload: true,   // form uploads a file

    // function that fires when an EnterSubmit field type has the ENTER 
    // key pressed when in focus
    enterSubmit: function() {
        // call upload
        this.upload();
    },

    // upload the text in the form
    upload: function() {
        // if the form is valid, text name has been entered and file
        // selected
        if ( this.getForm().isValid() )
        {
            // get the Ext.form.BasicForm with getForm ad submit it
            this.getForm().submit({
                // upload text url
                url: 'modules/file/uploadtext.php',
                method: 'POST', // via POST

                // wait message box
                waitMsg: 'Uploading text... May take a while.',

                // fired on success returned from server
                success: function(f,a) {
                    // f: form
                    // a: action
                    // neither needed
                    
                    var tv = Ext.getCmp( 'text-viewer' );
                   
                    // reload the tree and expand the text list
                    tv._reload();
                    tv.getRootNode().expand( true );

                    // close the uploader window
                    Ext.getCmp( 'uploader-window' ).close();
                },
                failure: function(f,a) {
                    // report errors if failed
                    report_errors( a.result.errors, "Upload Error" );
                }
            });
        }
    },

    // build the form dynamically
    initComponent: function() {
        // get the form
        var fp = this;

        // create a text field for the text name
        // will call enterSubmit in form when ENTER is pressed
        var namefield = new EnterField({
            fieldLabel: 'Text Name',
            name: 'name',
            allowBlank: false   // do not allow empty
        });

        // field for file
        var filefield = {
            xtype: 'fileuploadfield',   // tell it the type of field,
                                        // FileUploadField.js
            fieldLabel: 'File',
            name: 'file',
            listeners: {
                // fires when Open is pressed from file selector
                fileselected: function(f,file) {
                    // f: form
                    // file: filename + path

                    // match the base name of the file
                    var name = file.match( /[^\\\/]+\..{3}/ );

                    // if name is matched, set the text name to the 
                    // filename
                    if ( name )
                        namefield.setValue( name );
                    else 
                        namefield.setValue( file );
                }
            }
        };

        Ext.apply( this, {
            defaults: {
                anchor: '100%',     // use 100% of width
                xtype: 'textfield'  // default fields to accept text
            },

            // Ext.form.Field or coerced to
            items: [ filefield, namefield ],

            // add a button to the form to submit the form
            buttons: [{
                text: "Upload",
                scope: fp,
                handler: fp.upload
            }]
        });
        UploaderForm.superclass.initComponent.apply( this, arguments );
    }
});

// == Merger ==========================
// merge window, very similar to upload window

// handler for Merge Chunksets button
Merger = function() {
    // create and show merge window
    var win = new MergerWindow({id:'merger-window'});
    win.show();
}

// merge window with merge form
MergerWindow = Ext.extend( Ext.Window, {
    title: "Merge Chunksets",
    width: 700,    
    height: 400,
    layout: 'fit',
    modal: true,    // don't allow background action
    initComponent: function() {
        Ext.apply( this, {
            items: [ new MergerForm({}) ]
        });
        MergerWindow.superclass.initComponent.apply( this, arguments );
    }
});

// extended form with drag/drop feature
MergerForm = Ext.extend( Ext.form.FormPanel, {
    border: false,
    
    padding: 5,
    layout: 'border',   // allow for resizable panels
    
    // daynamically create the form
    initComponent: function() {
        var fp = this;

        // create the left grid as the "From" grid
        var fromGrid = new MergerGrid({
            // store contains the chunkset name and associated text name,
            // the ids of both, and the number of chunks in the chunkset
            // and populate the grid with all the chunksets
            store: new Ext.data.JsonStore({
			    fields: [ 
				    'cs', 'csid', 'text', 'textid', 
					{name:'size',type:'int'} 
			    ],
				url: 'modules/texts/getchunksets.php',
				root: '',
				autoLoad: true  // populate the grid on load
            })
        });

        // ditto, minus the pre-population of grid, this one starts empty
        var toGrid = new MergerGrid({
            store: new Ext.data.JsonStore({
			    fields: [ 
				    'cs', 'csid', 'text', 'textid', 
					{name:'size',type:'int'} 
			    ]
            })
        });

        // fbar fields
        // name of merge
        var mergenamefield = new EnterField({
			id: 'mergenamefield',
            fieldLabel: "NOPE THIS DOES NOT WORK, SEE fbar",
			xtype: 'textfield',
            // this doesn't work
			enterSubmit: function() {
				merge();
			}
        });

        // check to transpose or not
        // defaults to checked
        var transposefield = new Ext.form.Checkbox({
            id: 'transposefield',
            fieldLabel: "NOPE, STILL DON'T WORK",
            checked: true,
            selected: true
        });

		// handler for submit button
		var merge = function() {
            // do nothing if merge name is empty
			if ( !mergenamefield.isValid() )
				return;

            // get the count of CS to merge, if no CS in right grid, 
            // do nothing
			var store = toGrid.getStore();
			if ( store.getCount() < 1 )
				return;
		
            // create empty array for data
			data = [];

            // loop through list of chunksets in right list
			var i = 0;
			for ( ; i < store.getCount(); i++ )
			{
                // get the chunkset
				var cs = store.getAt(i);

                // create an object with the chunkset and text IDs
				var ids = {
					csid: cs.get( 'csid' ),
					textid: cs.get( 'textid' )
				};

                // push it into an array
				data.push( ids );
			}

            // encode the array of objects as json string
			var datastr = Ext.encode( data );

            // replace all double-quotes with single-quotes
            // because double quotes reek havok with the way data is
            // submitted
            // should be safe since all punct is stripped from IDs
			datastr = datastr.replace( /"/g, "'" );

            // create a frame for submitting the form
			var body = Ext.getBody();
			var frame = body.createChild({
				tag:'iframe',
				cls:'x-hidden',
				id:'iframe',
				name:'iframe'
			});
            
            // create HTML elements in a form to submit the data
            // the request is not AJAX, and we want to download the page
            // !WRONG!, could have just as easily created AJAX request
			var form = body.createChild({
				tag:'form',
				cls:'x-hidden',
				id:'form',
				action:'modules/texts/merge.php',
				target:'iframe',
				method: 'POST'
			});

            // <input class="x-hidden" name="name" type="hidden" 
            //    value="..merge name.."></input>
			form.createChild({
				tag: 'input',       // form input field
				cls: 'x-hidden',    // hide
				name: 'name',       // access id for component in POST var
				type: 'hidden',     // like password field, oops
				value: mergenamefield.getValue()
			});

            form.createChild({
                tag: 'input',
                cls: 'x-hidden',
                name: 'transpose',
                type: 'checkbox',
                value: transposefield.getValue(),
                checked: transposefield.getValue()
            });

			var csfield = form.createChild({
				tag: 'input',
				cls: 'x-hidden',
				name: 'chunksets',
				id: 'csfield',
				type: 'hidden',
				value: datastr  
			});

            // submit the form
			form.dom.submit(); 

		};

        // submit button
        var submitbutton = new Ext.Button({
            text: "Download Merge",
			handler: merge
        });

        Ext.apply( this, {
            // items are panels
            items: [ 
            {
                region: 'center',   // center region
                width: 350,
                title: "Available Chunksets (Drag onto Merged Chunksets)",
                layout: 'fit',
                split: true,        // split for resizability
                items:[fromGrid]    // pre-populated grid of chunksets
            },{
                region: 'east',
                width: 350,
                title: "Merged Chunksets",
                layout: 'fit',
                split: true,
                items: [toGrid] // grid that will be submitted
            }],
            // add footer
            fbar: [ "Transpose:", transposefield, 
                    "Merge Name:", mergenamefield, submitbutton ]
        });
        MergerForm.superclass.initComponent.apply( this, arguments );

    }
});

// don't know how this works exactly, until ***
// from extjs.eu
GridDropper = function( grid, config ) {
    this.grid = grid;
    GridDropper.superclass.constructor.call( this, grid.view.scroller.dom, 
                                             config );
}

Ext.extend( GridDropper, Ext.dd.DropZone, {
    containerScroll: true,
    onContainerOver: function( dd, e, data ) {
        return dd.grid !== this.grid ? this.dropAllowed : this.dropNotAllowed;
    },
    onContainerDrop: function( dd, e, data ) {
        if ( dd.grid !== this.grid )
        {
            this.grid.store.add( data.selections );
            Ext.each( data.selections, function( r ) {
                dd.grid.store.remove(r);
            });
            this.grid.onRecordDrop( dd.grid, data.selections );
            return true;
        }
        else
        {
            return false;
        }
    }
});
// ***

// grid that defaults to part of a draggable zone for merge window
MergerGrid = Ext.extend( Ext.grid.GridPanel, {
    
    border: false,
    autoScroll: true,
    layout: 'form',
    enableDragDrop: true,

    ddGroup: 'ddmergergrid',    // id of dragdrop zone, all ddGroup with
                                // same id string will be valid drag/drop 
                                // target locations

    // part of above unknown code
    onRender: function() {
        MergerGrid.superclass.onRender.apply( this, arguments );
        this.dz = new GridDropper( this, {ddGroup: this.ddGroup || 'ddgrid'} );
    },

    // do nothing on dropping a node into a list
    onRecordDrop: Ext.emptyFn,
   
    // dynamically generate columns for grid
    initComponent: function() {
        var config = {
            // define columns
            colModel: new Ext.grid.ColumnModel({
                columns: [{
                    header: "Chunkset", // header label
                    dataIndex: 'cs'     // index into store
                },{
                    header: "Text",
                    dataIndex: 'text'
                },{
					header: "Chunks",
					dataIndex: 'size'
				}],

                defaults: {
                    sortable: true,
                    menuDisabled: true
                }
            })
        };

        // wierd way of applying locally created config items, 
        // don't know how/why this works
        Ext.apply( this, Ext.apply( this.initialConfig, config ) );
        MergerGrid.superclass.initComponent( this, arguments );
    }
});

// == TextManToolbar ==================
// toolbar with buttons for upload/download and merge
TextManToolbar = Ext.extend( Ext.Toolbar, {

    // dynamically generate the buttons
    initComponent: function() {
        // download button, item in menu
        var downloadButton = new Ext.menu.Item({
            text: 'Download Texts',
            icon: 'icons/disk.png',
            handler: function() {
                // create a hidden frame
                var body = Ext.getBody();
                var frame = body.createChild({
                    tag:'iframe'
                    ,cls:'x-hidden'
                    ,id:'iframe'
                    ,name:'iframe'
                });
                     
                // create a hidden form to submit the request to download
                // the texts
                var form = body.createChild({
                    tag:'form'
                    ,cls:'x-hidden'
                    ,id:'form'
                    ,action:'download.php'  // url to download texts
                    ,target:'iframe'
                });

                form.dom.submit();
            }
        });

        // upload button, menu item
        var uploadButton = new Ext.menu.Item({
            text: "Upload New Text",
            handler: Uploader,          // handler created uploader window
            icon: 'icons/book_add.png'
        });

        // create button with menu
        var udmenu = new Ext.Button({
            text: "Upload/Download",
            menu: {
                items:[ uploadButton, downloadButton ]
            }

        });

        // simple button that creates a merge window
        var mergeButton = new Ext.Button({
            text: "Merge Chunksets",
            handler: Merger,
            icon: 'icons/merge.png'
        });
        Ext.apply( this, {
            items: [udmenu,mergeButton]
        });
        TextManToolbar.superclass.initComponent.apply( this, arguments );
    }
});



// == General Handling Functions ====================================

// report_errors
function report_errors( errors, title ) {
    // errors: array of error strings
    // title: title of window with error messages

    var out = "";

    // create string of all errors contained in array
    for ( var i = 0; i < errors.length; i++ ) 
    {
        out += "\n" + (i+1) + ". " + errors[i];
    }

    // create Ext alert dialog box
    Ext.Msg.alert( title, out );

}
