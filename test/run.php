<?php
namespace test_bench {
  require '../test/lib/test.php';
  require __DIR__.'/../lib/loader.php';
  require __DIR__.'/loader/module_test.php';
  
  class PackageTestBench extends Base {
    function initialize() {
      $this->add_test(new \loader\ModuleTest());
    }
  }
  
  $bench = new PackageTestBench();
  $bench->run_and_present_as_text();
}
?>