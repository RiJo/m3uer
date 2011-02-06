
function render(root, playlist) {
    if (playlist == '')
        render_playlists(root);
    else
        render_playlist(root, playlist);
}

function render_playlist(root, playlist) {
    var tree = new Ext.tree.TreePanel({
        renderTo: Ext.getBody(),
        title: playlist,
        width: 700,
        height: 500,
        userArrows: true,
        animate: true,
        autoScroll: true,
        dataUrl: 'data-playlist.php?root='+root+'&path='+playlist,
        root: {
            nodeType: 'async',
            text: root
        },
        listeners: {
            render: function() {
                this.getRootNode().expand();
            },
/*            checkchange: function(node, checked) {
                toggleCheck(node, checked);
                if (checked) {
                    node.getUI().addClass('complete');
                }
                else {
                    node.getUI().removeClass('complete');
                }
            }*/
            checkchange: function(n, checked) {
                n.expand();
                n.expandChildNodes(true);
                n.eachChild(function(child){
                    child.ui.toggleCheck(checked);
                });
            }
        },
        buttons: [{
            text: 'Cancel',
            handler: function() {
                Ext.Msg.show({
                    title: 'Cancel',
                    msg: 'Are you sure you want to cancel?',
                    buttons: Ext.Msg.YESNO,
                    icon: Ext.MessageBox.QUESTION,
                    fn: function(response) {
                        if (response == "yes") {
                            window.location = 'index.php';
                        }
                    }
                });
            }
        },
        {
            text: 'Save',
            handler: function() {
                var msg = '';
                Ext.each(tree.getChecked(), function(node) {
                    if(msg.length > 0) {
                        msg += ',';
                    }
                    msg += node.id;
                });
                // Basic request in Ext
                Ext.Ajax.request({
                    url: 'save_playlist.php?root='+root+'&path='+playlist,
                    params: { data: Ext.encode(msg.split(',')) },
                    success: function(response, opts) {
                        //var obj = Ext.decode(response.responseText);
                        //var jsonData = Ext.decode(result.responseText);
                        //var options = Ext.decode(result.responseText).options;

                        //var resultMessage = jsonData.data.result;
                        Ext.Msg.show({
                            title: 'Playlist saved', 
                            msg: response.responseText,
                            icon: Ext.Msg.INFO,
                            minWidth: 200,
                            buttons: Ext.Msg.OK
                        });
                    }
                    //failure: function(response, opts) {
                        // nop
                    //}
                });
            }
        }]

    })
}
