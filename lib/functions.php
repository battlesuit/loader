<?php
namespace {
  function loader() {
    static $instance;
    if(isset($instance)) return $instance;
    
    $instance = new Loader();
    $instance->register();
    return $instance;
  }
  
  function autoload($definition, $file) {
    loader()->autoload($definition, $file);
  }
  
  function autoload_in($namespace, $dir) {
    loader()->autoload_in($namespace, $dir);
  }
}
?>