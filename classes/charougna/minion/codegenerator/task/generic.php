<?php
/**
 * Declares generic charougna codegenerator minion task
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
 * @link      https://github.com/emtou/kohana-tasks-codegenerator/tree/master/classes/charougna/minion/codegenerator/task/generic.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides generic charougna codegenerator minion task
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
 * @link      https://github.com/emtou/kohana-tasks-codegenerator/tree/master/classes/charougna/minion/codegenerator/task/generic.php
 */
abstract class Charougna_Minion_Codegenerator_Task_Generic extends Minion_Task
{
  protected $_configs         = array();
  protected $_params          = array();
  protected $_specific_params = array();
  protected $_template        = NULL;
  protected $_template_name   = '';


  /**
   * Adds a mandatory configuration variable
   *
   * - Updates the $this->_configs array
   * - Fills in the $this->config array if needed
   *
   * @param string $config_name  name of the configuration varible
   * @param string $config_label optional label
   *
   * @return null
   *
   * @see Minion_Task::_config
   */
  protected function _add_mandatory_config($config_name, $config_label = '')
  {
    $this->_configs[$config_name] = array('mandatory' => TRUE, 'label' => $config_label);

    if ( ! in_array($config_name, $this->_config))
    {
      $this->_config[] = $config_name;
    }
  }


  /**
   * Adds an optional configuration variable
   *
   * - Updates the $this->_configs array
   * - Fills in the $this->config array if needed
   *
   * @param string $config_name  name of the configuration varible
   * @param string $config_label optional label
   *
   * @return null
   *
   * @see Minion_Task::_config
   */
  protected function _add_optional_config($config_name, $config_label = '')
  {
    $this->_configs[$config_name] = array('mandatory' => FALSE, 'label' => $config_label);

    if ( ! in_array($config_name, $this->_config))
    {
      $this->_config[] = $config_name;
    }
  }


  /**
   * Checks if all the mandatory configuration variables are set
   *
   * Returns empty string if every mandatory configuration variable
   * are set. Returns errors descriptions if not.
   *
   * Only called from $this->execute();
   *
   * @param array &$config configuration variables received from $this->execute()
   *
   * @return string empty if no errors, else one error per line
   */
  protected function _check_mandatory_configs(array & $config)
  {
    $errors = array();

    foreach ($this->_configs as $config_name => $config_values)
    {
      if ($config_values['mandatory'] and ! isset($config[$config_name]))
      {
        $errors[] = 'Configuration option «'.$config_name.'» is mandatory.';
      }
    }

    return implode(PHP_EOL, $errors);
  }


  /**
   * Checks if every param declared in the template has been filled
   *
   * Returns empty string if every paramhas been filled. Returns
   * errors descriptions if not.
   *
   * Only called from $this->execute();
   *
   * @return string empty if no errors, else one error per line
   */
  protected function _check_template()
  {
    $errors = array();

    $unreplaced_params = array();
    preg_match_all('/\%([\w_]+)\%/', $this->_template, $unreplaced_params);

    $unreplaced_params = array_unique($unreplaced_params[1]);
    sort($unreplaced_params);

    foreach ($unreplaced_params as $unreplaced_param)
    {
      $errors[] = 'Couldn\'t replace param «'.$unreplaced_param.'».';
    }

    return implode(PHP_EOL, $errors);
  }


  /**
   * Fills every param in the template
   *
   * @return null
   */
  protected function _fill_template()
  {
    foreach ($this->_params as $param_name => $param_value)
    {
      $params_inside_param = array();
      preg_match_all('/\%([\w_]+)\%/', $param_value, $params_inside_param);
      foreach ($params_inside_param[1] as $param_inside_param)
      {
        if (isset($this->_params[$param_inside_param]))
        {
          $param_value = preg_replace(
              '/\%'.$param_inside_param.'\%/',
              $this->_params[$param_inside_param],
              $param_value
          );
        }
      }

      $this->_template = preg_replace('/\%'.$param_name.'\%/', $param_value, $this->_template);
    }
  }


  /**
   * Finds the filename
   *
   * @return string filename.php
   */
  protected function _get_filename()
  {
    return strtolower(preg_replace('/^[\w]+_/', '', $this->_get_name())).'.php';
  }


  /**
   * Finds full path of the generated file
   *
   * Base is Kohana DOCROOT
   *
   * application or module
   *
   * @param string $extra extra path
   *
   * @return string full path
   */
  protected function _get_full_path($extra = '')
  {
    $path = '';

    if (isset($this->_params['module']))
    {
      $path .= 'modules/'.$this->_params['module'].'/';
    }
    else
    {
      $path .= 'application/';
    }

    $path .= $extra;

    $path .= $this->_get_path();

    return $path;
  }


  /**
   * Finds the name of the generated file
   *
   * @return string name
   */
  protected function _get_name()
  {
    return $this->_params['name'];
  }


  /**
   * Finds the path of the generated file
   *
   * @return string path
   */
  protected function _get_path()
  {
    if ( ! preg_match('/_/', $this->_get_name()))
      return '';

    $path = strtolower(preg_replace('/_[\w]+$/D', '', $this->_get_name()));

    return preg_replace('/_/', '/', $path).'/';
  }


