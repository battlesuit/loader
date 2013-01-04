<?php
/**
 * Helper functions for the loader scope
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage loader
 */
namespace loader {
  const available = true;
  
  /**
   * Singleton accessor function
   *
   * @return Module
   */
  function Module() {
    return Module::instance();
  }
  
  /**
   * Imports a bunch of bundles by name
   * 
   */
  function import() {
    call_user_func_array(array(Module(), 'import_many'), func_get_args());
  }
  
  /**
   * Alias for loader\import
   * 
   */
  function load() {
    call_user_func_array('loader\import', func_get_args());
  }
  
  /**
   * Appends a lookup location for bundle imports
   *
   * @param string $dir
   */
  function import_from($dir) {
    Module()->import_from($dir);
  }
  
  /**
   * Alias for loader\import_from
   * 
   */
  function load_from($dir) {
    return import_from($dir);
  }
  
  /**
   * Appends a class/interface/trait definition with its file
   *
   * @param string $definition
   * @param string $file
   */
  function def($definition, $file) {
    Module()->def($definition, $file); 
  }
  
  /**
   * Appends a namespace with its corresponding directory
   *
   * @param string $namespace
   * @param string $dir
   */
  function scope($namespace, $dir) {
    Module()->scope($namespace, $dir);
  }
}
?>