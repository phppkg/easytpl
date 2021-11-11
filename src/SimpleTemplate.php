<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl;

use InvalidArgumentException;
use function array_merge;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function str_contains;
use function strtr;

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
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        if (!file_exists($tplFile)) {
            throw new InvalidArgumentException('No such template file:' . $tplFile);
        }

        $tplCode = file_get_contents($tplFile);

        return $this->renderString($tplCode, $tplVars);
    }

    /**
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        $fmtVars = [];
        foreach ($tplVars as $name => $var) {
            $name = sprintf($this->format, (string)$name);
            // add
            $fmtVars[$name] = $var;
        }

        return strtr($tplCode, $fmtVars);
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
            throw new InvalidArgumentException('var format must contains %s');
        }

        $this->format = $format;
    }
}
