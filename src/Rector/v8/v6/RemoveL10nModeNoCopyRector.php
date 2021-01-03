<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v8\v6;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Breaking-79242-RemoveL10n_modeNoCopy.html
 */
final class RemoveL10nModeNoCopyRector extends AbstractRector
{
    use TcaHelperTrait;

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isTca($node)) {
            return null;
        }

        $columns = $this->extractColumns($node);

        if (! $columns instanceof ArrayItem) {
            return null;
        }

        $columnItems = $columns->value;

        if (! $columnItems instanceof Array_) {
            return null;
        }

        foreach ($columnItems->items as $fieldValue) {
            if (! $fieldValue instanceof ArrayItem) {
                continue;
            }

            if (null === $fieldValue->key) {
                continue;
            }

            if (! $fieldValue->value instanceof Array_) {
                continue;
            }

            $addAllowLanguageSynchronization = false;
            $configArray = $fieldValue->value;
            $newConfiguration = new ArrayItem($this->createArray([
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ]
            ), new String_('config'));

            foreach ($fieldValue->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }

                if (null === $configValue->key) {
                    continue;
                }

                if (! $this->isValues($configValue->key, ['l10n_mode', 'config'])) {
                    continue;
                }

                if ($this->isValue($configValue->value, 'mergeIfNotBlank')) {
                    $addAllowLanguageSynchronization = true;
                    $this->removeNode($configValue);
                } elseif ($this->isValue($configValue->value, 'noCopy')) {
                    $this->removeNode($configValue);
                } elseif ($configValue->value instanceof Array_) {
                    $configArray = $configValue->value;
                    $newConfiguration = new ArrayItem($this->createArray([
                        'behaviour' => [
                            'allowLanguageSynchronization' => true,
                        ],
                    ]
                    ));
                    foreach ($configValue->value->items as $configItemValue) {
                        if (! $configItemValue instanceof ArrayItem) {
                            continue;
                        }

                        if (null === $configItemValue->key) {
                            continue;
                        }

                        if (! $configItemValue->value instanceof Array_) {
                            continue;
                        }

                        if (! $this->isValue($configItemValue->key, 'behaviour')) {
                            continue;
                        }

                        $configArray = $configItemValue->value;
                        $newConfiguration = new ArrayItem($this->createTrue(), new String_(
                            'allowLanguageSynchronization'
                        ));

                        foreach ($configItemValue->value->items as $behaviourConfiguration) {
                            if (! $behaviourConfiguration instanceof ArrayItem) {
                                continue;
                            }

                            if (null === $behaviourConfiguration->key) {
                                continue;
                            }

                            if (! $this->isValue($behaviourConfiguration->key, 'allowLanguageSynchronization')) {
                                continue;
                            }

                            $addAllowLanguageSynchronization = false;

                            if (empty($this->getValue($behaviourConfiguration->value))) {
                                $behaviourConfiguration->value = $this->createTrue();
                            }
                        }
                    }
                }
            }

            if (! $addAllowLanguageSynchronization) {
                continue;
            }

            if ($configArray instanceof Array_) {
                $configArray->items[] = $newConfiguration;
            }
        }

        return $node;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove l10n_mode noCopy', [
            new CodeSample(<<<'PHP'
return [
    'ctrl' => [],
    'columns' => [
        'foo' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'Bar',
        ],
    ],
];
PHP
                , <<<'PHP'
return [
    'ctrl' => [],
    'columns' => [
        'foo' => [
            'exclude' => 1,
            'label' => 'Bar',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
    ],
];
PHP
            ),
        ]);
    }
}
