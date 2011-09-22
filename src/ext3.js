
// Global variable to be used in context menus
var currentNode = '';
var restrictCascade = false;

function render(reload, root, playlist) {
{
    Ext.Ajax.request({
        url: 'load_filesystem.php'+(reload ? "?reload=1" : ""),
        success: function(response, opts) {
            //  Update table
            if (playlist == '')
                render_playlists(root);
            else
                render_playlist(root, playlist);
            //  Hide loading message
            var loadingMask = Ext.get('loading-mask');
            var loading = Ext.get('loading');
            loading.fadeOut({ duration: 0.2, remove: true });
            //  Hide loading mask"
            loadingMask.setOpacity(0.9);
            loadingMask.shift({
                xy: loading.getXY(),
                width: loading.getWidth(),
                height: loading.getHeight(),
                remove: true,
                duration: 1,
                opacity: 0.1,
                easing: 'bounceOut'
            });
        },
        failure: function(response, opts) {
            alert('Could not load filesystem: '+response.responseText);
        }
    });
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
                    var title = 'Create new playlist';
                    Ext.Msg.prompt(title, 'Enter the name of the new playlist', function(btn, text) {
                        if (btn == 'ok'){
                            // Create playlist
                            Ext.Ajax.request({
                                url: 'playlist.php?q=create&root='+root+'&path='+currentNode.id+'&name='+text,
                                success: function(response, opts) {
                                    Ext.Msg.show({
                                        title: title,
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
                    var title = 'Delete playlist';
                    Ext.Msg.show({
                        title: title,
                        msg: 'Are you sure you want to delete the playlist?',
                        buttons: Ext.Msg.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        fn: function(response) {
                            if (response == "yes") {
                                // Delete playlist
                                Ext.Ajax.request({
                                    url: 'playlist.php?q=delete&root='+root+'&path='+currentNode.id,
                                    success: function(response, opts) {
                                        Ext.Msg.show({
                                            title: title,
                                            msg: response.responseText,
                                            icon: Ext.Msg.INFO,
                                            minWidth: 200,
                                            buttons: Ext.Msg.OK,
                                        });
                                        tree.root.reload();
                                    },
                                    failure: function(response, opts) {
                                        alert("Could not delete playlist: "+response.responseText);
                                    }
                                });
                            }
                        }
                    });
                }
            }
        ]
    });

    /*
     * Tree of playlists
     */

    var tree = new Ext.tree.TreePanel({
        renderTo: 'tree',
        title: 'Playlists',
        //width: 700,
        height: 475,
        userArrows: true,
        animate: false,
        autoScroll: true,
        loadMask: true,
        loader: new Ext.tree.TreeLoader({
            dataUrl: 'data.php?q=playlists&root='+root
        }),
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
        },
        buttons: [{
            text: 'Reload filesystem',
            handler: function() {
                window.location = 'index.php?reload=1';
            }
        }]
    });

    var myTreeSorter = new Ext.tree.TreeSorter(tree, {
    });
    myTreeSorter.doSort(tree.getRootNode());
}

function render_playlist(root, playlist) {
    /*
     * List with all contents of playlist
     */

    var store = new Ext.data.JsonStore({
        url: 'data.php?q=playlist-contents&root='+root+'&path='+playlist,
        fields: ['type', 'content']
    });
    store.load();

    var dataView = new Ext.DataView({
        multiSelect: false,
        store: store,
        emptyText: '<div class="row row-valid">(no media files)</div>',
        tpl: '<tpl for="."><div class="row row-{type}">{content}</div></tpl>',
    });

    var panel = new Ext.Panel({
        id: 'images-view',
        renderTo: 'messages',
        collapsible: true,
        collapsed: true,
        layout: 'fit',
        title: basename(playlist),
        items: dataView
    });

    /*
     * Tree of playlist items
     */
    var tree = new Ext.tree.TreePanel({
        renderTo: 'tree',
        //title: playlist,
        //width: 700,
        height: 475,
        userArrows: true,
        animate: false,
        autoScroll: true,
        loadMask: true,
        loader: new Ext.tree.TreeLoader({
            dataUrl: 'data.php?q=playlist-tree&root='+root+'&path='+playlist
        }),
        root: {
            id: root,
            nodeType: 'async',
            text: root
        },
        listeners: {
            'render': function() {
                this.getRootNode().expand();
            },
            'beforedblclick': function(node, e)  {
                return false; // disable doubleclick
            },
            /*'click': function(node, e) {
                node.ui.toggleCheck(!node.ui.isChecked());
            },*/
            'checkchange': function(node, checked) {
                // Cascade child nodes
                if (!restrictCascade) {
                    if (checked) {
                        node.expand();
                        node.eachChild(function(child) {
                            child.expand();
                            child.ui.toggleCheck(true);
                        });
                    }
                    else {
                        node.eachChild(function(child) {
                            child.ui.toggleCheck(false);
                            child.collapse();
                        });
                        node.collapse();
                    }
                }

                // Toggle parent node
                restrictCascade = true;
                var checkParent = true;
                node.parentNode.eachChild(function(child) {
                    checkParent &= child.ui.isChecked();
                });
                node.parentNode.ui.toggleCheck(checkParent);
                restrictCascade = false;
            }
        },
        buttons: [{
            text: 'Back',
            handler: function() {
                var title = 'Back to playlists';
                Ext.Msg.show({
                    title: title,
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
                    url: 'data.php?q=playlist-invalid-count&root='+root+'&path='+playlist,
                    success: function(response, opts) {
                        var title = 'Playlist saved';
                        var invalidItems = response.responseText;
                        if (invalidItems == "0") {
                            // No invalid items
                            Ext.Ajax.request({
                                url: 'playlist.php?q=save&root='+root+'&path='+playlist,
                                params: { data: Ext.encode(msg.split(',')) },
                                success: function(response, opts) {
                                    Ext.Msg.show({
                                        title: title,
                                        msg: response.responseText,
                                        icon: Ext.Msg.INFO,
                                        minWidth: 200,
                                        buttons: Ext.Msg.OK
                                    });
                                    store.reload();
                                },
                                failure: function(response, opts) {
                                    alert("Could not save playlist: "+response.responseText);
                                }
                            });
                        }
                        else {
                            // Invalid items on current playlist
                            Ext.Msg.show({
                                title: 'Confirm save playlist',
                                msg: 'There are '+invalidItems+' invalid item(s) in the current playlist, are you sure you want to overwrite it?',
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.MessageBox.QUESTION,
                                fn: function(response) {
                                    if (response == "yes") {
                                        Ext.Ajax.request({
                                            url: 'playlist.php?q=save&root='+root+'&path='+playlist,
                                            params: { data: Ext.encode(msg.split(',')) },
                                            success: function(response, opts) {
                                                Ext.Msg.show({
                                                    title: title,
                                                    msg: response.responseText,
                                                    icon: Ext.Msg.INFO,
                                                    minWidth: 200,
                                                    buttons: Ext.Msg.OK
                                                });
                                                store.reload();
                                            },
                                            failure: function(response, opts) {
                                                alert("Could not save playlist: "+response.responseText);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    },
                    failure: function(response, opts) {
                        alert("Could not save playlist: "+response.responseText);
                    }
                });
            }
        }]
    });

    var myTreeSorter = new Ext.tree.TreeSorter(tree, {
    });
    myTreeSorter.doSort(tree.getRootNode());
}
