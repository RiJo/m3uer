Ext.tree.TreeNodeTriStateUI = function() {
	Ext.tree.TreeNodeTriStateUI.superclass.constructor.apply(this, arguments);
	this.partial = false;
};

Ext.extend(Ext.tree.TreeNodeTriStateUI, Ext.tree.TreeNodeUI, {
	renderElements : function(n, a, targetNode, bulkRender){
		this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';
		
		var cb = Ext.isBoolean(a.checked),
		nel,
		href = a.href ? a.href : Ext.isGecko ? "" : "#",
		buf = ['<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls,'" unselectable="on">',
		'<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
		'<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
		'<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on" />',
		cb ? ('<span class="styledCheckboxWrap"><input class="x-tree-node-cb styledCheckbox" type="checkbox" ' + (a.checked ? 'checked="checked" />' : '/>') + '</span>' ) : '',
		'<a hidefocus="on" class="x-tree-node-anchor" href="',href,'" tabIndex="1" ',
		 a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '><span unselectable="on">',n.text,"</span></a></div>",
		'<ul class="x-tree-node-ct" style="display:none;"></ul>',
		"</li>"].join('');
		
		if(bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())){
			this.wrap = Ext.DomHelper.insertHtml("beforeBegin", nel, buf);
		}else{
			this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf);
		}
		
		this.elNode = this.wrap.childNodes[0];
		this.ctNode = this.wrap.childNodes[1];
		var cs = this.elNode.childNodes;
		this.indentNode = cs[0];
		this.ecNode = cs[1];
		this.iconNode = cs[2];
		var index = 3;
		if(cb){
			this.checkbox = cs[3].firstChild;
			// fix for IE6
			this.checkbox.defaultChecked = this.checkbox.checked;
			index++;
		}
		this.anchor = cs[index];
		this.textNode = cs[index].firstChild;
	},
	
	toggleCheck: function(value, partial){
		var cb = this.checkbox;
		if(cb){
			cb.checked = (value === undefined ? !cb.checked : value);
			cb.parentNode.className = 'styledCheckboxWrap' + (cb.checked ? (partial ? ' wrapPartial' : ' wrapChecked') : '');
			this.partial = cb.checked && partial;
			this.onCheckChange();
		}
	},
	
	updateCheck: function(){
		if ( this.node.childNodes.length == 0 )
			return;
		this.partial = 0;
		Ext.each( this.node.childNodes, function(item){
			var ui = item.getUI();
			if (ui.isChecked()) {
				this.partial++;
				if (ui.partial)
				{
					this.toggleCheck(true,true);
					return false;
				}
			}
		}, this );
		if ( this.partial !== true )
			this.toggleCheck( this.partial > 0, this.partial < this.node.childNodes.length );
	}
});

Ext.onReady(function(){
    var tree = new Ext.tree.TreePanel({
        renderTo:'tree-div',
        title: 'My Task List',
        height: 300,
        width: 400,
        autoScroll:true,
        animate:true,
        enableDD:true,
        containerScroll: true,
        rootVisible: false,
        frame: true,
        root: {
            nodeType: 'async'
        },
        cls: 'x-tree-noicon',
        loader: new Ext.tree.TreeLoader({
            dataUrl: 'data-playlist.php?root=/home/rijo/programming/github/m3uer/src&path=test.m3u',
            uiProviders: { default: Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI }
        }),
        listeners: { 'checkchange': checkchange }
    });
    tree.getRootNode().expand(true);
}); 

var changing = false;

function checkchange(node, checked)
{
	if (!changing)
	{
		changing = true;
		node.expand(true, false);
		if (checked)
			node.cascade( function(node){ node.getUI().toggleCheck(true) } );
		else
			node.cascade( function(node){ node.getUI().toggleCheck(false) } );
		node.bubble( function(node){ if (node.parentNode) node.getUI().updateCheck() } );
		changing = false;
	}
}
