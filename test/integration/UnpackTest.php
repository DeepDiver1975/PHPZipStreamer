<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 */

class UnpackTest extends \PHPUnit\Framework\TestCase
{
    /** @var false|string */
    private $tmpfname;

    protected function setUp(): void
    {
        parent::setUp();

        // create a zip file in tmp folder
        $this->tmpfname = tempnam('/tmp', 'FOO');
        $outstream = fopen($this->tmpfname, 'wb');

        $zip = new ZipStreamer\ZipStreamer((array(
            'outstream' => $outstream
        )));
        $stream = fopen(__DIR__ . '/../../README.md', 'rb');
        $zip->addFileFromStream($stream, 'README.test');
        fclose($stream);
        $zip->finalize();

        fflush($outstream);
        fclose($outstream);
    }

    public function test7zip(): void
    {
        $output = [];
        $return_var = -1;
        exec('7z t ' . escapeshellarg($this->tmpfname), $output, $return_var);

        $this->assertEquals(0, $return_var);
        $this->assertEquals('1 file, 943 bytes (1 KiB)', $output[5]);
        $this->assertEquals('Testing archive: ' . $this->tmpfname, $output[7]);
        $this->assertEquals('Path = ' . $this->tmpfname, $output[9]);
        $this->assertEquals('Type = zip', $output[10]);
        $this->assertEquals('Physical Size = 943', $output[11]);
    }

    public function testUnzip(): void
    {
        $output = [];
        $return_var = -1;
        exec('unzip -t ' . escapeshellarg($this->tmpfname), $output, $return_var);

        $this->assertEquals(0, $return_var);
        $this->assertEquals('Archive:  ' . $this->tmpfname, $output[0]);
        $this->assertEquals('    testing: README.test              OK', $output[1]);
        $this->assertEquals('No errors detected in compressed data of ' . $this->tmpfname . '.', $output[2]);
    }
}
