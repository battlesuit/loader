<?php
class LoaderTest extends test_case\Unit {
  function assert_class_loadable($class) {
    $this->assert_true(class_exists($class, true));
  }
  
  function set_up() {
    $this->loader = new Loader();
    $this->loader->register();
  }
  
  function tear_down() {
    $this->loader->unregister();
  }
  
  function test_autoload_single_class() {
    $this->loader->autoload('foo\Bar', __DIR__."/fixtures/foo/bar.php");
    $this->assert_class_loadable('foo\Bar');
  }
  
  function test_autoload_in_namespace_with_global_class() {
    $this->loader->autoload_in('\\', __DIR__."/fixtures");
    $this->assert_class_loadable('MainUser');
  }
  
  function test_autoload_in_namespace_with_namespaced_class() {
    $this->loader->autoload_in('sub_space\subsub_space', __DIR__."/fixtures/sub_space/subsub_space");
    $this->assert_class_loadable('sub_space\subsub_space\LoadMe');
    $this->assert_class_loadable('sub_space\subsub_space\LuckyApe');
  }
  
  function test_load_deep_namespace() {
    $this->loader->autoload_in('foo', __DIR__."/fixtures/foo");
    $this->assert_class_loadable('foo\bar\Baz');
  }
  
  function test_load_deep_base_namespace() {
    $this->loader->autoload_in('\\', __DIR__."/fixtures");
    $this->assert_class_loadable('obama\President');
    $this->assert_class_loadable('obama\president\Rules');
  }
}
?>