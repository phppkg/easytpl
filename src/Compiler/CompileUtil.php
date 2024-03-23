<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Compiler;

use function defined;
use function preg_match;
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

    private const ONLY_PATH_PATTERN = '/^\$?[a-zA-Z_][\w.-]+\w$/';

    /**
     * convert access array key path to php array access expression.
     *
     * - convert `$ctx.top.sub` to `$ctx['top']['sub']`
     * - convert `ctx.top.sub` to `$ctx['top']['sub']`
     *
     * @param string $line var line. NOT: line must start with $
     *
     * @return string
     */
    public static function toArrayAccessStmt(string $line): string
    {
        $hasSpace = str_contains($line, ' ');

        // only key path.
        if (!$hasSpace && preg_match(self::ONLY_PATH_PATTERN, $line) === 1) {
            return self::handleMatch($line);
        }

        // with space and key path at first node. like: ctx.top.sub ?? "fallback"
        if ($hasSpace) {
            [$first, $last] = explode(' ', $line, 2);
            if (preg_match(self::ONLY_PATH_PATTERN, $first) === 1 && !str_contains($last, '.')) {
                return self::handleMatch($first) . " $last";
            }
        }

        // with fallback statement. like: $ctx.top.sub ?? "fallback"
        if (!self::$matchKeyPath) {
            self::$matchKeyPath = '~(' . implode(')|(', [
                    '\$[\w.-]+\w', // array key path.
                ]) . ')~';
        }

        // https://www.php.net/manual/zh/reference.pcre.pattern.modifiers.php
        return preg_replace_callback(self::$matchKeyPath, static function (array $matches) use ($line) {
            $varName = $matches[0];
            // convert $ctx.top.sub to $ctx[top][sub]
            if (str_contains($varName, '.')) {
                return self::handleMatch($varName);
            }
            return $varName;
        }, $line);
    }

    private static function handleMatch(string $varName): string
    {
        $nodes = [];
        foreach (explode('.', $varName) as $i => $key) {
            if ($i === 0) {
                $nodes[] = $key[0] === '$' ? $key : '$' . $key;
            } else {
                $nodes[] = is_numeric($key) ? "[$key]" : "['$key']";
            }
        }

        return implode('', $nodes);
    }
}
