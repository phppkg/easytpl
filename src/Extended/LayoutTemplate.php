<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Extended;

use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\EasyTemplate;

/**
 * Class LayoutTemplate TODO
 * @package PhpPkg\EasyTpl\Extended
 */
class LayoutTemplate extends EasyTemplate
{
    protected function initCompiler(CompilerInterface $compiler): void
    {
        parent::initCompiler($compiler);

        $compiler
            ->addDirective('layout', function (string $tplName) {
                return ExtendLoader::new()->handle();
            })
            ->addDirective('contents', function () {
                return '$this->contents();';
            });
    }
}
