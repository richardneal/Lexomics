Ext.onReady(function(){
  
  Ext.ns( 'Scrubber' );

 Scrubber.TextArea = Ext.extend( Ext.form.TextArea, {

    initComponent: function() {},

    listeners: {
        specialkey: function(t, e) {
            if ( e.getKey == e.TAB )
            {

            }
        }
    },
    toLower: function() {
        this.setValue(this.getValue().toLocaleLowerCase());
    },
    toUpper: function() {
        this.setValue(this.getValue().toLocaleUpperCase());
    },
    trim: function() {
        this.ltrim();
        this.rtrim();
    },
    ltrim: function() {
        this.replace( /^\s+/ );
    },
    rtrim: function() {
        this.replace( /\s+$/ );
    },
    replace: function(reg, rep) {
        rep = rep || rep === 0 ? rep : "";
        this.setValue( this.getValue().replace( reg, rep ) );
    },
    applyStopwords: function( list ) {
        var w = list.getStopwordList(),
            ic = list.getIgnoreCase(),
            punct = list.getPunct(),
            f = ic ? "ig" : "g";
        for ( var i = 0; i < w.length; i++ ) {
            var ww = w[i];
            var p = '(\\s+|^|' + punct + ')' + ww + '(\\s+|$|' + punct + ')';
            var re = new RegExp( p, f );
            this.replace( re, "$1$2" );
        }
    }
});

Scrubber.Panel = Ext.extend(Ext.Panel, {
    padding: 5,
    layout: 'form',
    defaults: {
        anchor: '100%',
        border: false,
        hideLabel: true,
        height: '100%'
    },

    initComponent: function() {
        this.textarea = new Scrubber.TextArea({});
        var sa = this.textarea; // local variable to aid with less typing
        this.setValue = function( val ) {
            sa.setValue( val );
        };
        Ext.apply(this, {
            items: [sa]  
        });
        Scrubber.Panel.superclass.initComponent.apply(this, arguments);
    }

});
      var sp = new Scrubber.Panel({
        id: 'sp',
        title: "ScrubberPanel",
        autoWidth: true,
        border: true
    });

    Ext.ns("Uploader");

Scrubber.TextUpload = function() {
    uploadPanel =  {
      fileUpload: true,
      xtype       : 'form',
      autoScroll  : true,
      id          : 'formpanel',
      defaultType : 'field',
      frame       : true,
      title       : 'Upload New Text',
      labelWidth: 50,
      defaults: {
        anchor: '95%',
        allowBlank: false,
        msgTarget: 'side'
      },
      items       : [{
       xtype: 'fileuploadfield',
       fieldLabel: 'Text',
       name: 'text-path'
      },{
      xtype: 'checkboxgroup',
            title: 'Upload Type',
            autoHeight: true,
            defaultType: 'checkbox', 
            items: [{
                fieldLabel: '',
                boxLabel: 'XML',
                name: 'text-type-xml'
            }, {
                fieldLabel: '',
                labelSeparator: '',
                boxLabel: 'SGML',
                name: 'text-type-sgml'
            }, {
                fieldLabel: '',
                labelSeparator: '',
                boxLabel: 'HTML',
                name: 'text-type-html'
            }]
        }],

      buttons: [{
        text: 'Upload',
        handler: function(){
          var form = this.up('form').getForm();
            if(form.isValid()){
              form.submit({
                url: 'callbacks/scrub.php',
                waitMsg: 'Uploading your text...',
                success: function(fp, o) {
                  msg('Success', 'Successfully scrubbed "' + o.result.file);
                }
              });
            }
        }
    },{
      text: 'Reset',
      handler: function() {
        this.up('form').getForm().reset();
      }
    }]
  };

    var uploader = new Ext.Window({
        id     : 'uploadwindow',
        width  : 400,
        items  : [uploadPanel]
    });
    uploader.show();
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
            id: 'west-panel',
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
                handler: Scrubber.TextArea,
                title: 'Scrubbed Text',
                closable: false,
                autoScroll: true
            }],
            fbar: new ScrubberManToolbar({})
        })]
    });
});
