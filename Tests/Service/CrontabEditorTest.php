<?php


namespace BabymarktExt\CronBundle\Tests\Service;


use BabymarktExt\CronBundle\Service\CrontabEditor;
use BabymarktExt\CronBundle\Service\Reader\CrontabReaderInterface;
use BabymarktExt\CronBundle\Service\Writer;
use BabymarktExt\CronBundle\Tests\Fixtures\BufferedWriter;

class CrontabEditorTest extends \PHPUnit_Framework_TestCase
{

    public function testRemovingCrons()
    {
        /**
         * @var CrontabReaderInterface|\PHPUnit_Framework_MockObject_MockObject $reader
         */
        $reader = $this->getMockBuilder(CrontabReaderInterface::class)->getMock();
        $writer = new BufferedWriter();

        $reader->method('read')->willReturn([
            'row 1',
            'row 2',
            '### CRONTAB-EDITOR-START test ###',
            'some cron definition 1',
            'some cron definition 2',
            '### CRONTAB-EDITOR-END test ###',
            'row 99',
            'row 100',
        ]);

        $editor = new CrontabEditor('test', $reader, $writer);

        $editor->removeCrons();

        $buffer = $writer->getClean();

        $this->assertCount(4, $buffer);
        $this->assertNotContains('### CRONTAB-EDITOR-START test ###', $buffer);
        $this->assertNotContains('### CRONTAB-EDITOR-END test ###', $buffer);
        $this->assertNotContains('some cron definition 1', $buffer);
        $this->assertNotContains('some cron definition 2', $buffer);
    }

    public function testRemovingCronsWithMultipleBlocks()
    {
        /**
         * @var CrontabReaderInterface|\PHPUnit_Framework_MockObject_MockObject $reader
         */
        $reader = $this->getMockBuilder(CrontabReaderInterface::class)->getMock();
        $writer = new BufferedWriter();

        $reader->method('read')->willReturn([
            'row 1',
            '### CRONTAB-EDITOR-START test ###',
            'some cron definition test',
            '### CRONTAB-EDITOR-END test ###',
            'row 2',
            '### CRONTAB-EDITOR-START some_other ###',
            'some cron definition other',
            '### CRONTAB-EDITOR-END some_other ###',
            'row 3',
        ]);

        $editor = new CrontabEditor('test', $reader, $writer);

        $editor->removeCrons();

        $buffer = $writer->getClean();

        $this->assertCount(6, $buffer);
        $this->assertNotContains('### CRONTAB-EDITOR-START test ###', $buffer);
        $this->assertNotContains('### CRONTAB-EDITOR-END test ###', $buffer);
        $this->assertContains('### CRONTAB-EDITOR-START some_other ###', $buffer);
        $this->assertContains('### CRONTAB-EDITOR-END some_other ###', $buffer);
        $this->assertNotContains('some cron definition test', $buffer);
        $this->assertContains('some cron definition other', $buffer);
    }

    public function testUpdateExistingCronsBlock()
    {
        /**
         * @var CrontabReaderInterface|\PHPUnit_Framework_MockObject_MockObject $reader
         */
        $reader = $this->getMockBuilder(CrontabReaderInterface::class)->getMock();
        $writer = new BufferedWriter();

        $reader->method('read')->willReturn([
            'row 1',
            'row 2',
            '### CRONTAB-EDITOR-START test ###',
            'some old cron definition 1',
            'some old cron definition 2',
            '### CRONTAB-EDITOR-END test ###',
            'row 99',
            'row 100',
        ]);

        $editor = new CrontabEditor('test', $reader, $writer);

        $editor->injectCrons([
            'cron-1' => 'the new cron definition 1',
            'cron-2' => 'the new cron definition 2'
        ]);

        $buffer = $writer->getClean();

        $this->assertCount(8, $buffer);
        $this->assertContains('### CRONTAB-EDITOR-START test ###', $buffer);
        $this->assertContains('### CRONTAB-EDITOR-END test ###', $buffer);
        $this->assertNotContains('some old cron definition 1', $buffer);
        $this->assertNotContains('some old cron definition 2', $buffer);
        $this->assertContains('the new cron definition 1', $buffer);
        $this->assertContains('the new cron definition 2', $buffer);
    }

    public function testInjectingCrons()
    {
        /**
         * @var CrontabReaderInterface|\PHPUnit_Framework_MockObject_MockObject $reader
         */
        $reader = $this->getMockBuilder(CrontabReaderInterface::class)->getMock();
        $writer = new BufferedWriter();

        $reader->method('read')->willReturn([
            'row 1',
            'row 2',
        ]);

        $editor = new CrontabEditor('test', $reader, $writer);

        $editor->injectCrons([
            'cron-1' => 'cron definition 1',
            'cron-2' => 'cron definition 2'
        ]);

        $buffer = $writer->getClean();

        $this->assertContains('### CRONTAB-EDITOR-START test ###', $buffer);
        $this->assertContains('### CRONTAB-EDITOR-END test ###', $buffer);
        $this->assertContains('cron definition 1', $buffer);
        $this->assertContains('cron definition 2', $buffer);
    }

}
