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

// memory skimping time
// sacrifice a little bit of speed for mucho space saving
//

// um, a 'const'
var MAX_WORDS = 75000;

ChunkViewer = Ext.extend( Ext.list.ListView, {

    border: false,
    
	// 
    emptyText: 'No Chunks???',
    root: 'chunks',
    cpid: 'cp',

	columnSort: false,

    columns: [{
        header: 'Chunk',
        dataIndex: 'chunk',
        align: 'right'
    },{
        header: 'Start',
        dataIndex: 'start',
        align: 'right'
    },{
        header: 'End',
        dataIndex: 'end',
        align: 'right'
    },{
        header: 'Length',
        tpl: '{[values.end-values.start+1]}', 
        align: 'right'
    }],

    store: new Ext.data.JsonStore({
        root: 'chunks',
        fields: [
            {
                name: 'chunk',
                type: 'int'
            },
            {
                name: 'start',
                type: 'int'
            },
            {
                name: 'end',
                type: 'int'
            }
        ]
    }),

    listeners: {
        click: function( t, i, n, e ) {
            var ta = Ext.get( "cptextarea" );
            var c = ta.dom.getElementsByClassName( "cpchunk" ).item( i );
            if ( !c )
                return;
            var fly = Ext.fly( c );
            fly.dom.scrollIntoView();
            fly.highlight('ff0000', {
                easing: 'easeOut',
                duration: 2
            });
        }
    }

});

