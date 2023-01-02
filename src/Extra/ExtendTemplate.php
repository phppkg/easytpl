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
class ExtendTemplate extends EasyTemplate
{
    public const BLOCK_PREFIX = 'ET_BLOCK_';

    /**
     * @var self|null
     */
    private ?self $extendEt = null;

    // private string $extendTpl = '';

    /**
     * @var string current block name.
     */
    private string $currentBlock = '';

    /**
     * @param CompilerInterface $compiler
     *
     * @return void
     */
    protected function initCompiler(CompilerInterface $compiler): void
    {
        parent::initCompiler($compiler);

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
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        try {
            parent::renderFile($tplFile, $tplVars);

            $this->resetContext();
        } catch (Throwable $e) {
            $this->resetContext(true);
            throw $e;
        }

        $blockName = $this->currentBlock;
        Assert::empty($blockName, "error: the block '$blockName' must end by 'endblock'");

        // if use extends() on tpl file.
        if ($this->extendEt) {
            $contents = array_merge($this->extendEt->blockContents, $this->blockContents);
            // reset runtime property.
            $this->extendEt = null;
        } else {
            $contents = $this->blockContents;
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
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        try {
            parent::renderString($tplCode, $tplVars);

            $this->resetContext();
        } catch (Throwable $e) {
            $this->resetContext(true);
            throw $e;
        }

        return implode("\n", $this->blockContents);
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
     * @param array $tplVars TODO
     *
     * @return void
     */
    protected function startBlock(string $name, array $tplVars = []): void
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
