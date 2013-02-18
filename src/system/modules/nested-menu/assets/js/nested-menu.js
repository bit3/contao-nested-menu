$(document).addEvent('domready', function() {
	var left = $('left');

	Object.each(nestedMenuItems, function(groups, nested) {
		var icon = $('nested_' + nested);

		if (icon) {
			var menu = new Element('ul');
			menu.addClass('tl_level_1');
			menu.addClass('nested-sub-menu');

			Object.each(groups, function(group) {
				var groupElement = new Element('li');
				groupElement.addClass('tl_level_1_group');
				groupElement.set('html', group.label);
				groupElement.inject(menu);

				var children = new Element('li');
				children.addClass('tl_parent');
				children.inject(menu);

				var childrenContainer = new Element('ul');
				childrenContainer.addClass('tl_level_2');
				childrenContainer.inject(children);

				Array.each(group.modules, function(module) {
					var linkContainer = new Element('li');
					linkContainer.inject(childrenContainer);

					var link = new Element('a');
					link.addClass(module.class);
					link.setAttribute('href', module.href);
					link.setAttribute('title', module.title);
					if (module.icon) {
						link.setStyle('background-image', module.icon.replace(' style="background-image:', '').replace('"', ''));
					}
					link.set('html', module.label);
					link.inject(linkContainer);
				});
			});

			menu.setStyle('display', 'none');
			menu.inject(icon.getParent(), 'after');

			var size = menu.getDimensions();

			menu.setStyle('height', 0);
			menu.setStyle('opacity', 0);
			menu.setStyle('display', '');

			menu.set('morph', {
				duration: 'short',
				link: 'cancel'
			});

			var timeout = false;
			icon.addEvent('mouseenter', function() {
				menu.morph({
					height: size.height + 'px',
					opacity: 1
				});
			});
			icon.getParent().getParent().addEvent('mouseleave', function() {
				menu.morph({
					height: 0,
					opacity: 0
				});
			});
		}
	});
});