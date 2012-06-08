var mockIds = function(){
	var uniqueNum = Math.floor( Math.random()*99999 );
		return 'temp-group-'+String(uniqueNum);
}

var mockDataAttr = function(){
	var uniqueNum = Math.floor( Math.random()*99999 / 2 );
		return 'd-'+String(uniqueNum);
}



// Boxer plugin
$.widget("ui.boxer", $.extend({}, $.ui.mouse, {
	_init: function() {
		this.element.addClass("ui-boxer");
		this.dragged = false;
		this._mouseInit();
		this.helper = $(document.createElement('div')).css({border:'2px solid blue'}).addClass("ui-boxer-helper");
	},

	destroy: function() {
		this.element
			.removeClass("ui-boxer ui-boxer-disabled")
			.removeData("boxer")
			.unbind(".boxer");
		this._mouseDestroy();
		return this;
	},

	_mouseStart: function(event) {
		var self = this;
		this.opos = [event.pageX, event.pageY];
		if (this.options.disabled)
			return;
		var options = this.options;
		this._trigger("start", event);
		$(options.appendTo).append(this.helper);

		//if(this.opos[0] >)
		this.helper.css({
			"z-index": 100,
			"position": "absolute",
			"left": event.clientX,
			"top": event.clientY,
			"width": 200,
			"height": 200
		});
	},

	_mouseDrag: function(event) {
		var self = this;
		this.dragged = true;
		if (this.options.disabled)
			return;
		var options = this.options;
        var tmp;
		var x1 = this.opos[0], y1 = this.opos[1], x2 = event.pageX, y2 = event.pageY;
		if (x1 > x2) { tmp = x2; x2 = x1; x1 = tmp; }
		if (y1 > y2) { tmp = y2; y2 = y1; y1 = tmp; }
		//console.log('left:', x1); console.log('top:', y1); console.log('width:', x2-x1); console.log('height:', y2-y1);
		 //if(x2-x1 > 50){
			this.helper.css({
				//'left': x1, 'top': y1,
				'width': x2-x1, 'height': y2-y1
			});
		// }
		this._trigger("drag", event);
		return false;
	},

	_mouseStop: function(event) {
		var self = this;
		this.dragged = false;
		var options = this.options;
		var close = $('<a></a>',{
			'ahref': '#',
			'text': 'x',
			'width': 20,
			'height': 20,
			'class': "closeLayUp"
		});

		var clone = this.helper.clone()
					.removeClass('ui-boxer-helper')
					.attr({'id': mockIds()})
					.data( 'd_id', mockDataAttr() )
					.addClass('resizeLayUps')
					.append(close)
					.appendTo(this.element);
		this._trigger("stop", event, { 'box': clone });
		this.helper.remove();
		return false;
	}

}));
$.extend($.ui.boxer, {
	defaults: $.extend({}, $.ui.mouse.defaults, {
		'appendTo': '.layupjs',
		'distance': 0
	})
});
