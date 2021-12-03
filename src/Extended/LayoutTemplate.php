<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Extended;

use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\EasyTemplate;

/**
 * Class LayoutTemplate TODO
 * @package PhpPkg\EasyTpl\Extended
 */
class LayoutTemplate extends EasyTemplate
{

    protected function init(CompilerInterface $compiler): void
    {
        parent::init($compiler);

        $compiler
            ->addDirective('layout', function (string $tplName) {
                return ExtendLoader::new()->handle();
            })
            ->addDirective('contents', function () {
                return '$this->contents();';
            });
    }
}