  /**
   * Declares default specific params
   *
   * - filename
   * - path
   * - today
   *
   * @return null
   */
  protected function _init_specific_params()
  {
    $this->_specific_params['filename'] = $this->_get_filename();
    $this->_specific_params['path']     = $this->_get_path();
    $this->_specific_params['today']    = date('Y-m-d');
  }


  /**
   * Fills in the initial template
   *
   * @return null
   */
  protected function _init_template()
  {
    $this->_template = View::factory('minion/task/codegenerator/'.$this->_template_name);
  }


  /**
   * Sets default params from configuration
   *
   * @return null
   */
  protected function _set_default_params()
  {
    // Load default params
    if ($default_params = Kohana::config('minion/task/codegenerator/default'))
    {
      foreach ($default_params as $param_name => $param_value)
      {
        $this->_params[$param_name] = $param_value;
      }
    }

    // Load default params for template
    if ($default_params = Kohana::config('minion/task/codegenerator/template/'.$this->_template_name))
    {
      foreach ($default_params as $param_name => $param_value)
      {
        $this->_params[$param_name] = $param_value;
      }
    }
  }


  /**
   * Fills in params from configuration variables
   *
   * @param array &$config configuration variables received from $this->execute()
   *
   * @return null;
   */
  protected function _set_params(array & $config)
  {
    foreach ($config as $param_name => $param_value)
    {
      $this->_params[$param_name] = $param_value;
    }
  }


  /**
   * Fills in params from specific params
   *
   * @return null
   */
  protected function _set_specific_params()
  {
    foreach ($this->_specific_params as $param_name => $param_value)
    {
      $this->_template = preg_replace('/\%'.$param_name.'\%/', $param_value, $this->_template);
    }
  }


  /**
   * Extracts template name, initialises it and declares default configuration variables
   *
   * mandatory configuration variables :
   * - name
   *
   * optional configuration variables :
   * - module
   * - write
   * -force
   *
   * return null
   */
  public function __construct()
  {
    $this->_template_name = strtolower(preg_replace('/^[\w]+_/', '', get_class($this)));

    $this->_init_template();

    $this->_add_mandatory_config('name');

    $this->_add_optional_config('module');

    $this->_add_optional_config('write');
    $this->_add_optional_config('force');
  }


  /**
   * Creates the generated file
   *
   * @param array $config configuration variables
   *
   * @return log of the process
   */
  public function execute(array $config)
  {
    if ($errors = $this->_check_mandatory_configs($config))
    {
      return Minion_Cli::color($errors, 'red').PHP_EOL;
    }

    $this->_set_default_params();
    $this->_set_params($config);
    $this->_init_specific_params();
    $this->_set_specific_params();
    $this->_fill_template();

    if ($errors = $this->_check_template())
    {
      return Minion_Cli::color($errors, 'red').PHP_EOL;
    }

    $filename = $this->_get_full_path().$this->_get_filename();

    if ( ! isset($this->_params['write']))
    {
      return Minion_Cli::color('File content :', 'blue').PHP_EOL.
             Minion_Cli::color(str_repeat('-', 80), 'blue').PHP_EOL.
             $this->_template.PHP_EOL.
             Minion_Cli::color(str_repeat('-', 80), 'blue').PHP_EOL.
             Minion_Cli::color('File «'.$filename.'» not written.', 'yellow').PHP_EOL.
             'Use «--write=true» param to create file.'.PHP_EOL;
    }

    if (file_exists(DOCROOT.$filename)
        and ! isset($this->_params['force']))
    {
      return Minion_Cli::color('File «'.$filename.'» already exists.', 'red').PHP_EOL.
             'Use «--force=true» to overwrite.'.PHP_EOL;
    }

    if (file_exists(DOCROOT.$filename)
        and ! is_writable(DOCROOT.$filename))
    {
      return Minion_Cli::color('Can\t write to file «'.$filename.'»: permission denied.', 'red').PHP_EOL;
    }

    // Create directory
    if (file_exists(DOCROOT.$this->_get_full_path())
        and ! file_exists(DOCROOT.$filename)
        and ! is_writable(DOCROOT.$this->_get_full_path()))
    {
      return Minion_Cli::color(
          'Can\t create file «'.$this->_get_filename().'» in «'.$this->_get_full_path().'» directory:'.
          ' permission denied.', 'red'
      ).PHP_EOL;
    }

    if ( ! file_exists(DOCROOT.$this->_get_full_path()))
    {
      $output     = array();
      $return_var = 0;

      exec('mkdir -p "'.DOCROOT.$this->_get_full_path().'"', $output, $return_var);

      if ($return_var != 0)
      {
        return Minion_Cli::color(
            'Error while creating «'.$this->_get_full_path().'» directory: '.
            implode(PHP_EOL, $output), 'red'
        ).PHP_EOL;
      }
    }

    if ( ! $handle = fopen(DOCROOT.$filename, 'w'))
    {
      return Minion_Cli::color('Can\t open file «'.$filename.'» for writing: permission denied.', 'red').PHP_EOL;
    }

    if (fwrite($handle, $this->_template) === FALSE)
    {
      return Minion_Cli::color('Can\t write in file «'.$filename.'».', 'red').PHP_EOL;
    }

    fclose($handle);

    return Minion_Cli::color('File written in «'.$filename.'».', 'green').PHP_EOL;
  }

}