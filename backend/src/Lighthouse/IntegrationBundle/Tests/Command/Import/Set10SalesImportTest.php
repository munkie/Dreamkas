<?php

namespace Lighthouse\IntegrationBundle\Tests\Command\Import;

use Lighthouse\IntegrationBundle\Command\Import\Set10SalesImport;
use Lighthouse\CoreBundle\Document\Log\LogRepository;
use Lighthouse\IntegrationBundle\Set10\Import\Products\Set10Import;
use Lighthouse\IntegrationBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Set10SalesImportTest extends WebTestCase
{
    /**
     * @var string
     */
    protected $prefix = 'lighthouse_sales_';

    protected function tearDown()
    {
        $this->clearTempFiles();
        parent::tearDown();
    }

    /**
     * @return string
     */
    protected function getDirPrefix()
    {
        return $this->prefix . $this->getPoolPosition() . '_';
    }

    protected function clearTempFiles()
    {
        $tmp = new DirectoryIterator('/tmp/');
        /* @var DirectoryIterator $dir */
        foreach ($tmp as $dir) {
            if ($dir->isDir() && 0 === strpos($dir->getFilename(), $this->getDirPrefix())) {
                $it = new RecursiveDirectoryIterator($dir->getPathname());
                /* @var \SplFileInfo $file */
                foreach (new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
                    if ($file->isFile()) {
                        unlink($file->getPathname());
                    }
                }
                rmdir($dir->getPathname());
            }
        }
    }

    public function testExecute()
    {
        $this->factory()->store()->getStores(array('197', '666', '777'));
        $this->factory()->catalog()->getProductByNames(
            array(
                '1',
                '3',
                '7',
                '8594403916157',
                '2873168',
                '2809727',
                '25525687',
                '55557',
                '8594403110111',
                '4601501082159',
                'Кит-Кат-343424',
            )
        );

        $tmpDir = $this->createTempDir();
        $file1 = $this->copyFixtureFileToDir('purchases-14-05-2012_9-18-29.xml', $tmpDir);
        $file2 = $this->copyFixtureFileToDir('purchases-13-09-2013_15-09-26.xml', $tmpDir);

        $this->createConfig(Set10Import::URL_CONFIG_NAME, 'file://' . $tmpDir);

        $commandTester = new CommandTester($this->getSet10SalesImportCommand());
        $commandTester->execute(array());

        $display = $commandTester->getDisplay();

        $this->assertContains(basename($file1), $display);
        $this->assertContains("...                                                  3\nFlushing", $display);
        $this->assertContains(basename($file2), $display);
        // Возвраты не проходят валидацию, так как не указана продажа (Временно игнорируем проблему)
        $this->assertContains(".....................V.V.V...                        32\nFlushing", $display);

        $this->assertFileNotExists($file1);
        $this->assertFileNotExists($file2);

        /* @var LogRepository $logRepository */
        $logRepository = $this->getContainer()->get('lighthouse.core.document.repository.log');
        $cursor = $logRepository->findAll();
        $this->assertCount(3, $cursor);
    }

    public function testExecuteWithErrors()
    {
        $this->factory()->store()->getStore('197');
        $this->factory()->catalog()->getProductByNames(
            array(
                '10001',
                '10002',
                '10003',
                '10004',
                '10005',
                '10006',
                '10007',
                '10008',
                '10009',
            )
        );

        $tmpDir = $this->createTempDir();
        $file1 = $this->copyFixtureFileToDir('purchases-not-found.xml', $tmpDir);

        $this->createConfig(Set10Import::URL_CONFIG_NAME, 'file://' . $tmpDir);

        $commandTester = new CommandTester($this->getSet10SalesImportCommand());
        $commandTester->execute(array());

        $display = $commandTester->getDisplay();

        $this->assertContains(basename($file1), $display);
        // Возвраты не проходят валидацию, так как не указана продажа (Временно игнорируем проблему)
        $this->assertContains("..E...............E.V.V...                           26\nFlushing", $display);
        $this->assertContains('Product with sku "2873168" not found', $display);

        $this->assertFileNotExists($file1);

        /* @var LogRepository $logRepository */
        $logRepository = $this->getContainer()->get('lighthouse.core.document.repository.log');
        $cursor = $logRepository->findAll();
        $this->assertCount(4, $cursor);
    }

    /**
     * @expectedException \Lighthouse\CoreBundle\Exception\RuntimeException
     * @expectedExceptionMessage Failed to read directory
     * @dataProvider invalidDirectoryProvider
     * @param string $url
     */
    public function testInvalidDirectory($url)
    {
        $this->createConfig(Set10Import::URL_CONFIG_NAME, $url);

        /* @var Set10SalesImport $command*/
        $command = $this->getContainer()->get('lighthouse.core.command.import.set10_sales_import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
    }

    /**
     * @return array
     */
    public function invalidDirectoryProvider()
    {
        return array(
            array('file://invalid/path'),
            array('smb://invalid.host/invalid/path'),
        );
    }

    /**
     * @expectedException \Lighthouse\CoreBundle\Exception\RuntimeException
     * @expectedExceptionMessage Failed to read directory
     */
    public function testInvalidDirectoryIsFile()
    {
        $tmpDir = $this->createTempDir();
        $file1 = $this->copyFixtureFileToDir('purchases-14-05-2012_9-18-29.xml', $tmpDir);

        $this->createConfig(Set10Import::URL_CONFIG_NAME, 'file://' . $file1);

        $commandTester = new CommandTester($this->getSet10SalesImportCommand());
        $commandTester->execute(array());
    }

    public function testImportInvalidXmlFile()
    {
        $tmpDir = $this->createTempDir();
        $file1 = $this->copyFixtureFileToDir('purchases-invalid.xml', $tmpDir);

        $this->createConfig(Set10Import::URL_CONFIG_NAME, 'file://' . $tmpDir);

        $commandTester = new CommandTester($this->getSet10SalesImportCommand());
        $commandTester->execute(array());

        $display = $commandTester->getDisplay();
        $this->assertContains('Failed to import sales', $display);
        $this->assertContains('Deleting "purchases-', $display);

        $this->assertFileNotExists($file1);
    }

    public function testOnlyPurchaseFilesImported()
    {
        $this->factory()->store()->getStore('197');
        $this->factory()->catalog()->getProductByNames(
            array(
                '1',
                '3',
                '7',
                '8594403916157',
                '2873168',
                '2809727',
                '25525687',
                '55557',
                '8594403110111',
                '4601501082159',
            )
        );

        $tmpDir = $this->createTempDir();
        $file1 = $this->copyFixtureFileToDir('purchases-invalid.xml', $tmpDir, 'checks-');
        $file2 = $this->copyFixtureFileToDir('purchases-14-05-2012_9-18-29.xml', $tmpDir);
        $file3 = $this->copyFixtureFileToDir('purchases-13-09-2013_15-09-26.xml', $tmpDir, 'purchases-', 'ico');

        $this->createConfig(Set10Import::URL_CONFIG_NAME, 'file://' . $tmpDir);

        $commandTester = new CommandTester($this->getSet10SalesImportCommand());
        $commandTester->execute(array());

        $display = $commandTester->getDisplay();
        $this->assertNotContains(sprintf('Importing "%s"', basename($file1)), $display);
        $this->assertNotContains(sprintf('Importing "%s"', basename($file3)), $display);
        $this->assertContains(sprintf('Importing "%s"', basename($file2)), $display);
        $this->assertContains(sprintf('Deleting "%s"', basename($file2)), $display);

        $this->assertFileExists($file1);
        $this->assertFileNotExists($file2);
        $this->assertFileExists($file3);
    }

    /**
     * @return string
     */
    protected function createTempDir()
    {
        $tmpDir = '/tmp/' . uniqid($this->getDirPrefix(), true) . '/';
        mkdir($tmpDir);
        return $tmpDir;
    }

    /**
     * @param string $file
     * @param string $dir
     * @param string $prefix
     * @param string $extension
     * @return string
     */
    protected function copyFixtureFileToDir($file, $dir, $prefix = 'purchases-', $extension = 'xml')
    {
        $source = $this->getFixtureFilePath('Set10/Import/Sales/' . $file);
        $copyFilename = str_replace('purchases-', $prefix, $file);
        $destination = $dir . '/' . $copyFilename . '.' . $extension;
        copy($source, $destination);
        return $destination;
    }

    /**
     * @return Set10SalesImport
     */
    protected function getSet10SalesImportCommand()
    {
        return $this->getContainer()->get('lighthouse.core.command.import.set10_sales_import');
    }
}
