
// Global variable to be used in context menus
var currentNode = '';

function render(root, playlist) {
    if (playlist == '')
        render_playlists(root);
    else
        render_playlist(root, playlist);
}

function render_playlists(root) {
    var ctxDirectory = new Ext.menu.Menu({
        id: 'ctxDirectory',
        items: [
            {
                id: 'add',
                text:'Add new playlist',
                handler: function() {
                    // Prompt for playlist name
                    Ext.Msg.prompt('Create new playlist', 'Enter the name of the new playlist', function(btn, text) {
                        if (btn == 'ok'){
                            // Create playlist
                            Ext.Ajax.request({
                                url: 'new_playlist.php?root='+root+'&path='+currentNode.id+'&name='+text,
                                success: function(response, opts) {
                                    Ext.Msg.show({
                                        title: 'Create new playlist',
                                        msg: response.responseText,
                                        icon: Ext.Msg.INFO,
                                        minWidth: 200,
                                        buttons: Ext.Msg.OK,
                                    });
                                    tree.root.reload();
                                },
                                failure: function(response, opts) {
                                    alert("Could not create playlist: "+response.responseText);
                                }
                            });
                        }
                    });
                }
            }
        ]
    });

    var ctxFile = new Ext.menu.Menu({
        id: 'ctxFile',
        items: [
            {
                id: 'edit',
                text:'Edit playlist',
                handler: function() {
                    window.location = 'index.php?root='+root+'&playlist='+currentNode.id;
                }
            },
            {
                id: 'delete',
                text:'Delete playlist',
                handler: function() {
                    alert('DELETE');
                }
            }
        ]
    });

    var tree = new Ext.tree.TreePanel({
        renderTo: Ext.getBody(),
        title: 'Playlists',
        width: 700,
        height: 500,
        userArrows: true,
        animate: false,
        autoScroll: true,
        dataUrl: 'data_playlists.php?root='+root,
        root: {
            id: root,
            nodeType: 'async',
            text: root
        },
        listeners: {
            'render': function() {
                this.getRootNode().expand();
            },
            'dblclick': function(node, e)  {
                if (node.isLeaf()) {
                    window.location = 'index.php?root='+root+'&playlist='+node.id;
                }
            },
            'contextmenu': function(node, e) {
                currentNode = node;
                if (node.isLeaf()) {
                    ctxFile.show(node.ui.getAnchor());
                }
                else {
                    ctxDirectory.show(node.ui.getAnchor());
                }
            }
        }
    });
}

function render_playlist(root, playlist) {
    var tree = new Ext.tree.TreePanel({
        renderTo: Ext.getBody(),
        title: playlist,
        width: 700,
        height: 500,
        userArrows: true,
        animate: false,
        autoScroll: true,
        dataUrl: 'data_playlist.php?root='+root+'&path='+playlist,
        root: {
            id: root,
            nodeType: 'async',
            text: root
        },
        listeners: {
            'render': function() {
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
            'checkchange': function(node, checked) {
                node.expand();
                node.expandChildNodes(true);
                node.eachChild(function(child){
                    child.ui.toggleCheck(checked);
                });
            }
        },
        buttons: [{
            text: 'Back',
            handler: function() {
                Ext.Msg.show({
                    title: 'Back to playlists',
                    msg: 'Are you sure you want to go back?',
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
                    },
                    failure: function(response, opts) {
                        alert("Could not save playlist: "+response.responseText);
                    }
                });
            }
        }]

    })
}
