<?php
namespace test_bench {
  require_once '/../../suitcase.php';
  \suitcase\import('test', 'loader');
  
  require 'bench.php';
  require 'loader_test.php';
  
  $bench = new PackageTestBench();
  $bench->run_and_present_as_text();
}
?>