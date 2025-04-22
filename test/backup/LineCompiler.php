<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Compiler;

use function explode;
use function implode;
use function preg_split;
use function trim;
use const PREG_SPLIT_NO_EMPTY;

/**
 * class LineCompiler - compile template code to php code TODO
 *
 * @author inhere
 */
class LineCompiler extends AbstractCompiler
{
    /**
     * inside the if/elseif/else/for/foreach
     *
     * @var bool
     */
    private bool $insideIfFor = false;

    /**
     * inside the php tag
     *
     * @var bool
     */
    private bool $insideTag = false;

    /**
     * compile template contents to raw PHP template codes
     *
     * @param string $tplCode
     *
     * @return string
     */
    public function compile(string $tplCode): string
    {
        // Not contains open tag
        if (!str_contains($tplCode, $this->openTag)) {
            return $tplCode;
        }

        $compiled = [];
        foreach (explode("\n", $tplCode) as $line) {
            // empty line
            if (!$line || !trim($line)) {
                $compiled[] = $line;
                continue;
            }

            if (
                !$this->insideTag
                && (!str_contains($line, $this->openTag) || !str_contains($line, $this->closeTag))
            ) {
                $compiled[] = $line;
                continue;
            }

            // parse line
            $compiled[] = $this->analyzeLineChars($line);
        }

        return implode("\n", $compiled);
    }

    /**
     * @param string $line
     *
     * @return string
     */
    public function analyzeLineChars(string $line): string
    {
        $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);

        $prev = $next = 0;
        foreach ($chars as $i => $char) {
        }

        return '';
    }
}
