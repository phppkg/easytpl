<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Concern;

use InvalidArgumentException;
use PhpPkg\EasyTpl\Compiler\PregCompiler;
use PhpPkg\EasyTpl\Contract\CompilerInterface;
use Toolkit\FsUtil\File;
use function file_exists;
use function file_get_contents;

/**
 * trait CompiledTemplateTrait
 *
 * @author inhere
 * @date 2023/1/2
 */
trait CompiledTemplateTrait
{
    /**
     * @var CompilerInterface
     */
    protected CompilerInterface $compiler;

    /**
     * Custom filter for handle result(only for echo body).
     *
     * ```php
     * $p->addFilter('upper', 'strtoupper');
     *
     * // or
     * $p->addFilter('upper', function(string $str) {
     *      return strtoupper($str);
     * });
     * ```
     *
     * @var array{string, callable-string|callable(string, mixed): string}
     */
    public array $customFilters = [];

    /**
     * create compiler
     *
     * @param array $config
     *
     * @return CompilerInterface
     */
    protected function createCompiler(array &$config): CompilerInterface
    {
        // custom compiler
        if (isset($config['compiler'])) {
            $customCompiler = $config['compiler'];
            // class-string
            if (is_string($customCompiler)) {
                $customCompiler = new $customCompiler;
            }

            $compiler = $customCompiler;
            unset($config['compiler']);
        } else {
            $compiler = new PregCompiler();
        }

        return $compiler;
    }

    protected function loadBuiltInFilters(): void
    {
        // add built-in filters
        $this->addFilters([
            'upper'  => 'strtoupper',
            'lower'  => 'strtolower',
            'escape' => 'htmlspecialchars',
            'nl'     => function ($str): string {
                return $str . "\n";
            },
        ]);
    }

    /**
     * compile contents to PHP code.
     *
     * @param string $code
     *
     * @return string
     */
    public function compileCode(string $code): string
    {
        return $this->compiler->compile($code);
    }

    /**
     * @param string $tplFile
     *
     * @return string
     */
    public function compileFile(string $tplFile): string
    {
        $tplFile = $this->curTplFile = $this->findTplFile($tplFile);
        $tplCode = file_get_contents($tplFile);
        $tmpFile = $this->tmpFilepath(md5($tplCode), File::getName($tplFile, true));

        // only compile on cached tmp file not exists
        if (!file_exists($tmpFile)) {
            $phpCode = $this->compiler->compile($tplCode);
            $tmpFile = $this->genTmpPhpFile($phpCode, $tmpFile);
        }

        return $tmpFile;
    }

    /**
     * Apply added filter by name.
     *
     * @param string $filter
     * @param string $result
     * @param mixed ...$args
     *
     * @return string
     * @see AddFilter()
     */
    public function applyFilter(string $filter, string $result, ...$args): string
    {
        if (!isset($this->customFilters[$filter])) {
            throw new InvalidArgumentException("Template - apply unregistered filter: $filter");
        }

        $filterFn = $this->customFilters[$filter];
        return $filterFn($result, ...$args);
    }

    /**
     * @param array<string, callable> $filters
     *
     * @return static
     */
    public function addFilters(array $filters): static
    {
        foreach ($filters as $name => $filterFn) {
            $this->addFilter($name, $filterFn);
        }
        return $this;
    }

    /**
     * Add new filter function
     *
     * @param string $name The filter name
     * @param callable $filterFn
     *
     * @return static
     */
    public function addFilter(string $name, callable $filterFn): static
    {
        // if not an string, build call filter expr
        $callExpr = is_string($filterFn) ? $filterFn : "\$this->applyFilter('$name', ";

        $this->customFilters[$name] = $filterFn;
        $this->compiler->addFilter($name, $callExpr);

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
        $this->compiler->addDirective($name, $handler);
        return $this;
    }

    /**
     * @return static
     */
    public function disableEchoFilter(): static
    {
        $this->compiler->disableEchoFilter();
        return $this;
    }

    /**
     * @param string $filterSep
     *
     * @return static
     */
    public function setFilterSep(string $filterSep): static
    {
        $this->compiler->filterSep = $filterSep;
        return $this;
    }

    /**
     * @param callable $name
     *
     * @return static
     */
    public function setEchoFilterFunc(callable $name): static
    {
        $this->compiler->setEchoFilterFunc($name);
        return $this;
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return static
     */
    public function setOpenCloseTag(string $open, string $close): static
    {
        $this->getCompiler()->setOpenCloseTag($open, $close);
        return $this;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler(): CompilerInterface
    {
        return $this->compiler;
    }

    /**
     * @param CompilerInterface $compiler
     */
    protected function setCompiler(CompilerInterface $compiler): void
    {
        $this->compiler = $compiler;
    }
}
