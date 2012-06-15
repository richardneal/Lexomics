Ext.data.JsonP.localization({"title":"Localization","guide":"<h1>Localization in Ext JS</h1>\n<div class='toc'>\n<p><strong>Contents</strong></p>\n<ol>\n<li><a href='#!/guide/localization-section-1'>Ext's Localization Files</a></li>\n<li><a href='#!/guide/localization-section-2'>Utilizing Localization</a></li>\n<li><a href='#!/guide/localization-section-3'>Conclusion</a></li>\n</ol>\n</div>\n\n<hr />\n\n<p>Creating an application that works is one thing; creating an application that works for your users is something very different. Communicating with users in a language that they understand and with conventions that they're used to is vital.</p>\n\n<p>Imagine this scenario, you hand your phone to a friend in good faith but when they return it, everything's in Japanese. Frustrated, you try to remember which combination of buttons leads you to the Settings menu so you can change it back, navigating through, you realize that menus slide in the opposite direction, maybe even the color scheme is different. You start to realize just how important language and cultural conventions are and how disorienting it is when faced with a localization setting that wasn't meant for you. Now imagine your users, wanting to use your Ext JS application but feeling the same confusion and unsure of what's being asked of them.</p>\n\n<p>To fix this, we go through a process known as 'localization' (sometimes called l10n). A large part of localization is translation and, thankfully, Ext JS makes it easy to localize your application.</p>\n\n<h2 id='localization-section-1'>Ext's Localization Files</h2>\n\n<p>In the root directory of your copy of Ext JS there is a folder called <code>locale</code>. This contains common examples (e.g. day names) in 45 languages ranging from Indonesian to Macedonian. You can inspect the contents of each to see exactly what they contain. Here's an excerpt from the Spanish localization file:</p>\n\n<pre><code>if (<a href=\"#!/api/Ext.toolbar.Paging\" rel=\"Ext.toolbar.Paging\" class=\"docClass\">Ext.toolbar.Paging</a>){\n    <a href=\"#!/api/Ext-method-apply\" rel=\"Ext-method-apply\" class=\"docClass\">Ext.apply</a>(Ext.PagingToolbar.prototype, {\n        beforePageText : \"P&amp;#225;gina\",\n        afterPageText  : \"de {0}\",\n        firstText      : \"Primera p&amp;#225;gina\",\n        prevText       : \"P&amp;#225;gina anterior\",\n        nextText       : \"P&amp;#225;gina siguiente\",\n        lastText       : \"Última p&amp;#225;gina\",\n        refreshText    : \"Actualizar\",\n        displayMsg     : \"Mostrando {0} - {1} de {2}\",\n        emptyMsg       : \"Sin datos para mostrar\"\n    });\n}\n</code></pre>\n\n<p>Note: The <code>&amp;#000;</code> are character entity references which render as special characters, e.g. <code>&amp;#225;</code> shows &#225;.</p>\n\n<p>You can see that it checks to see if a <a href=\"#!/api/Ext.view.BoundList-property-pagingToolbar\" rel=\"Ext.view.BoundList-property-pagingToolbar\" class=\"docClass\">Paging toolbar</a> is in use, and if it is, applies the Spanish strings to each area text is shown. If you have custom text areas you, can append them here as well with the appropriate translations. You'll also notice that it is setting these properties to the Paging Toolbar's prototype. The upshot of this is that every new Paging Toolbar that is created will inherit these translated properties.</p>\n\n<h2 id='localization-section-2'>Utilizing Localization</h2>\n\n<p>There are two ways you could implement localization in your application: statically or dynamically. We're going to look at how to do it dynamically so users can choose which language they're most familiar with. First, we're going to create a Combobox where users will select their language and secondly, we'll deduce the language from the URL so if a user visits http://yoursite.com/?lang=es the Spanish version of your Ext application is used.</p>\n\n<p>Set up a basic HTML page with links to Ext JS's necessary parts and our localized application's languages.js and app.js files.</p>\n\n<pre><code>&lt;!DOCTYPE html&gt;\n&lt;html&gt;\n&lt;head&gt;\n    &lt;meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"&gt;\n    &lt;title&gt;Localization example&lt;/title&gt;\n    &lt;!-- Ext Library Files --&gt;\n    &lt;link rel=\"stylesheet\" type=\"text/css\" href=\"ext/resources/css/ext-all.css\"&gt;\n    &lt;script src=\"ext/ext-all-debug.js\"&gt;&lt;/script&gt;\n    &lt;!-- App Scripts --&gt;\n    &lt;script src=\"languages.js\"&gt;&lt;/script&gt;\n    &lt;script src=\"app.js\"&gt;&lt;/script&gt;\n&lt;/head&gt;\n&lt;body&gt;\n    &lt;div id=\"languages\"&gt;&lt;/div&gt;\n    &lt;div id=\"datefield\"&gt;&lt;/div&gt;\n&lt;/body&gt;\n&lt;/html&gt;\n</code></pre>\n\n<p>We have two separate JavaScript files: the first will be a list of all the languages that Ext JS comes with, the second will be the application itself. We've also set up two <code>div</code> tags, the first will contain the combobox for users to select their language and the second, <code>datefield</code>, will have a date picker.</p>\n\n<p>Now create a file called <code>languages.js</code>. In this we'll store the languages in an array with two values, the language code and the name of the language like so:</p>\n\n<pre><code><a href=\"#!/api/Ext-method-namespace\" rel=\"Ext-method-namespace\" class=\"docClass\">Ext.namespace</a>('Ext.local');\n\nExt.local.languages = [\n    ['af', 'Afrikaans'],\n    ['bg', 'Bulgarian'],\n    ['ca', 'Catalonian'],\n    ['cs', 'Czech'],\n    ['da', 'Danish'],\n    ['de', 'German'],\n    ['el_GR', 'Greek'],\n    ['en_GB', 'English (UK)'],\n    ['en', 'English'],\n    ['es', 'Spanish/Latin American'],\n    ['fa', 'Farsi (Persian)'],\n    ['fi', 'Finnish'],\n    ['fr_CA', 'French (Canadian)'],\n    ['fr', 'French (France)'],\n    ['gr', 'Greek (Old Version)'],\n    ['he', 'Hebrew'],\n    ['hr', 'Croatian'],\n    ['hu', 'Hungarian'],\n    ['id', 'Indonesian'],\n    ['it', 'Italian'],\n    ['ja', 'Japanese'],\n    ['ko', 'Korean'],\n    ['lt', 'Lithuanian'],\n    ['lv', 'Latvian'],\n    ['mk', 'Macedonian'],\n    ['nl', 'Dutch'],\n    ['no_NB', 'Norwegian Bokmål'],\n    ['no_NN', 'Norwegian Nynorsk'],\n    ['pl', 'Polish'],\n    ['pt_BR', 'Portuguese/Brazil'],\n    ['pt_PT', 'Portuguese/Portugal'],\n    ['ro', 'Romanian'],\n    ['ru', 'Russian'],\n    ['sk', 'Slovak'],\n    ['sl', 'Slovenian'],\n    ['sr_RS', 'Serbian Cyrillic'],\n    ['sr', 'Serbian Latin'],\n    ['sv_SE', 'Swedish'],\n    ['th', 'Thai'],\n    ['tr', 'Turkish'],\n    ['ukr', 'Ukrainian'],\n    ['vn', 'Vietnamese'],\n    ['zh_CN', 'Simplified Chinese'],\n    ['zh_TW', 'Traditional Chinese']\n];\n</code></pre>\n\n<p>This is all the languages file will consist of but will serve as a useful reference for our Ext JS application.</p>\n\n<p>Next, we'll start building the application itself. Using the module pattern, we will have four methods: <code>init</code>, <code>onSuccess</code>, <code>onFailure</code> and <code>setup</code>.</p>\n\n<pre><code><a href=\"#!/api/Ext.Loader-method-setConfig\" rel=\"Ext.Loader-method-setConfig\" class=\"docClass\">Ext.Loader.setConfig</a>({enabled: true});\n<a href=\"#!/api/Ext.Loader-method-setPath\" rel=\"Ext.Loader-method-setPath\" class=\"docClass\">Ext.Loader.setPath</a>('Ext.ux', 'ext/examples/ux/');\n<a href=\"#!/api/Ext-method-require\" rel=\"Ext-method-require\" class=\"docClass\">Ext.require</a>([\n    'Ext.data.*',\n    '<a href=\"#!/api/Ext.tip.QuickTipManager\" rel=\"Ext.tip.QuickTipManager\" class=\"docClass\">Ext.tip.QuickTipManager</a>',\n    'Ext.form.*',\n    '<a href=\"#!/api/Ext.ux.data.PagingMemoryProxy\" rel=\"Ext.ux.data.PagingMemoryProxy\" class=\"docClass\">Ext.ux.data.PagingMemoryProxy</a>',\n    '<a href=\"#!/api/Ext.grid.Panel\" rel=\"Ext.grid.Panel\" class=\"docClass\">Ext.grid.Panel</a>'\n]);\n\n<a href=\"#!/api/Ext-method-onReady\" rel=\"Ext-method-onReady\" class=\"docClass\">Ext.onReady</a>(function() {\n\n    MultiLangDemo = (function() {\n        return {\n            init: function() {\n\n            },\n            onSuccess: function() {\n\n            },\n            onFailure: function() {\n\n            },\n            setup: function() {\n\n            }\n        };\n    })();\n\n    MultiLangDemo.init();\n});\n</code></pre>\n\n<p>To create the <a href=\"#!/api/Ext.form.field.ComboBox\" rel=\"Ext.form.field.ComboBox\" class=\"docClass\">combobox</a> that will contain all the possible language selections we first need to create an <a href=\"#!/api/Ext.data.ArrayStore\" rel=\"Ext.data.ArrayStore\" class=\"docClass\">array store</a> in the <code>init</code> function like so.</p>\n\n<pre><code>var store = <a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.data.ArrayStore\" rel=\"Ext.data.ArrayStore\" class=\"docClass\">Ext.data.ArrayStore</a>', {\n    fields: ['code', 'language'],\n    data  : Ext.local.languages //from languages.js\n});\n</code></pre>\n\n<p>This is a very simple store that contains the two fields that correspond to the two values for each record in the <code>languages.js</code> file. As we gave it a namespace, we can refer to it as <code>Ext.local.languages</code>. You can type this in your browser's console to see what it consists of.</p>\n\n<p>Now create the combobox itself, again, within the <code>init</code> function:</p>\n\n<pre><code>var combo = <a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.form.field.ComboBox\" rel=\"Ext.form.field.ComboBox\" class=\"docClass\">Ext.form.field.ComboBox</a>', {\n    renderTo: 'languages',\n    store: store,\n    displayField: 'language',\n    queryMode: 'local',\n    emptyText: 'Select a language...',\n    hideLabel: true,\n    listeners: {\n        select: {\n            fn: function(cb, records) {\n                var record = records[0];\n                window.location.search = <a href=\"#!/api/Ext-method-urlEncode\" rel=\"Ext-method-urlEncode\" class=\"docClass\">Ext.urlEncode</a>({\"lang\":record.get(\"code\")});\n            },\n            scope: this\n        }\n    }\n});\n</code></pre>\n\n<p>If you refresh your browser, you should see a combobox that, when clicked, shows a list of languages bundled with Ext JS. When one of these languages is selected, the page refreshes and appends <code>?lang=da</code> (if you chose Danish) to the URL. We'll use this information to display the desired language to the user.</p>\n\n<p><p><img src=\"guides/localization/combobox.png\" alt=\"\"></p></p>\n\n<p>After the creation of the combobox, we're going to check to see if any language has been previously selected and act accordingly by checking the URL with Ext's <a href=\"#!/api/Ext-method-urlDecode\" rel=\"Ext-method-urlDecode\" class=\"docClass\">urlDecode</a> function.</p>\n\n<pre><code>var params = <a href=\"#!/api/Ext-method-urlDecode\" rel=\"Ext-method-urlDecode\" class=\"docClass\">Ext.urlDecode</a>(window.location.search.substring(1));\n\nif (params.lang) {\n    var url = <a href=\"#!/api/Ext.util.Format-method-format\" rel=\"Ext.util.Format-method-format\" class=\"docClass\">Ext.util.Format.format</a>('ext/locale/ext-lang-{0}.js', params.lang);\n\n    <a href=\"#!/api/Ext.Ajax-method-request\" rel=\"Ext.Ajax-method-request\" class=\"docClass\">Ext.Ajax.request</a>({\n        url: url,\n        success: this.onSuccess,\n        failure: this.onFailure,\n        scope: this\n    });\n\n    // check if there's really a language with passed code\n    var record = store.findRecord('code', params.lang, null, null, null, true);\n    // if language was found in store, assign it as current value in combobox\n\n    if (record) {\n        combo.setValue(record.data.language);\n    }\n} else {\n    // no language found, default to english\n    this.setup();\n}\n\n<a href=\"#!/api/Ext.tip.QuickTipManager-method-init\" rel=\"Ext.tip.QuickTipManager-method-init\" class=\"docClass\">Ext.tip.QuickTipManager.init</a>();\n</code></pre>\n\n<p>Note: We're loading the files with an AJAX request, so the files will have to be uploaded to a server otherwise they'll fail to load due to browser security measures.</p>\n\n<p>Here you can see why we have the <code>onSuccess</code> and <code>onFailure</code> methods. If a language file fails to load then the user must be notified instead of failing silently. First, we'll deal with failed files to make it obvious if debugging is needed; the idea is that if a user types in a nonexistent language code, or for some reason the language has been removed, an alert will be displayed so the user won't be surprised that the application is still in English.</p>\n\n<pre><code>onFailure: function() {\n    <a href=\"#!/api/Ext.MessageBox-method-alert\" rel=\"Ext.MessageBox-method-alert\" class=\"docClass\">Ext.Msg.alert</a>('Failure', 'Failed to load locale file.');\n    this.setup();\n},\n</code></pre>\n\n<p><p><img src=\"guides/localization/onfailure.png\" alt=\"\"></p></p>\n\n<p>The <code>onSuccess</code> method is similar. We evaluate the locale file and then setup the demo knowing that the file has been loaded:</p>\n\n<pre><code>onSuccess: function(response) {\n    eval(response.responseText);\n    this.setup();\n},\n</code></pre>\n\n<p>The AJAX call that we made returns a few parameters. We use JavaScript's <code>eval</code> function on <code>responseText</code>. <code>responseText</code> is the entirety of the locale file that we loaded and <code>eval</code> parses all of the JavaScript contained in the string that is <code>responseText</code>, that is, applying all of the translated text and thus localizing the application.</p>\n\n<p>However, there's nothing in <code>setup()</code> to look at yet so we'll move onto this method next. We're going to start by creating a <a href=\"#!/api/Ext.menu.DatePicker\" rel=\"Ext.menu.DatePicker\" class=\"docClass\">date picker</a> that will change based on the chosen language.</p>\n\n<pre><code>setup: function() {\n    <a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.form.Panel\" rel=\"Ext.form.Panel\" class=\"docClass\">Ext.FormPanel</a>', {\n        renderTo: 'datefield',\n        frame: true,\n        title: 'Date picker',\n        width: 380,\n        defaultType: 'datefield',\n        items: [{\n            fieldLabel: 'Date',\n            name: 'date'\n        }]\n    });\n}\n</code></pre>\n\n<p>Now, if you click on the calendar icon you'll see the month in the specified language as well as the first letter of each day.</p>\n\n<p><p><img src=\"guides/localization/datepicker.png\" alt=\"\"></p></p>\n\n<p>To show more of Ext JS's localization features we'll now create an e-mail field and a month browser. Inside the setup method, write the following:</p>\n\n<pre><code><a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.form.Panel\" rel=\"Ext.form.Panel\" class=\"docClass\">Ext.FormPanel</a>', {\n    renderTo: 'emailfield',\n    labelWidth: 100,\n    frame: true,\n    title: 'E-mail Field',\n    width: 380,\n    defaults: {\n        msgTarget: 'side',\n        width: 340\n    },\n    defaultType: 'textfield',\n    items: [{\n        fieldlabel: 'Email',\n        name: 'email',\n        vtype: 'email'\n    }]\n});\n\nvar monthArray = <a href=\"#!/api/Ext.Array-method-map\" rel=\"Ext.Array-method-map\" class=\"docClass\">Ext.Array.map</a>(<a href=\"#!/api/Ext.Date-property-monthNames\" rel=\"Ext.Date-property-monthNames\" class=\"docClass\">Ext.Date.monthNames</a>, function (e) { return [e]; });\nvar ds = <a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.data.Store\" rel=\"Ext.data.Store\" class=\"docClass\">Ext.data.Store</a>', {\n     fields: ['month'],\n     remoteSort: true,\n     pageSize: 6,\n     proxy: {\n         type: 'pagingmemory',\n         data: monthArray,\n         reader: {\n             type: 'array'\n         }\n     }\n });\n\n<a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.grid.Panel\" rel=\"Ext.grid.Panel\" class=\"docClass\">Ext.grid.Panel</a>', {\n    renderTo: 'grid',\n    width: 380,\n    height: 203,\n    title:'Month Browser',\n    columns:[{\n        text: 'Month of the year',\n        dataIndex: 'month',\n        width: 240\n    }],\n    store: ds,\n    bbar: <a href=\"#!/api/Ext-method-create\" rel=\"Ext-method-create\" class=\"docClass\">Ext.create</a>('<a href=\"#!/api/Ext.toolbar.Paging\" rel=\"Ext.toolbar.Paging\" class=\"docClass\">Ext.toolbar.Paging</a>', {\n        pageSize: 6,\n        store: ds,\n        displayInfo: true\n    })\n});\n// trigger the data store load\nds.load();\n</code></pre>\n\n<p>Remember that <code>renderTo</code> corresponds to an <code>id</code> on an HTML tag so add those to our index file, too.</p>\n\n<p>Notice that when typing in fields, a warning icon is displayed that, when hovered over, reveals context-specific information in the native language as a tooltip.</p>\n\n<p><p><img src=\"guides/localization/tooltip.png\" alt=\"\"></p></p>\n\n<p>An excellent example of what localization means beyond translation can be seen by selecting Polish and seeing how the order of the date field changes from DD-MM-YYYY to YYYY-MM-DD. Another is selecting Finnish and seeing how instead of dashes (-), periods (.) are used to separate day from month from year and the months are not capitalized. It's details like this that Ext takes care for you with it's comprehensive locale files.</p>\n\n<h2 id='localization-section-3'>Conclusion</h2>\n\n<p>In this tutorial we have looked at how to load different locale files included with Ext JS by using AJAX requests that reload the application in the desired language along with subtle cultural conventions.</p>\n\n<p>Your users will benefit from a more native experience and appreciate the extra lengths that you've gone to to ensure a better experience.</p>\n"});