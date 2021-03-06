<?php
/*
 * Copyright (c) 2015 Babymarkt.de GmbH - All Rights Reserved
 *
 * All information contained herein is, and remains the property of Baby-Markt.de
 * and is protected by copyright law. Unauthorized copying of this file or any parts,
 * via any medium is strictly prohibited.
 */

namespace BabymarktExt\CronBundle\Tests\Service;


use BabymarktExt\CronBundle\Entity\Cron\Definition;
use BabymarktExt\CronBundle\Service\DefinitionChecker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class DefinitionCheckerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DefinitionChecker
     */
    protected $checker;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $command = new Command('test:command');

        $application = new Application();
        $application->add($command);

        $this->checker = new DefinitionChecker();
        $this->checker->setApplication($application);
    }

    public function testValidDefinition()
    {
        $validDef = new Definition();
        $validDef->setCommand('test:command');

        $this->assertTrue($this->checker->check($validDef));
        $this->assertEmpty($this->checker->getResult());
    }

    public function testInvalidDefinition()
    {
        $validDef = new Definition();
        $validDef->setCommand('unknown:command');

        $this->assertFalse($this->checker->check($validDef));
        $this->assertEquals(DefinitionChecker::RESULT_INCORRECT_COMMAND, $this->checker->getResult());
    }
}
