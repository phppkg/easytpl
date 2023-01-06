<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Compiler;

use function defined;
use function str_contains;
use function str_starts_with;

/**
 * class CompileUtil
 *
 * @author inhere
 * @date 2022/12/30
 */
class CompileUtil
{
    /**
     * will match:
     * - varName
     * - top.subKey
     */
    protected const REGEX_VAR_NAME = '@^[a-zA-Z_][\w.-]*$@';

    /**
     * Check is var output.
     *
     * @param string $line
     *
     * @return bool
     */
    public static function canAddVarPrefix(string $line): bool
    {
        // has prefix or is magic const. (eg: __LINE__)
        if ($line[0] === '$' || str_starts_with($line, '__')) {
            return false;
        }

        if (preg_match(self::REGEX_VAR_NAME, $line) === 1) {
            if (str_contains($line, '.') || str_contains($line, '-')) {
                return true;
            }

            // up: check is const name
            // - defined() cannot check magic const. (eg: __LINE__)
            return !defined($line);
        }

        return false;
    }

    /**
     * @var string|null
     */
    private static ?string $matchKeyPath = null;

    /**
     * convert access array key path to php array access expression.
     *
     * - convert $ctx.top.sub to $ctx['top']['sub']
     *
     * @param string $line var line. must start with $
     *
     * @return string
     */
    public static function toArrayAccessStmt(string $line): string
    {
        if (self::$matchKeyPath === null) {
            // - convert $ctx.top.sub to $ctx['top']['sub']
            self::$matchKeyPath = '~(' . implode(')|(', [
                    '\$[\w.-]+\w', // array key path.
                ]) . ')~';
        }

        // https://www.php.net/manual/zh/reference.pcre.pattern.modifiers.php
        return preg_replace_callback(self::$matchKeyPath, static function (array $matches) {
            $varName = $matches[0];
            // convert $ctx.top.sub to $ctx[top][sub]
            if (str_contains($varName, '.')) {
                $nodes = [];
                foreach (explode('.', $varName) as $key) {
                    if ($key[0] === '$') {
                        $nodes[] = $key;
                    } else {
                        $nodes[] = is_numeric($key) ? "[$key]" : "['$key']";
                    }
                }

                $varName = implode('', $nodes);
            }

            return $varName;
        }, $line);
    }
}
