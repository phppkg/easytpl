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
use RuntimeException;
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

    /**
     * content mark on layout file.
     */
    public const CONTENT_MARK = '{__CONTENT__}';

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
     * - will disable echo filter for default.
     *
     * @param array $config
     *
     * @return static
     */
    public static function newTexted(array $config = []): self
    {
        if (!isset($config['allowExt'])) {
            $config['allowExt'] = ['.php', '.tpl'];
        }

        $t = new self($config);
        // use raw echo for text template
        $t->disableEchoFilter();

        return $t;
    }

    /**
     * Create a text template engine instance.
     *
     * @param array $config
     *
     * @return static
     */
    public static function textTemplate(array $config = []): self
    {
        return self::newTexted($config);
    }

    /**
     * Create a text template engine instance.
     *
     * @param array $config
     *
     * @return static
     */
    public static function newRaw(array $config = []): self
    {
        return self::newTexted($config);
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
                /** will call {@see renderLayout()} */
                return '$this->renderLayout' . $body;
            })
            // use on layout file. syntax: {{ contents }}
            ->addDirective('contents', fn() => self::CONTENT_MARK, true);
    }

    /**
     * Render template file and returns result.
     *
     * - support `layout()` and $this->defaultLayout.
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
            $this->renderLayout($this->defaultLayout);
        }

        // use layout
        if ($useLayout) {
            $contents = str_replace(self::CONTENT_MARK, $contents, $this->layoutContent);

            // reset context
            $this->currentLayout = $this->layoutContent = '';
        }

        return $contents;
    }

    /**
     * Render template file and returns result.
     *
     * - support layout(), but not apply $this->defaultLayout.
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
            $contents = str_replace(self::CONTENT_MARK, $contents, $this->layoutContent);
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
     * Use and render layout file.
     *
     * Usage: {{ layout('layouts/main.tpl') }}
     *
     * @param string $layoutFile
     * @param array $tplVars
     *
     * @return void
     */
    protected function renderLayout(string $layoutFile, array $tplVars = []): void
    {
        // dont allow cycle use layout()
        if ($this->currentLayout) {
            throw new RuntimeException("cannot repeat use 'layout()' on template");
        }

        $phpFile = $this->compileFile($layoutFile);
        $contents = $this->doRenderFile($phpFile, $tplVars);

        $this->currentLayout = $layoutFile;
        $this->layoutContent = $contents;
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
