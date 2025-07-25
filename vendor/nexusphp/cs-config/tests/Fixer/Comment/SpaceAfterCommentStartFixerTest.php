<?php

declare(strict_types=1);

/**
 * This file is part of Nexus CS Config.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Nexus\CsConfig\Tests\Fixer\Comment;

use Nexus\CsConfig\Fixer\AbstractCustomFixer;
use Nexus\CsConfig\Fixer\Comment\SpaceAfterCommentStartFixer;
use Nexus\CsConfig\Test\AbstractCustomFixerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 *
 * @deprecated v3.26.0
 */
#[CoversClass(AbstractCustomFixer::class)]
#[CoversClass(SpaceAfterCommentStartFixer::class)]
#[CoversClass(AbstractCustomFixerTestCase::class)]
final class SpaceAfterCommentStartFixerTest extends AbstractCustomFixerTestCase
{
    #[DataProvider('provideFixCases')]
    public function testFix(string $expected, ?string $input = null): void
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, list<string>>
     */
    public static function provideFixCases(): iterable
    {
        yield 'not-simple-comment' => [
            '<?php /* a comment */',
        ];

        yield 'code-separator' => [
            <<<'EOD'
                <?php

                //-------------------------------

                //===============================

                EOD,
        ];

        yield 'no-space' => [
            '<?php // a comment',
            '<?php //a comment',
        ];

        yield 'one-space' => [
            '<?php // an unchanged comment',
        ];

        yield 'two-spaces' => [
            '<?php // two spaces reduced to one',
            '<?php //  two spaces reduced to one',
        ];

        yield 'multiple-spaces' => [
            '<?php // many spaces turned into one',
            '<?php //           many spaces turned into one',
        ];

        yield 'multi-lined' => [
            <<<'EOD'
                <?php

                // a comment
                //
                // another comment

                EOD,
            <<<'EOD'
                <?php

                //a comment
                //
                //  another comment

                EOD,
        ];

        yield 'multi-empty' => [
            <<<'EOD'
                <?php

                //
                //
                //
                // a comment

                EOD,
        ];
    }
}
