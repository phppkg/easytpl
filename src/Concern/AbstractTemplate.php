<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Concern;

use InvalidArgumentException;
use PhpPkg\EasyTpl\Contract\TemplateInterface;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj;
use function dirname;
use function is_file;
use function strpos;

/**
 * Class AbstractTemplate
 *
 * @author inhere
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $globalVars = [];

    /**
     * allow template file ext list. should start with '.'
     *
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    /**
     * manual set template files
     *
     * @var array<string, string>
     */
    public array $tplFiles = [];

    /**
     * template files dir.
     *
     * @var string
     */
    public string $tplDir = '';

    /**
     * The cache dir for compiled temp php file.
     *
     * @var string
     */
    public string $tmpDir = '';

    /**
     * custom path resolve
     *
     * @var callable(string): string
     */
    public $pathResolver;

    /**
     * Current rendering tpl file by {@see findTplFile()}
     *
     * @var string
     */
    protected string $curTplFile = '';

    /**
     * @param array{tplDir: string, allowExt: array, globalVars: array} $config
     *
     * @return static
     */
    public static function new(array $config = []): static
    {
        return new static($config);
    }

    /**
     * Class constructor.
     *
     * @param array{tplDir: string, allowExt: array, globalVars: array} $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @param callable $fn
     *
     * @return $this
     */
    public function configThis(callable $fn): static
    {
        $fn($this);
        return $this;
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     */
    public function display(string $tplFile, array $tplVars = []): void
    {
        echo $this->render($tplFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     */
    public function displayFile(string $tplFile, array $tplVars = []): void
    {
        echo $this->renderFile($tplFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function render(string $tplFile, array $tplVars = []): string
    {
        return $this->renderFile($tplFile, $tplVars);
    }

    /**
     * @param string $tplName
     *
     * @return string
     */
    protected function findTplFile(string $tplName): string
    {
        $tplName = $this->resolvePath($tplName);
        if (is_file($tplName)) {
            return $tplName;
        }

        if (isset($this->tplFiles[$tplName])) {
            return $this->tplFiles[$tplName];
        }

        $suffix  = '';
        if (strpos($tplName, '.') > 0) {
            $suffix = File::getExtension($tplName);
        }

        // check is an exists file on tplDir
        if ($this->tplDir) {
            $tplFile = $this->resolvePath($this->tplDir) . '/' . $tplName;
            if ($tplFile = $this->tryExistFile($tplFile, $suffix)) {
                return $tplFile;
            }
        }

        // try relative the parent tpl file dir.
        if ($this->curTplFile) {
            $tplFile = dirname($this->curTplFile) . '/' . $tplName;
            if ($tplFile = $this->tryExistFile($tplFile, $suffix)) {
                return $tplFile;
            }
        }

        throw new InvalidArgumentException("no such template file: $tplName");
    }

    /**
     * @param string $tplFile
     * @param string $suffix
     *
     * @return string
     */
    protected function tryExistFile(string $tplFile, string $suffix): string
    {
        if ($suffix) {
            if (is_file($tplFile)) {
                return $tplFile;
            }
        } else {
            foreach ($this->allowExt as $ext) {
                $filename = $tplFile . $ext;
                if (is_file($filename)) {
                    return $filename;
                }
            }
        }

        return ''; // not exist
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    public function resolvePath(string $filePath): string
    {
        if ($fn = $this->pathResolver) {
            return $fn($filePath);
        }

        return $filePath;
    }

    /**
     * @param string $tplName
     * @param string $filePath
     */
    public function addTplFile(string $tplName, string $filePath): void
    {
        $this->tplFiles[$tplName] = $filePath;
    }

    /**
     * @return array
     */
    public function getTplFiles(): array
    {
        return $this->tplFiles;
    }

    /**
     * @param array $tplFiles
     */
    public function setTplFiles(array $tplFiles): void
    {
        $this->tplFiles = $tplFiles;
    }

    /**
     * @return string
     */
    public function getTplDir(): string
    {
        return $this->tplDir;
    }

    /**
     * @param string $tplDir
     */
    public function setTplDir(string $tplDir): void
    {
        $this->tplDir = $tplDir;
    }

    /**
     * @param string $tmpDir
     */
    public function setTmpDir(string $tmpDir): void
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * @return string[]
     */
    public function getAllowExt(): array
    {
        return $this->allowExt;
    }

    /**
     * @param string[] $allowExt
     */
    public function addAllowExt(array $allowExt): void
    {
        foreach ($allowExt as $ext) {
            $this->allowExt[] = $ext;
        }
    }

    /**
     * @param string[] $allowExt
     */
    public function setAllowExt(array $allowExt): void
    {
        $this->allowExt = $allowExt;
    }

    /**
     * @return array
     */
    public function getGlobalVars(): array
    {
        return $this->globalVars;
    }

    /**
     * @param array $globalVars
     */
    public function setGlobalVars(array $globalVars): void
    {
        $this->globalVars = $globalVars;
    }

    /**
     * @param callable $pathResolver
     *
     * @return AbstractTemplate
     */
    public function setPathResolver(callable $pathResolver): static
    {
        $this->pathResolver = $pathResolver;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurTplFile(): string
    {
        return $this->curTplFile;
    }
}
