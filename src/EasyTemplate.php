<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl;

use PhpPkg\EasyTpl\Concern\CompiledTemplateTrait;
use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\Contract\EasyTemplateInterface;

/**
 * Class EasyTemplate
 *
 * ## Usage
 *
 * ### include file
 *
 * ```php
 * Header contents ...
 * {{ include('parts/common.tpl') }}
 * Footer contents ...
 * ```
 *
 * ### layout file
 *
 * this is example layout file: layout.tpl
 *
 * -----
 *
 * ```php
 * Header contents ...
 * {{ contents() }}
 * Footer contents ...
 * ```
 *
 * #### page file
 *
 * ```php
 * PAGE: home.tpl
 *  main contents
 *   for the page home.
 * ```
 *
 * Usage:
 *
 * ```php
 * $et = EasyTemplate::new();
 * $et->render('page/home.tpl');
 * ```
 *
 * #### rendered
 *
 * ```text
 * Header contents ...
 * PAGE: home.tpl
 *  main contents
 *   for the page home.
 * Footer contents ...
 * ```
 *
 * @author inhere
 */
class EasyTemplate extends PhpTemplate implements EasyTemplateInterface
{
    use CompiledTemplateTrait;

    /**
     * @var string[]
     */
    protected array $allowExt = ['.html', '.phtml', '.php', '.tpl'];

    /**
     * Default layout file.
     *
     * @var string
     */
    private string $defaultLayout = '';

    /**
     * Current layout file.
     *
     * @var string
     */
    private string $currentLayout = '';

    /**
     * Create a text template engine instance.
     *
     * @return static
     */
    public static function textTemplate(): self
    {
        $t = new self(['allowExt' => ['.php', '.tpl'],]);
        // use raw echo for text template
        $t->disableEchoFilter();

        return $t;
    }

    /**
     * Class constructor.
     *
     * @param array{tmpDir: string, compiler: class-string|CompilerInterface} $config
     */
    public function __construct(array $config = [])
    {
        // create compiler
        $compiler = $this->createCompiler($config);

        parent::__construct($config);

        // set and init
        $this->setCompiler($compiler);
        $this->initCompiler($compiler);
    }

    /**
     * @param CompilerInterface $compiler
     */
    protected function initCompiler(CompilerInterface $compiler): void
    {
        // add built-in filters
        $this->loadBuiltInFilters();

        $this->initDirectives($compiler);
    }

    /**
     * @param CompilerInterface $compiler
     *
     * @return void
     */
    protected function initDirectives(CompilerInterface $compiler): void
    {
        // include file. syntax: {{ include('path/to/file.tpl') }}
        $compiler->addDirective('include', function (string $body) {
            /** will call {@see include()} */
            return '$this->include' . $body;
        })
            // use layout file. syntax: {{ layout('layouts/main.tpl') }}
            ->addDirective('layout', function (string $body) {
                /** will call {@see useLayout()} */
                return '$this->useLayout' . $body;
            })
            // use on layout file. syntax: {{ contents }}
            ->addDirective('contents', function () {
                return '{_MAIN_CONTENT_}';
            });
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        $phpFile = $this->compileFile($tplFile);

        return $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $tplCode
     * @param array $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        $tplCode = $this->compiler->compile($tplCode);

        return parent::renderString($tplCode, $tplVars);
    }

    /**
     * Include render file and output result.
     * use on template file: {{ include('parts/common.tpl')}}
     *
     * @param string $tplFile
     * @param array $tplVars
     */
    protected function include(string $tplFile, array $tplVars = []): void
    {
        $phpFile = $this->compileFile($tplFile);

        echo $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $defaultLayout
     *
     * @return EasyTemplate
     */
    public function setDefaultLayout(string $defaultLayout): self
    {
        $this->defaultLayout = $defaultLayout;
        return $this;
    }

}
