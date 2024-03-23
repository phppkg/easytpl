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
use PhpPkg\EasyTpl\Concern\AbstractTemplate;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Std\PipeFilters;
use Toolkit\Stdlib\Str;
use function array_merge;
use function explode;
use function file_get_contents;
use function is_array;
use function preg_quote;
use function preg_replace_callback;
use function sprintf;
use function str_contains;
use function trim;

/**
 * Class SimpleTemplate
 *
 * @author inhere
 * @package PhpPkg\EasyTpl
 */
class SimpleTemplate extends AbstractTemplate
{
    /**
     * template var format
     *
     * @var string
     */
    protected string $format = '{{%s}}';

    /**
     * var left chars. auto parsed from $format
     *
     * @var string
     */
    private string $varStart = '{{';

    /**
     * regex pattern, build from $format
     *
     * @var string
     */
    private string $pattern = '';

    /**
     * @var PipeFilters|null
     */
    private ?PipeFilters $pipeFilter = null;

    /**
     * @return void
     */
    protected function afterInit(): void
    {
        $this->pipeFilter = PipeFilters::newWithDefaultFilters();

        // add built in filters
        $this->pipeFilter->addFilters([
            'nl' => function ($val) {
                return $val . "\n";
            }
        ]);
    }

    /**
     * @param string $tplFile
     * @param array $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        $tplFile = $this->curTplFile = $this->findTplFile($tplFile);

        return $this->renderString(file_get_contents($tplFile), $tplVars);
    }

    /**
     * @param string $tplCode
     * @param array $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        if (!str_contains($tplCode, $this->varStart)) {
            return $tplCode;
        }

        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        return $this->renderVars($tplCode, $tplVars);
    }

    /**
     * @param string $tplCode
     * @param array $vars
     *
     * @return string
     */
    protected function renderVars(string $tplCode, array $vars): string
    {
        if (!$this->pattern) {
            $this->parseFormat($this->format);
        }

        return preg_replace_callback($this->pattern, function (array $match) use ($vars) {
            $var = trim($match[1]);
            if (!$var) {
                return $match[0];
            }

            $filters = '';
            if (str_contains($var, '|')) {
                [$var, $filters] = Str::explode($var, '|', 2);
            }

            $value = Arr::getByPath($vars, $var);
            if ($value !== null) {
                $value = is_array($value) ? Arr::toStringV2($value) : (string)$value;
                return $filters ? $this->pipeFilter->applyStringRules($value, $filters) : $value;
            }

            return $match[0];
        }, $tplCode);
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        if (!str_contains($format, '%s')) {
            throw new InvalidArgumentException('template var format must contains %s');
        }

        $this->format = $format;
        $this->parseFormat($format);
    }

    /**
     * @return PipeFilters
     */
    public function getPipeFilter(): PipeFilters
    {
        return $this->pipeFilter;
    }

    /**
     * @param string $format
     *
     * @return void
     */
    protected function parseFormat(string $format): void
    {
        [$left, $right] = explode('%s', $format);

        $this->varStart = $left;
        $this->pattern  = sprintf('/%s([^\n]+?)%s/', preg_quote($left, '/'), preg_quote($right, '/'));
    }

}
