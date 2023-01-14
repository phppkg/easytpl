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
use Toolkit\Stdlib\Str;
use function array_merge;
use function explode;
use function file_get_contents;
use function str_contains;

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
    private string $varLeft = '{{';

    /**
     * @param string $tplFile
     * @param array  $tplVars
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
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        if (!str_contains($tplCode, $this->varLeft)) {
            return $tplCode;
        }

        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        return Str::renderVars($tplCode, $tplVars, $this->format);
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
        // get left chars
        [$left, ] = explode('%s', $format);
        $this->varLeft = $left;
    }
}
