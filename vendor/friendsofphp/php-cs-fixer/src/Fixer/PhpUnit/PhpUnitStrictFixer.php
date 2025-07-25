<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @phpstan-type _AutogeneratedInputConfiguration array{
 *  assertions?: list<'assertAttributeEquals'|'assertAttributeNotEquals'|'assertEquals'|'assertNotEquals'>,
 * }
 * @phpstan-type _AutogeneratedComputedConfiguration array{
 *  assertions: list<'assertAttributeEquals'|'assertAttributeNotEquals'|'assertEquals'|'assertNotEquals'>,
 * }
 *
 * @implements ConfigurableFixerInterface<_AutogeneratedInputConfiguration, _AutogeneratedComputedConfiguration>
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class PhpUnitStrictFixer extends AbstractPhpUnitFixer implements ConfigurableFixerInterface
{
    /** @use ConfigurableFixerTrait<_AutogeneratedInputConfiguration, _AutogeneratedComputedConfiguration> */
    use ConfigurableFixerTrait;

    /**
     * @var array<string, string>
     */
    private const ASSERTION_MAP = [
        'assertAttributeEquals' => 'assertAttributeSame',
        'assertAttributeNotEquals' => 'assertAttributeNotSame',
        'assertEquals' => 'assertSame',
        'assertNotEquals' => 'assertNotSame',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPUnit methods like `assertSame` should be used instead of `assertEquals`.',
            [
                new CodeSample(
                    '<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertAttributeEquals(a(), b());
        $this->assertAttributeNotEquals(a(), b());
        $this->assertEquals(a(), b());
        $this->assertNotEquals(a(), b());
    }
}
'
                ),
                new CodeSample(
                    '<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function testSomeTest()
    {
        $this->assertAttributeEquals(a(), b());
        $this->assertAttributeNotEquals(a(), b());
        $this->assertEquals(a(), b());
        $this->assertNotEquals(a(), b());
    }
}
',
                    ['assertions' => ['assertEquals']]
                ),
            ],
            null,
            'Risky when any of the functions are overridden or when testing object equality.'
        );
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();
        $functionsAnalyzer = new FunctionsAnalyzer();

        foreach ($this->configuration['assertions'] as $methodBefore) {
            $methodAfter = self::ASSERTION_MAP[$methodBefore];

            for ($index = $startIndex; $index < $endIndex; ++$index) {
                $methodIndex = $tokens->getNextTokenOfKind($index, [[\T_STRING, $methodBefore]]);

                if (null === $methodIndex) {
                    break;
                }

                if (!$functionsAnalyzer->isTheSameClassCall($tokens, $methodIndex)) {
                    continue;
                }

                $openingParenthesisIndex = $tokens->getNextMeaningfulToken($methodIndex);
                $argumentsCount = $argumentsAnalyzer->countArguments(
                    $tokens,
                    $openingParenthesisIndex,
                    $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openingParenthesisIndex)
                );

                if (2 === $argumentsCount || 3 === $argumentsCount) {
                    $tokens[$methodIndex] = new Token([\T_STRING, $methodAfter]);
                }

                $index = $methodIndex;
            }
        }
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('assertions', 'List of assertion methods to fix.'))
                ->setAllowedTypes(['string[]'])
                ->setAllowedValues([new AllowedValueSubset(array_keys(self::ASSERTION_MAP))])
                ->setDefault([
                    'assertAttributeEquals',
                    'assertAttributeNotEquals',
                    'assertEquals',
                    'assertNotEquals',
                ])
                ->getOption(),
        ]);
    }
}
