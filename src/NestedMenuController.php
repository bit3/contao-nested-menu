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


/**
 * Nested menu backend controller.
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    NestedMenu
 */
class NestedMenuController
	extends TwigBackendModule
{
	/**
	 * Singleton instance.
	 *
	 * @var NestedMenuController
	 */
	protected static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return NestedMenuController
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new NestedMenuController();
		}
		return self::$instance;
	}

	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $strTemplate = 'be_nested_menu_controller';

	/**
	 * Merge the BE_MOD entries and build nested items.
	 *
	 * @param $name
	 * @param $language
	 *
	 * @return void
	 */
	public function hookLoadLanguageFile()
	{
		if (TL_MODE == 'BE') {
			foreach ($GLOBALS['BE_MOD'] as &$modules) {
				foreach ($modules as $moduleKey => $module) {
					if (!empty($module['nested'])) {
						list($nested) = explode(':', $module['nested']);

						// create nested menu entry
						if (!isset($modules[$nested])) {
							if (isset($GLOBALS['TL_LANG']['MOD'][$nested])) {
								$label = $GLOBALS['TL_LANG']['MOD'][$nested];

								if (is_array($label)) {
									$label = $label[0];
								}
							}
							if (empty($label)) {
								$label = $nested;
							}

							$pos = array_search(
								$moduleKey,
								array_keys($modules)
							);

							$left  = array_slice($modules, 0, $pos);
							$right = array_slice($modules, $pos);

							$middle = array(
								$nested => array(
									'tables'     => array(''),
									'stylesheet' => 'system/modules/nested-menu/assets/css/nested-menu.css'
								)
							);

							$modules = array_merge($left, $middle, $right);
						}
						else {
							if (!isset($modules[$nested]['tables'])) {
								$modules[$nested]['tables'] = array('');
							}
							else if ($modules[$nested]['tables'][0] !== '') {
								array_unshift($modules[$nested]['tables'], '');
							}

							$modules[$nested]['callback'] = 'NestedMenuController';
						}

						// merge tables
						if (isset($module['tables'])) {
							$modules[$nested]['tables'] = array_merge(
								$modules[$nested]['tables'],
								$module['tables']
							);
						}
					}
				}
			}

			$GLOBALS['TL_JAVASCRIPT']['nested-menu'] = 'system/modules/nested-menu/assets/js/nested-menu.js';

			$GLOBALS['TL_CSS']['nested-menu'] = 'system/modules/nested-menu/assets/css/nested-menu.css';
		}

		unset($GLOBALS['TL_HOOKS']['loadLanguageFile']['nested-menu']);
	}

	/**
	 * Merge nesting items into one item.
	 *
	 * @param array $navigation
	 * @param bool  $showAll
	 *
	 * @return array
	 */
	public function hookGetUserNavigation(array $navigation, $showAll)
	{
		if (TL_MODE == 'BE' && !$showAll) {
			$input = Input::getInstance();
			$do    = $input->get('do');

			$menu = array();

			foreach ($navigation as $groupKey => $group) {
				if (is_array($group['modules'])) {
					$modules = & $navigation[$groupKey]['modules'];
					foreach ($modules as $moduleName => $module) {
						if (!empty($module['nested'])) {
							list($nested) = explode(':', $module['nested']);

							if ($do == $moduleName) {
								$modules[$nested]['class'] .= ' active';
							}
							if (!isset($modules[$nested]['_nested_icon'])) {
								$modules[$nested]['_nested_icon'] = true;
								$modules[$nested]['label'] .= sprintf(
									'<span class="nested-icon" id="nested_%s">►</span>',
									$nested
								);
							}

							if (!isset($menu[$nested][$module['nested']])) {
								$label = isset($GLOBALS['TL_LANG']['MOD'][$module['nested']])
									? $GLOBALS['TL_LANG']['MOD'][$module['nested']]
									: $module['nested'];

								if (is_array($label)) {
									if (count($label) >= 2) {
										array_shift($label);
									}
									$label = array_shift($label);
								}

								$menu[$nested][$module['nested']] = array(
									'label'   => $label,
									'modules' => array($module)
								);
							}
							else {
								$menu[$nested][$module['nested']]['modules'][] = $module;
							}

							unset($modules[$moduleName]);
						}
					}
				}
			}

			$GLOBALS['TL_MOOTOOLS']['nested-menu'] = sprintf(
				'<script>var nestedMenuItems = %s;</script>',
				json_encode($menu)
			);
		}
		return $navigation;
	}

	/**
	 * Generate the module.
	 *
	 * @return string
	 */
	public function generate()
	{
		$input = Input::getInstance();

		$key = $input->get('key');
		if ($key) {
			$do = $input->get('do');

			foreach ($GLOBALS['BE_MOD'] as $modules) {
				if (isset($modules[$do]) && isset($modules[$do][$key])) {
					list($className, $methodName) = $modules[$do][$key];

					$class = new ReflectionClass($className);
					if ($class->hasMethod($methodName)) {
						$module = $class->newInstance();
						$method = $class->getMethod($methodName);
						return $method->invoke($module, $this->objDc, $this->objDc->table, $GLOBALS['BE_MOD'][$do]);
					}
					else {
						return sprintf(
							'<p class="tl_error">Method %s:%s not found!</p>',
							$className,
							$methodName
						);
					}
				}
			}

			return sprintf(
				'<p class="tl_error">Method %s not found!</p>',
				$key
			);
		}

		if ($this->objDc->table) {
			$act = $input->get('act');

			if (!strlen($act) || $act == 'paste' || $act == 'select') {
				$act = ($this->objDc instanceof listable)
					? 'showAll'
					: 'edit';
			}

			switch ($act) {
				case 'delete':
				case 'show':
				case 'showAll':
				case 'undo':
					if (!$this->objDc instanceof listable) {
						$this->log(
							'Data container ' . $this->objDc->table . ' is not listable',
							'Backend getBackendModule()',
							TL_ERROR
						);
						trigger_error(
							'The current data container is not listable',
							E_USER_ERROR
						);
					}
					break;

				case 'create':
				case 'cut':
				case 'cutAll':
				case 'copy':
				case 'copyAll':
				case 'move':
				case 'edit':
					if (!$this->objDc instanceof editable) {
						$this->log(
							'Data container ' . $this->objDc->table . ' is not editable',
							'Backend getBackendModule()',
							TL_ERROR
						);
						trigger_error(
							'The current data container is not editable',
							E_USER_ERROR
						);
					}
					break;

				default:
			}

			return $this->objDc->$act();
		}

		return parent::generate();
	}

	/**
	 * Compile the current element
	 *
	 * @return void
	 */
	protected function compile()
	{
		$user  = BackendUser::getInstance();
		$input = Input::getInstance();

		$navigation = $user->navigation(true);

		$settings    = array(
			'headline' => true
		);
		$do          = $input->get('do');
		$groups      = array();
		$preContent  = '';
		$postContent = '';

		foreach ($GLOBALS['BE_MOD'] as $modules) {
			if (isset($modules[$do])) {
				if (isset($modules[$do]['nested-config'])) {
					$settings = array_merge(
						$settings,
						$modules[$do]['nested-config']
					);
				}
				break;
			}
		}

		// collect groups and items for nested menu
		foreach ($navigation as $groupKey => $group) {
			if (is_array($group['modules'])) {
				$modules = & $navigation[$groupKey]['modules'];
				foreach ($modules as $moduleName => $module) {
					if (!empty($module['nested'])) {
						list($nested) = explode(':', $module['nested']);

						if ($do == $nested) {
							if (
								isset($GLOBALS['TL_LANG']['MOD'][$moduleName]) &&
								is_array($GLOBALS['TL_LANG']['MOD'][$moduleName])
							) {
								$module['description'] = $GLOBALS['TL_LANG']['MOD'][$moduleName][1];
							}

							$groups[$module['nested']][$moduleName] = $module;
						}
					}
				}
			}
		}

		// HOOK: add custom logic
		if (
			isset($GLOBALS['TL_HOOKS']['nestedMenuItems']) &&
			is_array($GLOBALS['TL_HOOKS']['nestedMenuItems'])
		) {
			foreach ($GLOBALS['TL_HOOKS']['nestedMenuItems'] as $callback) {
				$this->import($callback[0]);
				$groups = $this->$callback[0]->$callback[1]($do, $groups);
			}
		}

		// HOOK: add custom logic
		if (
			isset($GLOBALS['TL_HOOKS']['nestedMenuPreContent']) &&
			is_array($GLOBALS['TL_HOOKS']['nestedMenuPreContent'])
		) {
			foreach ($GLOBALS['TL_HOOKS']['nestedMenuPreContent'] as $callback) {
				$this->import($callback[0]);
				$preContent .= $this->$callback[0]->$callback[1]($do, $groups);
			}
		}

		// HOOK: add custom logic
		if (
			isset($GLOBALS['TL_HOOKS']['nestedMenuPostContent']) &&
			is_array($GLOBALS['TL_HOOKS']['nestedMenuPostContent'])
		) {
			foreach ($GLOBALS['TL_HOOKS']['nestedMenuPostContent'] as $callback) {
				$this->import($callback[0]);
				$postContent .= $this->$callback[0]->$callback[1]($do, $groups);
			}
		}

		$this->Template->settings = $settings;
		$this->Template->do       = $do;
		$this->Template->pre      = $preContent;
		$this->Template->post     = $postContent;
		$this->Template->groups   = $groups;
	}
}