CutterPanel = Ext.extend( Ext.Panel, {

	// config vars
    
    id: 'cp',
    tid: null,

	cls: 'cp-panel-cursor cp-panel-align cp-panel-overflow',

	autoScroll: true,

	padding: 5,

	colors: [ '#ffdead', '#fffacd', '#e0eee0', '#eed5b7' ],

	updater: new ChunkViewer({
		id: 'cp-cv'
	}),
	
	// data

	textData: '',

	// public functions
    
    reset: function() {
        this.emptySpaces();
        this.updateTable();
    },

	threeParamSpacer: function( size, shift, last ) {
        var spaces = [];
		shift = size;
        var t;
		var curr = size - 1;
		for ( ; curr < this.data.tN; curr = curr + shift )
            spaces.push( curr );

		var cut = curr - shift;
		var lastlen = this.data.tN - cut;
		if ( ( lastlen / size ) > last && lastlen != 1)
            ;
		else
            spaces.pop();

        this.setSpaces( spaces );
        this.updateTable();
	},

	oneParamSpacer: function( chunks ) {
		var size = this.data.textArray.length / chunks;
		size = Math.round( size );
		this.threeParamSpacer( size, size, .5 );
	},

    updateTable: function( e, t, o ) {
        // if a click event
        if ( e )
            r = Ext.getCmp( this.id );
        else
            r = this;

        // if it is a click, get the id of the target and make
        // sure it is a word
        if ( e )
        {
            if ( !e.target.id.match( r.data.cmp ) )
                return;
        }

        var list = r.updater;
	
		var rows = [];
		var spaces = r.getSpaces();
		var end, start = 1;
		var i = 0;
		for ( i = 0; i < spaces.length; i++ ) 
		{
			rows.push({
				chunk: i + 1,
				start: start,
				end: spaces[i] + 1
			});
			start = spaces[i] + 2;
		}

		// add the last row
		rows.push({
			chunk: i + 1,
			start: start,
			end: r.data.tN
		});

		// get the store and update it with the data
        // this is very slow, nearly doubles the time to click a word
		list.getStore().loadData( { chunks: rows } );

		// set the colors in the table 
		var nodes = list.getNodes();
		
		for ( i = 0; i < nodes.length; i++ )
		{
			var node = Ext.get( nodes[i] );
			node.setStyle( 'background-color', 
					r.colors[ i % r.colors.length ] );
		}
    },

    getCmpId: function( id ) {
        var match = id.match( /\d+/ );
        if ( match )
            return Number( match[0] );   // id # of space
        else
            return;
    },

    newText: function( text, tid ) {
        var spaces = this.getSpaces();
		this.killPanel();
        
        this.tid = tid;
		this.textData = text;
		
        this.makePanel();
		this.paint();
		this.doLayout();
		this.paint2();

        var w = Ext.get( this.data.cmp + 0 );
        if ( w && w.dom )
            w.dom.scrollIntoView();

        return spaces;
    },

	recolor: Ext.emptyFn,

	emptySpaces: function() {
        // empty the array of selected spaces, collapses all cpchunk into one
        // single cpchunk as a child of cptextarea
        this.data.selectedSpaces = [];

        var ta = Ext.get( "cptextarea" );
        var cpchunks = ta.dom.getElementsByClassName( "cpchunk" );

        // one new cpchunk and HTMLElement
        var elt = {
            tag: 'span',
            cls: 'cpchunk'
        };
        var elto = Ext.DomHelper.createDom( elt );

        // get a fly so we can use appendChild on cpchunk
        var fly = Ext.fly( elto );

        var len = cpchunks.length;

        for ( var i = 0; i < len; i++ )
        {
            var child = ta.dom.removeChild( cpchunks.item(0) );
            var words = child.getElementsByTagName( 'span' );
            
            // coerce the words NodeList into an Array so appendChild works
            var wordsArray = Array.prototype.slice.call( words, 0, words.length );
            fly.appendChild( wordsArray );
        }

        ta.appendChild( elto );
	},

    addSpaces: function( arr ) {
        // splits cptextarea's single child ("cpchunk") into multiple "cpchunk"
        // children
        // PRE: 
        //   - assumes cptextarea has one child which contains all words
        //arr.push( this.data.tN );
        arr.sort( function(a,b){ return a-b; } );
        var start = 0, end;
        var ta = Ext.get( "cptextarea" );
        var cpchunk = ta.child( 'span', true );
        var words = cpchunk.getElementsByTagName( "span", true );
        ta.dom.removeChild( cpchunk );
        for ( var i = 0; i <= arr.length; i++ )
        {
            // if arr[i] contains zero, will evaulate to false
            if ( arr[i] === 0 || arr[i] )
            {
                end = arr[i];
                // add the end to the array
                this.data.selectedSpaces = 
                    this.data.selectedSpaces.concat( arr[i] );
            }
            else
                end = this.data.tN;

            var elt = {
                tag: 'span',
                cls: 'cpchunk'
            };
            var len = end - start + 1;
            var children = Array.prototype.slice.call( words, 0, len );
            var elto = Ext.DomHelper.createDom( elt );
            var poo = 0;
            var fly = Ext.fly( elto );
            fly.appendChild( children );
            ta.dom.appendChild( elto );

            start = end + 1;
            
        }
    },

    removeSpaces: Ext.emptyFn,

    setSpaces: function( arr ) {
        this.emptySpaces();
        this.addSpaces( arr );
    },

    getSpaces: function() {
        this.data.selectedSpaces.sort( function(a,b){ return a-b; } );
        return this.data.selectedSpaces;
    },

	clickSpace: function( e, t, o, tt ) {
        // remove: 
        // one to compensate for user # and one to get appropriate space
        var space = t.getAttribute( "title" ) - 2;
        if ( space < 0 )
            return;
        var cp = Ext.getCmp( 'cp' );

        if ( cp.data.selectedSpaces.indexOf( space ) == -1 )
        {
            cp.data.selectedSpaces.push( space );
        }
        else
        {
            cp.data.selectedSpaces = cp.data.selectedSpaces.remove( space );
        }

        cp.setSpaces( cp.data.selectedSpaces );

        cp.updateTable();
	},

    canFit: function() {
        // it always fits! whoo!
        return true;
    },


	// functions called prior to rendering

	// private

    // stylize the spaces
    stylize: function() {
        var e = Ext.fly( "cptextarea" );
        e.on( 'click', this.clickSpace );
    },

    cleanData: function() {
        this.textData = 
            this.textData.trim()
            .replace( />/g, "&gt;" )
            .replace( /</g, "&lt;" )
            ;
    },

	textArrayify: function() {
		this.data.textArray = this.textData.trim()
            .replace( /[ \t\r\f\v\n]+/gi, " " )
            .split( /[ ]+/gi );
	},

	spaceArrayify: function() {
		this.data.spaceArray = this.data.spaceString.trim()
            .replace( /\n+/gi, "\n" )
            .replace( /[^ \n]/gi, '' ).split( '' );
	},

    spaceStringer: function() {
        this.data.spaceString = this.textData.trim()
            // remove all doubles+ that are not newlines and replace with one
            .replace( /(\t)+/ig, '$1' )
            .replace( /( )+/ig, '$1' )
            .replace( /([\f\v\r])/ig, '' )
            // condense all space tabs to tabs
            .replace( /( \t)|(\t ) /gi, '\t' )
            // make all double+ newlines double newlines
            .replace( /(\n\n)+/ig, '$1' )
            // replaces all non spaces with nothing
            .replace( /\S/gi, '' )
            // replace space type with appropriate modifiers
            .replace( /\t/ig, 't' )
            .replace( / /ig, 's' )
            .replace( /\v/ig, '' )
            .replace( /\n\n/ig, 'd' )
            .replace( /\n/ig, 'n' )
            ;
    },

	// initializers and constructors

	initComponent: function() {
        this.makeStyles();
        this.makePanel();
    	CutterPanel.superclass.initComponent.apply( this, arguments );
	},

	listeners: {
		beforerender: function() {
			this.paint();
		},
		afterrender: function() {
			this.paint2();
			//this.getEl().on( 'click', this.updateTable );
		},
		render: function(c) {
		},
        beforedestroy: function() {
            this.killPanel();
        }
	},

    killPanel: function() {
        this.data.textArray = null;
        this.data.spaceArray = null;
        
        this.data.spaceString = null;

        this.data.cmp = null;

        this.data.tN = null;
        this.data.sN = null;
        this.data.N = null;

        this.data.html = null;

		Ext.destroy( this.get(0) );
    },

    emptyDom: function() {
    },

    makePanel: function() {

        this.data = {
            cmp: this.id + '-cmp-', // beginning of the name of each word/space

            textArray: Array(),     // array of words
            spaceArray: Array(),    // array of spaces

            spaceString: "",

            sN: 0,  // number of spaces
            tN: 0,  // number of words
            N: 0,   // number of spaces + words
            
            selectedSpaces: Array(),    // array of indices to selected spaces
            html: "",
            comp: null
        };

        this.cleanData();

        this.textArrayify();
        this.spaceStringer();

        this.data.sN = this.data.spaceString.length;
        this.data.tN = this.data.textArray.length;
        this.data.N  = this.data.sN + this.data.tN;

        var html = "";
        var cmp = "";
        var space;
        var i = 0;        // index into textArray and spaceArray

        for ( i = 0; i < this.data.tN - 1; i++ )
        {
            html += '<span title="' + (i+1) + '"';

            // don't add hover effect on first word
            if ( i == 0 )
                html += ' class="cp-first-word"';

            html += '>' + this.data.textArray[i];

            space = this.data.spaceString[i];
            if ( space == 's' )
                html += ' ';
            else if ( space == 'n' )
                html += '<br/>';
            else if ( space == 't' )
                html += '&nbsp;&nbsp;&nbsp;&nbsp; ';
            else if ( space == 'd' )
                html += '<br/><br/>';
            else
                html += ' ';
            
            html += '</span>';
        }

        html += '<span title="' + (i+1) + '">' + this.data.textArray[i] + 
            '</span>';
        
        this.data.html = html;

    },

	paint: function() {
        var tmphtml = '<span id="cptextarea" class="cp-text-area">' +
            '<span class="cpchunk">';
        tmphtml += this.data.html;
        tmphtml += '</span></span>';
		var comp = new Ext.Component({html:tmphtml});
		this.add( comp );
		this.data.html = "";
	},

	paint2: function() {
        this.stylize();
		this.updateTable();
	},

    makeStyles: function() {
        // use Ext.util.CSS to create a stylesheet based on the colors array
        // Template:
        // .cpchunk:nth-of-type(4n+1) { background-color: #ffdead; }
        // .cpchunk:nth-of-type(4n+2) { background-color: #eed5b7; }
        // .cpchunk:nth-of-type(4n+3) { background-color: #fffacd; }
        // .cpchunk:nth-of-type(4n+4) { background-color: #e0eee0; }
        var len = this.colors.length;
        var css = "";

        for ( var i = 0; i < len; i++ )
        {
            css += ".cpchunk:nth-of-type(" + len + "n+" + (i+1) + ") ";
            css += "{background-color:" + this.colors[i] + ";}\n";
        }
        Ext.util.CSS.createStyleSheet( css, "cpcolorstyle" );
        
    }

});


Ext.reg( 'cutterpanel', CutterPanel );
