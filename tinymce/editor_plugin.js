/**
 * $Id: editor_plugin.js
 *
 * @author Brenda Egeland
 */

(function() {
	tinymce.create('tinymce.plugins.minAgNews', {
		init : function(ed, url) {
			this.editor = ed;
			
			// Register commands
			ed.addCommand('mceAddDocLink', function() {
				var se = ed.selection;
				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A')) {
				    content = '';
				} else {
				    var content = ed.selection.getContent();
				}
				ed.windowManager.open({
					file : url + '/minagnews-list.php',
					width : 700,
					height : 500,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('minAgNews', {
				title : 'Link to Minutes, Agendas, Newsletters',
				image : url + '/../images/minagnews20.png',
				cmd : 'mceAddDocLink'
			});
      // ed.onNodeChange.add(function(ed, cm, n, co) {
      //  cm.setActive('minAgNews', true);
      // });
		},

		getInfo : function() {
			return {
				longname : 'minAgNews',
				author : 'Brenda Egeland',
				authorurl : 'http://www.redletterdesign.net',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('minAgNews', tinymce.plugins.minAgNews);
})();