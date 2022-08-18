<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

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
    protected function initCompiler(CompilerInterface $compiler): void
    {
        parent::initCompiler($compiler);

        // use raw echo for text template
        $compiler->disableEchoFilter();
    }
}
