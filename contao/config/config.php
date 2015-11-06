<?php

/**
 * Nested menu for Contao Open Source CMS
 * Copyright (C) 2013 bit3 UG
 *
 * PHP version 5
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    NestedMenu
 * @license    LGPL
 * @filesource
 */


if (TL_MODE === 'BE') {
	$GLOBALS['TL_HOOKS']['loadLanguageFile']['nested-menu']  = array('NestedMenuController', 'hookLoadLanguageFile');
	$GLOBALS['TL_HOOKS']['getUserNavigation']['nested-menu'] = array('NestedMenuController', 'hookGetUserNavigation');
}
