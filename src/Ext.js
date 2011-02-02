Ext.onReady(function() {


    
    //alert("RiJo has configured Ext properly!");
});

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
        dataUrl: 'data.php?playlist=' + playlist,
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
                    title: 'Abort', 
                    msg: 'Cancel pressed',
                    icon: Ext.Msg.INFO,
                    minWidth: 200,
                    buttons: Ext.Msg.OK
                });
            }
        },
        {
            text: 'Save',
            handler: function() {
                var msg = '', selNodes = tree.getChecked();
                Ext.each(selNodes, function(node) {
                    if(msg.length > 0) {
                        msg += '<br>';
                    }
                    msg += node.id;
                });
                Ext.Msg.show({
                    title: 'Completed Tasks', 
                    msg: msg.length > 0 ? msg : 'None',
                    icon: Ext.Msg.INFO,
                    minWidth: 200,
                    buttons: Ext.Msg.OK
                });
            }
        }]

    })
}
