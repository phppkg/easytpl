<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl;

use PhpPkg\EasyTpl\Contract\CompilerInterface;

/**
 * class TextTemplate
 *
 * @author inhere
 */
class TextTemplate extends EasyTemplate
{
    /**
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    /**
     * @param CompilerInterface $compiler
     */
    protected function init(CompilerInterface $compiler): void
    {
        parent::init($compiler);

        // use raw echo for text template
        $compiler->disableEchoFilter();
    }
}
