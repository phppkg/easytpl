<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl;

use InvalidArgumentException;
use PhpPkg\EasyTpl\Concern\CompiledTemplateTrait;
use PhpPkg\EasyTpl\Contract\CompilerInterface;
use RuntimeException;
use Throwable;
use Toolkit\Stdlib\Helper\Assert;
use function array_merge;
use function implode;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function sprintf;
use function trim;

/**
 * Class ExtendTemplate
 *
 * Support extends layout file and with block stmt.
 *
 * ## Usage
 *
 * - on layout file: layouts/main.tpl
 *
 * ```php
 * {{ block 'header' }}
 * header contents in layout main.
 * {{ endblock }}
 *
 * {{ block 'body' }}
 * body contents in layout main.
 * {{ endblock }}
 *
 * {{ block 'footer' }}
 * footer contents in layout main.
 * {{ endblock }}
 * ```
 *
 * - on page file: home/index.tpl
 *
 * ```php
 * {{ extends('layouts/main.tpl') }}
 *
 * {{ block 'body' }}
 * body contents in home index.
 * {{ endblock }}
 * ```
 *
 * **after rendered:**
 *
 * ```text
 * header contents in layout main.
 * body contents in home index.
 * footer contents in layout main.
 * ```
 */
class ExtendTemplate extends PhpTemplate
{
    use CompiledTemplateTrait;

    /**
     * @var self|null
     */
    private ?self $extendEt = null;

    // private string $extendTpl = '';

    /**
     * @var string[]
     */
    protected array $allowExt = ['.html', '.phtml', '.php', '.tpl'];

    /**
     * @var string current block name.
     */
    private string $currentBlock = '';

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
        $extendFn = function (string $body) {
            $body = trim($body, '() ');
            return sprintf('$this->extends(%s)', $body);
        };

        $compiler
            ->addDirective('extend', $extendFn)
            ->addDirective('extends', $extendFn)
            // syntax: {{ block 'NAME' }}
            ->addDirective('block', function (string $body) {
                $block = trim($body, '() ');
                return sprintf('$this->startBlock(%s);', $block);
            })
            ->addDirective('endblock', function () {
                return '$this->endBlock();';
            });
    }

    /**
     * Render give template file.
     *
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        try {
            $phpFile = $this->compileFile($tplFile);

            $this->doRenderFile($phpFile, $tplVars);
        } catch (Throwable $e) {
            $this->resetContext(true);

            if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException) {
                throw $e;
            }
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $blockName = $this->currentBlock;
        Assert::empty($blockName, "error: the block '$blockName' must end by 'endblock'");

        // if use extends() on tpl file.
        if ($this->extendEt) {
            $contents = array_merge($this->extendEt->blockContents, $this->blockContents);
            $this->resetContext();
        } else {
            $contents = $this->blockContents;
            // tip: cannot reset property, it will used on parent renderFile();
            $this->currentBlock = '';
        }

        return implode("\n", $contents);
    }

    /**
     * Render give template string.
     *
     * @param string $tplCode
     * @param array $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        $tplCode = $this->compiler->compile($tplCode);

        try {
            parent::renderString($tplCode, $tplVars);
        } catch (Throwable $e) {
            $this->resetContext(true);

            if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException) {
                throw $e;
            }
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $rendered = implode("\n", $this->blockContents);
        $this->resetContext();

        return $rendered;
    }

    /**
     * render extends layout template file.
     *
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return void
     */
    protected function extends(string $tplFile, array $tplVars = []): void
    {
        if ($this->extendEt) {
            throw new RuntimeException("cannot use multi extends() on template file: $this->curTplFile");
        }

        $et = clone $this;

        try {
            // cannot use returns, will only return contents not in any block.
            $et->renderFile($tplFile, $tplVars);
        } catch (Throwable $e) {
            throw new RuntimeException("render the extends template '$tplFile' fail", $e->getCode(), $e);
        }

        $this->extendEt = $et;
    }

    /**
     * Block contents
     * - key is block name
     *
     * @var array<string, string>
     */
    private array $blockContents = [];

    /**
     * Start new block
     *
     * @param string $name block name.
     *
     * @return void
     */
    protected function startBlock(string $name): void
    {
        Assert::notBlank($name, 'block name is required');

        $curName = $this->currentBlock;
        Assert::empty($curName, "current in the block '$curName', cannot start new block '$name'");

        // start block rendering
        $this->currentBlock = trim($name);
        Assert::isTrue(ob_start(), "start output buffer fail on block '$name'");
    }

    /**
     * end the block
     *
     * @return void
     */
    protected function endBlock(): void
    {
        $block = $this->currentBlock;
        Assert::notEmpty($block, 'missing block start statement before call endblock.');

        $this->blockContents[$block] = ob_get_clean();
        // reset current
        $this->currentBlock = '';
    }

    /**
     * @param bool $clearBuf
     *
     * @return void
     */
    protected function resetContext(bool $clearBuf = false): void
    {
        // end and clean buffer on started.
        if ($clearBuf && $this->currentBlock) {
            ob_end_clean();
        }

        // reset runtime property.
        $this->extendEt = null;

        $this->currentBlock  = '';
        $this->blockContents = [];
    }
}
