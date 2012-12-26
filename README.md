bs.loader
=========

Autoloader for PHP 5.3+

###Usage

    namespace loader {
      # autoloads a definition
      def('foo\Bar', __DIR__."/path/to/foo/bar.php");
      
      # autoloads a scope(namespace)
      scope('foo', __DIR__);
      scope('my\name\space', __DIR__."/my/name");
    }