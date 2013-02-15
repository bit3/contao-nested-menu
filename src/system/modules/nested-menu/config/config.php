<?php

$GLOBALS['TL_HOOKS']['loadLanguageFile']['nested-menu']  = array('NestedMenuController', 'hookLoadLanguageFile');
$GLOBALS['TL_HOOKS']['getUserNavigation']['nested-menu'] = array('NestedMenuController', 'hookGetUserNavigation');
