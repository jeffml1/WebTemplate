/**
 * $Id: editor_plugin_src.js 677 2008-03-07 13:52:41Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.ProLinkPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceProLink', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/index.php',
					width : 800,
					height : 600,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('prolink', {
				title : 'Insert an Internal Link',
				cmd : 'mceProLink',
				image: 	url + '/interface/images/proimage.gif'
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.get( 'prolink' ).setDisabled( ed.selection.isCollapsed() );
            });
		},
		getInfo : function() {
			return {
				longname : 'ProImage',
				author : 'Paige Saunders, ProSouth Technology Solutions',
				authorurl : 'http://prosouth.co.nz',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('prolink', tinymce.plugins.ProLinkPlugin);
})();