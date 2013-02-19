/**
 * Nested menu for Contao Open Source CMS
 * Copyright (C) 2013 bit3 UG
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    NestedMenu
 * @license    LGPL
 * @filesource
 */

$(document).addEvent('domready', function() {
	var left = $('left');

	Object.each(nestedMenuItems, function(groups, nested) {
		var icon = $('nested_' + nested);

		if (icon) {
			icon.inject(icon.getParent(), 'after');

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
			menu.inject(icon, 'after');

			var size = menu.getDimensions();

			menu.setStyle('width', 0);
			menu.setStyle('opacity', 0);
			menu.setStyle('display', '');

			menu.set('morph', {
				duration: 'short',
				link: 'cancel'
			});

			var li = icon.getParent();
			var navigation = li.getParent().getParent().getParent();

			icon.addEvent('mouseenter', function() {
				var navigationPosition = navigation.getPosition();

				var offset = li.getPosition();
				var top = offset.y - Math.ceil(size.height / 2);

				menu.setStyles({
					left: (offset.x + li.getWidth()) + 'px',
					top: Math.max(navigationPosition.y, top) + 'px'
				})
				menu.morph({
					width: size.width + 'px',
					opacity: 1
				});
			});
			li.addEvent('mouseleave', function() {
				menu.morph({
					width: 0,
					opacity: 0
				});
			});
		}
	});
});