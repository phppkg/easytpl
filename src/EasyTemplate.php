<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl;

use PhpPkg\EasyTpl\Compiler\PregCompiler;
use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\Contract\EasyTemplateInterface;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use function is_string;

/**
 * Class EasyTemplate
 *
 * @author inhere
 */
class EasyTemplate extends PhpTemplate implements EasyTemplateInterface
{
    /**
     * @var string[]
     */
    protected array $allowExt = ['.html', '.phtml', '.php', '.tpl'];

    /**
     * @var CompilerInterface
     */
    private CompilerInterface $compiler;

    /**
     * custom filter for handle result(only for echo body).
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
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->compiler = new PregCompiler();
        $this->compiler->addDirective(
            'include',
            function (string $body, string $name) {
                /** will call {@see include()} */
                return '$this->' . $name . $body;
            }
        );
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars): string
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
    public function renderString(string $tplCode, array $tplVars): string
    {
        $tplCode = $this->compiler->compile($tplCode);

        return parent::renderString($tplCode, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     */
    protected function include(string $tplFile, array $tplVars = []): void
    {
        $phpFile = $this->compileFile($tplFile);

        echo $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    protected function renderInclude(string $tplFile, array $tplVars): string
    {
        $phpFile = $this->compileFile($tplFile);

        return $this->doRenderFile($phpFile, $tplVars);
    }

    /**
     * @param string $tplFile
     *
     * @return string
     */
    public function compileFile(string $tplFile): string
    {
        $tplFile = $this->findTplFile($tplFile);

        // compile contents
        $phpCode = $this->compiler->compileFile($tplFile);

        // generate temp php file
        return $this->genTmpPhpFile($phpCode, File::getName($tplFile, true));
    }

    /**
     * compile contents
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
     * Apply added filter by name.
     *
     * @param string $filter
     * @param string $result
     * @param mixed ...$args
     *
     * @return string
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
     * @return $this
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $name => $filterFn) {
            $this->addFilter($name, $filterFn);
        }

        return $this;
    }

    /**
     * @param string $name The filter name
     * @param callable $filterFn
     *
     * @return $this
     */
    public function addFilter(string $name, callable $filterFn): self
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
     * @return $this
     */
    public function addDirective(string $name, callable $handler): self
    {
        $this->compiler->addDirective($name, $handler);
        return $this;
    }

    /**
     * @return $this
     */
    public function disableEchoFilter(): self
    {
        $this->compiler->disableEchoFilter();
        return $this;
    }

    /**
     * @param string $filterSep
     *
     * @return $this
     */
    public function setFilterSep(string $filterSep): self
    {
        $this->compiler->filterSep = $filterSep;
        return $this;
    }

    /**
     * @param callable $name
     *
     * @return $this
     */
    public function setEchoFilterFunc(callable $name): self
    {
        $this->compiler->setEchoFilterFunc($name);
        return $this;
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        $this->getCompiler()->setOpenCloseTag($open, $close);
        return $this;
    }

    /**
     * @param CompilerInterface $compiler
     *
     * @return EasyTemplate
     */
    public function setCompiler(CompilerInterface $compiler): self
    {
        $this->compiler = $compiler;
        return $this;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler(): CompilerInterface
    {
        return $this->compiler;
    }
}
