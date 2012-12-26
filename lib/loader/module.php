<?php
namespace loader;

/**
 * Main loading module
 * Loads single classes and bundles
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Battlesuit
 * @subpackage loader
 */
class Module {
  
  /**
   * Extension for loaded scriptfiles
   *
   * @access public
   * @var string
   */
  public $script_extension = '.php';
  
  /**
   * Registration status changed by register() and unregister()
   * 
   * @access private
   * @var boolean
   */
  private $registered = false;
  
  /**
   * Qualified definitions with corresponding files
   *
   * @access private
   * @var array
   */
  private $definitions = array();
  
  /**
   * Namespaces with corresponding directories
   *
   * @access private
   * @var array
   */
  private $scopes = array();

  /**
   * Bundle names with corresponding directories
   *
   * @access private
   * @var array
   */
  private $imports = array();  
  
  /**
   * Import directories for bundles
   *
   * @access private
   * @var array
   */
  private $import_directories = array();
  
  /**
   * Builds and returns a singleton
   *
   * @static
   * @access public
   * @return Module
   */
  static function instance() {
    static $instance;
    if(isset($instance)) return $instance;
    
    $instance = new static();
    $instance->register();
    return $instance;
  }
  
  /**
   * Loads a class or interface by a fully qualified name
   *
   * @access public
   * @param string $qualified_name
   */
  function load_definition($qualified_name) {
    if(isset($this->definitions[$qualified_name])) $this->load_file($this->definitions[$qualified_name]);
    elseif(($file = $this->find_file_for($qualified_name))) $this->load_file($file);
  }
  
  /**
   * Requires the given file
   *
   * @access protected
   * @param string $file
   */
  protected function load_file($file) {
    require $file;
  }
  
  /**
   * Finds a file for a given qualified definition
   *
   * @access protected
   * @param string $definition
   * @return string or boolean(false)
   */
  protected function find_file_for($definition) {
    list($namespace, $camelcased_name) = $this->break_definition($definition);
    $filename = DIRECTORY_SEPARATOR.$this->lowerscore($camelcased_name).$this->script_extension;
    $path = '';
    
    if(!empty($namespace)) {
      $each_namespace = $namespace;
      $lbp = false;
      
      do {
        $lbp = strrpos($each_namespace, '\\');
        $path = $lbp ? DIRECTORY_SEPARATOR.substr($each_namespace, $lbp+1).$path : DIRECTORY_SEPARATOR.$namespace;
        
        if(($file = $this->find_file_under($each_namespace, $path.$filename))) {
          return $this->definitions[$definition] = $file;
        }
        
        $each_namespace = substr($each_namespace, 0, $lbp);
      } while($lbp);
    }
    
    # find under global scopes
    $namespace_path = DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
    if(($file = $this->find_file_under('\\', $namespace_path.$filename))) return $this->definitions[$definition] = $file;
    
    return false;
  }
  
  /**
   * Looks up a file under a given namespace
   *
   * @access protected
   * @param string $namespace
   * @param string $filepath
   * @return string or boolean(false)
   */
  protected function find_file_under($namespace, $filepath) {
    if(!empty($this->scopes[$namespace])) {
      foreach($this->scopes[$namespace] as $dir) {
        $file = $dir.$filepath;
        if(file_exists($file)) return $file;
      }
    }
    
    return false;
  }
  
  /**
   * Registeres instance #load_definition to spl_autoload
   *
   * @access public
   * @param boolean $prepend
   * @return boolean
   */
  function register($prepend = false) {
    if($this->registered) return false; # already registered
    
    spl_autoload_register(array($this, 'load_definition'), true, $prepend);
    return $this->registered = true;
  }
  
  /**
   * Unregisters instance #load_definition from spl_autoload
   * Sets $registered to false
   *
   * @access public
   */
  function unregister() {
    if($this->registered) {
      spl_autoload_unregister(array($this, 'load_definition'));
      $this->registered = false;
    }
  }
  
  /**
   * Test the autoload registration status
   *
   * @access public
   * @return boolean
   */
  function registered() {
    return $this->registered;
  }
  
  /**
   * Maps a class/interface/trait definition to a real file
   *
   * @access public
   * @param string $definition
   * @param string $file
   */   
  function def($definition, $file) {
    $this->definitions[$definition] = realpath($file);
  }
  
  /**
   * Maps a namespaced scope to a real directory
   *
   * @access public
   * @param string $namespace
   * @param string $dir
   */  
  function scope($namespace, $dir) {
    if(!isset($this->scopes[$namespace])) {
      $this->scopes[$namespace] = array();
    }
    
    $this->scopes[$namespace][] = realpath($dir);
  }
  
  /**
   * Adds a lookup directory for finding bundles
   *
   * @access public
   * @param string $dir
   */
  function import_from($dir) {
    $dir = realpath($dir);
    
    if(isset($this->import_directories[$dir])) {
      throw new Error("Double assigned import directory $dir");
    }
    
    $this->import_directories[$dir] = $dir;
  }
  
  /**
   * Initializes and autoloads a bundle by name
   *
   * @access public
   * @param string $name
   * @return boolean
   */
  function import($name) {
    if(isset($this->imports[$name])) return false;
    
    $path = str_replace('-', '/', $name);
    
    foreach($this->import_directories as $dir) {
      $bundle_dir = "$dir/$name";    
      if(!is_dir($bundle_dir)) continue;
      
      $lib_dir = "$bundle_dir/lib";
      $init_file = "$lib_dir/$path.php";
      
      if(file_exists($init_file)) {
        require $init_file;
        
        $this->imports[$name] = $bundle_dir;
        $namespace = str_replace('/', '\\', $path);
        $autoload_function = "$namespace\autoload";
        
        if(function_exists($autoload_function)) {
          call_user_func("$namespace\autoload", $this);
        }
        
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * Initializes and autoloads a bunch of bundles by name
   * Each given argument loads a bundle
   *
   * @access public
   */
  function import_many() {
    foreach(func_get_args() as $bundle) $this->import($bundle);
  }
  
  /**
   * Breaks a qualified name into namespace and basename
   *
   * @access private
   * @param string $definition
   * @return string
   */
  private function break_definition($definition) {
    $namespace = $basename = null;
    $definition = trim($definition, ' \\');
    
    if(($last_backslash_pos = strrpos($definition, '\\')) !== false) {
      $namespace = substr($definition, 0, $last_backslash_pos);
      $basename = substr($definition, $last_backslash_pos+1);
    } else $basename = $definition;
    
    return array($namespace, $basename);
  }
  
  /**
   * Converts camelcased or pascalcased string into underscored and lowercased ones
   *
   * Example
   *  PascalCasedWord => pascal_cased_word
   *
   * @access private
   * @param string $str
   * @return string
   */
  private function lowerscore($str) {
    return strtolower(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $str));
  }
}
?>