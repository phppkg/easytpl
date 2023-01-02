<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Extra;

use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\EasyTemplate;

/**
 * Class LayoutTemplate TODO
 *
 * ## Usage
 *
 * ### layout file
 *
 * this is example layout file: layout.tpl
 *
 * -----
 *
 * ```php
 * Header contents ...
 * {{ mainContents() }}
 * Footer contents ...
 * ```
 *
 * ### page file
 *
 * ```php
 * {{ layout('layout.tpl') }}
 * PAGE: home.tpl
 *  main contents
 *   for the page home.
 * ```
 *
 * ### rendered
 *
 * ```text
 * Header contents ...
 * PAGE: home.tpl
 *  main contents
 *   for the page home.
 * Footer contents ...
 * ```
 */
class LayoutTemplate extends EasyTemplate
{
    protected function initCompiler(CompilerInterface $compiler): void
    {
        parent::initCompiler($compiler);

        $compiler
            ->addDirective('layout', function (string $tplName) {
                $tplName = trim($tplName, '() ');
                return sprintf('$this->renderLayout(%s)', $tplName);
            })
            ->addDirective('contents', function () {
                return '$this->mainContents();';
            });
    }

    protected function renderLayout(): void
    {
        // TODO
    }
}
