Nested menus for the Contao OpenSource CMS
==========================================

[![Build Status](https://travis-ci.org/bit3/contao-nested-menu.png?branch=master)](https://travis-ci.org/bit3/contao-nested-menu)

The benefit of *nested menu* is to reduce the count of backend menu items and group them into a sub menu.

Define nested menu items
------------------------

First you need a navigation setup with a lot of items.

```php
$GLOBALS['BE_MOD']['my'] = array(
	'entry1' => array(
		'tables' => array('table1'),
	),
	'entry2' => array(
		'tables' => array('table2'),
	),
	'entry3' => array(
		'tables' => array('table3'),
	),
	'entry4' => array(
		'tables' => array('table4'),
	),
	'entry5' => array(
		'tables' => array('table5'),
	),
	'entry6' => array(
		'tables' => array('table6'),
	),
	'entry7' => array(
		'tables' => array('table7'),
	),
	'entry8' => array(
		'tables' => array('table8'),
	),
	'entry9' => array(
		'tables' => array('table9'),
	),
);
```

Now you have to define, which item will be nested into another item.

```php
$GLOBALS['BE_MOD']['my'] = array(
	'entry1' => array(
		'tables' => array('table1'),
	),
	'entry2' => array(
		'tables' => array('table2'),
	),
	'entry3' => array(
		'tables' => array('table3'),
	),
	'entry4' => array(
		'tables' => array('table4'),
	),
	'entry5' => array(
		'nested' => 'grouped_entries',
		'tables' => array('table5'),
	),
	'entry6' => array(
		'nested' => 'grouped_entries',
		'tables' => array('table6'),
	),
	'entry7' => array(
		'nested' => 'grouped_entries',
		'tables' => array('table7'),
	),
	'entry8' => array(
		'nested' => 'grouped_entries',
		'tables' => array('table8'),
	),
	'entry9' => array(
		'nested' => 'grouped_entries',
		'tables' => array('table9'),
	),
);
```

Now a **virtual** structure will be created, that look like this:

```php
$GLOBALS['BE_MOD']['my'] = array(
	'entry1' => array(
		'tables' => array('table1'),
	),
	'entry2' => array(
		'tables' => array('table2'),
	),
	'entry3' => array(
		'tables' => array('table3'),
	),
	'entry4' => array(
		'tables' => array('table4'),
	),
	'grouped_entries' => array(
		'callback' => 'NestedMenuController',
	),
);
```

Keep in mind, the entries `entry5` till `entry9` are only hidden from the user.

The new item `grouped_entries` now provide a navigation listing,
similar to the *old* navigation listing, known from the Contao 2 backend startpage.

It is also possible to make different groups in the sub menu, just add a `:group-name` to the `nested` key.

```php
$GLOBALS['BE_MOD']['my'] = array(
	'entry1' => array(
		'tables' => array('table1'),
	),
	'entry2' => array(
		'tables' => array('table2'),
	),
	'entry3' => array(
		'tables' => array('table3'),
	),
	'entry4' => array(
		'tables' => array('table4'),
	),
	'entry5' => array(
		'nested' => 'grouped_entries:group1',
		'tables' => array('table5'),
	),
	'entry6' => array(
		'nested' => 'grouped_entries:group1',
		'tables' => array('table6'),
	),
	'entry7' => array(
		'nested' => 'grouped_entries:group2',
		'tables' => array('table7'),
	),
	'entry8' => array(
		'nested' => 'grouped_entries:group2',
		'tables' => array('table8'),
	),
	'entry9' => array(
		'nested' => 'grouped_entries:group3',
		'tables' => array('table9'),
	),
);
```

Now `entry5` and `entry6` will be grouped, `entry7` and `entry8` will be grouped and also `entry9` get an own group.

Translations
------------

Every sub menu group require a language key in the `$GLOBALS['TL_LANG']['MOD']` array, equals to the group name.

```php
$GLOBALS['TL_LANG']['MOD']['grouped_entries:group1'] = 'Group 1';
$GLOBALS['TL_LANG']['MOD']['grouped_entries:group2'] = 'Group 2';
$GLOBALS['TL_LANG']['MOD']['grouped_entries:group3'] = 'Group 3';
```

Security
--------

Because nested menu is just a visual modification, you can grant or limit access to every single menu item, including the grouped item itself.
You will not miss all any permissions Contao provide.
