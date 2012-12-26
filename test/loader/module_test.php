<?php
namespace loader;

class ModuleTest extends \test_case\Unit {
  function assert_class_loadable($class) {
    $this->assert_true(class_exists($class, true));
  }
  
  function set_up() {
    $this->fixture_dir = realpath(__DIR__."/../fixtures");
    $this->loader = new Module();
    $this->loader->register();
  }
  
  function tear_down() {
    $this->loader->unregister();
  }

  function test_autoload_single_class() {
    $this->loader->def('foo\Bar', "$this->fixture_dir/foo/bar.php");
    $this->assert_class_loadable('foo\Bar');
  }
  
  function test_autoload_in_namespace_with_global_class() {
    $this->loader->scope('\\', $this->fixture_dir);
    $this->assert_class_loadable('MainUser');
  }
  
  function test_autoload_in_namespace_with_namespaced_class() {
    $this->loader->scope('sub_space\subsub_space', "$this->fixture_dir/sub_space");
    $this->assert_class_loadable('sub_space\subsub_space\LoadMe');
    $this->assert_class_loadable('sub_space\subsub_space\LuckyApe');
  }

  function test_load_deep_namespace() {
    $this->loader->scope('foo', "$this->fixture_dir");
    $this->assert_class_loadable('foo\bar\Baz');
  }
  
  function test_load_deep_base_namespace() {
    $this->loader->scope('\\', $this->fixture_dir);
    $this->assert_class_loadable('obama\President');
    $this->assert_class_loadable('obama\president\Rules');
  }
  
  function test_load_same_namespace_in_different_directories() {
    $this->loader->scope('http', $this->fixture_dir."/foo");
    $this->loader->scope('http', $this->fixture_dir."/obama");
    $this->assert_class_loadable('http\Request');
    $this->assert_class_loadable('http\Response');
    $this->assert_class_loadable('http\transaction\Target');
    $this->assert_class_loadable('http\sub\dir\Baz');
  }
}
?>