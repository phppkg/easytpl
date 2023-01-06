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
use function str_replace;

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

    public const MAIN_CONTENT_MARK = '{_MAIN_CONTENT_}';

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
    private string $layoutContent = '';

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
            // use layout file. syntax: {{ layout('layouts/main.tpl', ['name' => 'inhere']) }}
            ->addDirective('layout', function (string $body) {
                /** will call {@see useLayout()} */
                return '$this->useLayout' . $body;
            })
            // use on layout file. syntax: {{ contents }}
            ->addDirective('contents', fn() => self::MAIN_CONTENT_MARK);
    }

    /**
     * Render template file, support `layout()` and $this->defaultLayout.
     *
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function render(string $tplFile, array $tplVars = []): string
    {
        $phpFile = $this->compileFile($tplFile);
        $contents = $this->doRenderFile($phpFile, $tplVars);

        $useLayout = false;
        if ($this->currentLayout) {
            $useLayout = true;
        } elseif ($this->defaultLayout) {
            $useLayout = true;
            $this->useLayout($this->defaultLayout);
        }

        // use layout
        if ($useLayout) {
            $contents = str_replace(self::MAIN_CONTENT_MARK, $contents, $this->layoutContent);

            // reset context
            $this->currentLayout = $this->layoutContent = '';
        }

        return $contents;
    }

    /**
     * Render view file, support layout(), but not apply $this->defaultLayout.
     *
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        $phpFile = $this->compileFile($tplFile);
        $contents = $this->doRenderFile($phpFile, $tplVars);

        // use layout
        if ($this->currentLayout) {
            $contents = str_replace(self::MAIN_CONTENT_MARK, $contents, $this->layoutContent);

            // reset context
            $this->currentLayout = $this->layoutContent = '';
        }

        return $contents;
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
     * use and render layout file
     *
     * @param string $layoutFile
     * @param array $tplVars
     *
     * @return void
     */
    protected function useLayout(string $layoutFile, array $tplVars = []): void
    {
        $this->currentLayout = $layoutFile;
        $this->layoutContent = $this->renderFile($layoutFile, $tplVars);
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
