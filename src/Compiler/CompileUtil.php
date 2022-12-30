<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Compiler;

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
        return $line[0] !== '$' && preg_match(self::REGEX_VAR_NAME, $line) === 1;
    }

    /**
     * convert quick access array key path to php expression.
     *
     * - convert $ctx.top.sub to $ctx['top']['sub']
     *
     * @param string $line
     *
     * @return string
     */
    public static function pathToArrayAccess(string $line): string
    {
        // - convert $ctx.top.sub to $ctx['top']['sub']
        $pattern = '~(' . implode(')|(', [
                '\$[\w.-]+\w', // array key path.
            ]) . ')~';

        // https://www.php.net/manual/zh/reference.pcre.pattern.modifiers.php
        return preg_replace_callback($pattern, static function (array $matches) {
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
