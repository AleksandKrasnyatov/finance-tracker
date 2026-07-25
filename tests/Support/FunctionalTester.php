<?php

declare(strict_types=1);

namespace Test\Support;

use Codeception\Actor;
use Doctrine\Common\DataFixtures\FixtureInterface;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions {
        loadFixtures as private generateLoadFixtures;
    }

    /**
     * Codeception stubs resolve FixtureInterface relative to Test\Support\_generated.
     * Override with the real Doctrine type so PHPStan accepts fixture class-strings.
     *
     * @param class-string<FixtureInterface>|list<class-string<FixtureInterface>|FixtureInterface>|FixtureInterface $fixtures
     *
     */
    public function loadFixtures($fixtures, bool $append = true): void
    {
        $this->generateLoadFixtures($fixtures, $append);
    }
}
