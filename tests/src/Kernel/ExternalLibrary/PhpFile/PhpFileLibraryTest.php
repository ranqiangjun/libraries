<?php

/**
 * @file
 * Contains \Drupal\Tests\libraries\Kernel\ExternalLibrary\PhpFile\PhpFileLibraryTest.
 */

namespace Drupal\Tests\libraries\Kernel\ExternalLibrary\PhpFile;

use Drupal\libraries\ExternalLibrary\Asset\AssetLibrary;
use Drupal\libraries\ExternalLibrary\PhpFile\PhpFileLibraryInterface;
use Drupal\libraries\ExternalLibrary\Registry\ExternalLibraryRegistryInterface;
use Drupal\Tests\libraries\Kernel\KernelTestBase;

/**
 * Tests that the external library manager properly loads PHP file libraries.
 *
 * @group libraries
 */
class PhpFileLibraryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['libraries', 'libraries_test'];

  /**
   * The external library manager.
   *
   * @var \Drupal\libraries\ExternalLibrary\ExternalLibraryManagerInterface
   */
  protected $externalLibraryManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $root = $this->container->get('app.root');
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $this->container->get('module_handler');
    $module_path = $module_handler->getModule('libraries')->getPath();

    $library = $this->prophesize(PhpFileLibraryInterface::class);
    $library->getPhpFiles()->willReturn([
      $root . '/' . $module_path . '/tests/example/example_1.php',
    ]);
    $registry = $this->prophesize(ExternalLibraryRegistryInterface::class);
    $registry->getLibrary('test_php_file_library')->willReturn($library->reveal());
    $this->container->set('libraries.registry', $registry->reveal());

    $this->externalLibraryManager = $this->container->get('libraries.manager');
  }

  /**
   * Tests that the external library manager properly loads PHP file libraries.
   *
   * @covers \Drupal\libraries\ExternalLibrary\ExternalLibraryManager
   * @covers \Drupal\libraries\ExternalLibrary\ExternalLibraryTrait
   * @covers \Drupal\libraries\ExternalLibrary\PhpFile\PhpFileLibrary
   * @covers \Drupal\libraries\ExternalLibrary\PhpFile\PhpRequireLoader
   */
  public function testPhpFileLibrary() {
    if (function_exists('_libraries_test_example_1')) {
      $this->markTestSkipped('Cannot test file inclusion if the file to be included has already been included prior.');
      return;
    }

    $this->assertFalse(function_exists('_libraries_test_example_1'));
    $this->externalLibraryManager->load('test_php_file_library');
    $this->assertTrue(function_exists('_libraries_test_example_1'));
  }

}
