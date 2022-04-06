<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Compiler;

use PhpPkg\EasyTpl\Contract\CompilerInterface;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Str;
use function array_shift;
use function explode;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_bool;
use function is_float;
use function is_int;
use function sprintf;
use function str_contains;
use function strlen;

/**
 * class AbstractCompiler
 *
 * @author inhere
 */
abstract class AbstractCompiler implements CompilerInterface
{
    public const PHP_TAG_OPEN  = '<?php';

    public const PHP_TAG_ECHO  = '<?';

    public const PHP_TAG_ECHO1 = '<?=';

    public const PHP_TAG_CLOSE = '?>';

    public string $openTag = '{{';

    public string $closeTag = '}}';

    public const RAW_OUTPUT = 'raw';

    /**
     * @var string
     */
    public string $filterSep = '|';

    /**
     * @var callable|callable-string
     * @see htmlspecialchars()
     */
    public $echoFilterFunc = 'htmlspecialchars';

    /**
     * custom filter name and expr mapping
     *
     * ```php
     * [
     *  'upper'   => 'strtoupper(',
     *  'filter1' => '$this->applyFilter("filter1", ',
     * ]
     * ```
     *
     * @var array<string, string>
     */
    public array $filterMapping = [];

    /**
     * custom directive, control statement token.
     *
     * -----
     * eg: implements: `include('parts/header.tpl')`
     *
     * ```php
     * $compiler->addDirective('include', function(string $body, string $name): string {
     *      // $name : 'include'
     *      // $body : "('parts/header.tpl')"
     *      // do something...
     * });
     * ```
     *
     * @var array{string, callable(string, string): string}
     */
    public array $customDirectives = [];

    /**
     * @return static
     */
    public static function new(): static
    {
        return new static();
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): static
    {
        $this->openTag  = $open;
        $this->closeTag = $close;

        return $this;
    }

    /**
     * @param string $tplFile
     *
     * @return string
     */
    public function compileFile(string $tplFile): string
    {
        $tplCode = File::readAll($tplFile);

        return $this->compile($tplCode);
    }

    /**
     * @param string $echoBody
     *
     * @return string
     */
    protected function parseInlineFilters(string $echoBody): string
    {
        if (!$this->filterSep) {
            return $echoBody;
        }

        $filters = Str::explode($echoBody, $this->filterSep);
        $newExpr = array_shift($filters);

        foreach ($filters as $filter) {
            if ($filter === self::RAW_OUTPUT) {
                continue;
            }

            if (str_contains($filter, ':')) {
                [$filter, $argStr] = explode(':', $filter, 2);
                if (strlen($argStr) > 1 && str_contains($argStr, ',')) {
                    $args = Str::toTypedList($argStr);
                } else {
                    $args = [Str::toTyped($argStr, true)];
                }

                $fmtArgs = [];
                foreach ($args as $arg) {
                    if (is_int($arg) || is_float($arg)) {
                        $fmtArgs[] = $arg;
                    } elseif (is_bool($arg)) {
                        $fmtArgs[] = $arg ? 'true' : 'false';
                    } elseif ($arg[0] === '$') {
                        $fmtArgs[] = $arg;
                    } else {
                        $fmtArgs[] = Str::paramQuotes($arg);
                    }
                }

                $filter  = $this->filterMapping[$filter] ?? $filter . '(';
                $newExpr = sprintf('%s%s, %s)', $filter, $newExpr, implode(',', $fmtArgs));
            } else {
                $filter  = $this->filterMapping[$filter] ?? $filter . '(';
                $newExpr = sprintf('%s%s)', $filter, $newExpr);
            }
        }

        $coreFn = $this->echoFilterFunc;
        if (
            $coreFn
            && $coreFn !== self::RAW_OUTPUT
            && !in_array(self::RAW_OUTPUT, $filters, true)
        ) {
            $newExpr = sprintf('%s((string)%s)', $coreFn, $newExpr);
        }

        return $newExpr;
    }

    /**
     * @param string $name
     * @param string $callExpr
     *
     * @return $this
     */
    public function addFilter(string $name, string $callExpr): self
    {
        $callExpr = str_contains($callExpr, '(') ? $callExpr : $callExpr . '(';

        $this->filterMapping[$name] = $callExpr;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $handler
     *
     * @return static
     */
    public function addDirective(string $name, callable $handler): static
    {
        $this->customDirectives[$name] = $handler;
        return $this;
    }

    /**
     * @return static
     */
    public function disableEchoFilter(): static
    {
        $this->echoFilterFunc = self::RAW_OUTPUT;
        return $this;
    }

    /**
     * @param callable $echoFilterFunc
     *
     * @return AbstractCompiler
     */
    public function setEchoFilterFunc(callable $echoFilterFunc): self
    {
        $this->echoFilterFunc = $echoFilterFunc;
        return $this;
    }
}
