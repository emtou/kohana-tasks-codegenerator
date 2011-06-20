<?php
/**
 * Declares charougna model codegenerator minion task
 *
 * PHP version 5
 *
 * @group minion.tasks.codegenerator
 *
 * @category  MinionTasksCodegenerator
 * @package   MinionTasksCodegenerator
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-tasks-codegenerator/tree/master/classes/charougna/minion/codegenerator/task/model.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides charougna model codegenerator minion task
 *
 * PHP version 5
 *
 * @group minion.tasks.codegenerator
 *
 * @category  MinionTasksCodegenerator
 * @package   MinionTasksCodegenerator
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-tasks-codegenerator/tree/master/classes/charougna/minion/codegenerator/task/model.php
 */
abstract class Charougna_Minion_Codegenerator_Task_Model extends Charougna_Minion_Codegenerator_Task_Generic
{

  /**
   * Adds specific param «model_name»
   *
   * @return null
   */
  protected function _init_specific_params()
  {
    parent::_init_specific_params();

    $this->_specific_params['model_name'] = $this->_get_name();
  }


  /**
   * Finds full model path
   *
   * @param string $extra no extra parameter
   *
   * @return string $path
   */
  protected function _get_full_path($extra = '')
  {
    return parent::_get_full_path('classes/model/');
  }


  /**
   * Sets «model» type param
   *
   * @return null
   */
  protected function _set_default_params()
  {
    parent::_set_default_params();

    $this->_params['type'] = 'model';
  }

} // end Charougna_Minion_Codegenerator_Task_Model