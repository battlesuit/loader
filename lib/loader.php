<?php
require __DIR__."/functions.php";

class Loader {
  private $registered = false;
  private $definitions = array();
  private $locations = array();
  public $script_extension = '.php';
  
  function load($definition) {
    if(isset($this->definitions[$definition])) $this->load_file($this->definitions[$definition]);
    elseif(($file = $this->find_file_for_definition($definition))) $this->load_file($file);
  }
  
  function load_file($file) {
    require $file;
  }
  
  function find_file_for_definition($definition) {
    list($namespace, $camelcased_name) = $this->break_definition($definition);
    $filepath = DIRECTORY_SEPARATOR.$this->file_for_camelcased_name($camelcased_name);
    
    $dirs = array();
    if(!empty($namespace)) {
      if(!empty($this->locations[$namespace])) {
        $dirs = $this->locations[$namespace];
      } else {
        $namespace_segments = explode('\\', $namespace);
        $each_ns = $segment_path = '';
        
        foreach($namespace_segments as $segment) {
          $each_ns .= $segment;
          if(isset($this->locations[$each_ns])) {
            $dirs = $this->locations[$each_ns];
          } else {
            $segment_path .= DIRECTORY_SEPARATOR.$segment;
          }
          $each_ns .= '\\';
        }
        
        $filepath = $segment_path.$filepath;
      }
    }
    
    if(!empty($this->locations['\\'])) {
      $dirs = array_merge($dirs, $this->locations['\\']);
    }
    
    foreach($dirs as $dir) {
      $file = $dir.$filepath;
      if(file_exists($file)) return $this->definitions[$definition] = $file;
    }
    
    return false;
  }
  
  private function file_for_camelcased_name($name) {
    return strtolower(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $name)).$this->script_extension;
  }
  
  function autoload($definition, $file) {
    $this->definitions[$definition] = $file;
  }
  
  function autoload_in($namespace, $dir) {
    if(!isset($this->locations[$namespace])) {
      $this->locations[$namespace] = array();
    }
    
    $this->locations[$namespace][] = $dir;
  }
  
  function register($prepend = false) {
    if($this->registered) return; # already registered
    
    spl_autoload_register(array($this, 'load'), true, $prepend);
    $this->registered = true;
  }
  
  function unregister() {
    if($this->registered) {
      spl_autoload_unregister(array($this, 'load'));
      $this->registered = false;
    }
  }
  
  private function break_definition($definition) {
    $namespace = $class = null;
    $definition = trim($definition, ' \\');
    
    if(($last_backslash_pos = strrpos($definition, '\\')) !== false) {
      $namespace = substr($definition, 0, $last_backslash_pos);
      $class = substr($definition, $last_backslash_pos+1);
    } else $class = $definition;
    
    return array($namespace, $class);
  }
}
?>