<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Compiler;

use function implode;
use function in_array;

/**
 * class Token
 *
 * @author inhere
 */
class Token
{
    // define statement
    public const T_DEFINE = 'define';

    //----- directive tokens -----

    // echo statement
    public const T_ECHO = 'echo';

    //----- control block tokens -----

    // if statement
    public const T_IF     = 'if';

    public const T_ELSEIF = 'elseif';

    public const T_ELSE   = 'else';

    public const T_ENDIF  = 'endif';

    // for statement
    public const T_FOR    = 'for';

    public const T_ENDFOR = 'endfor';

    // foreach statement
    public const T_FOREACH    = 'foreach';

    public const T_ENDFOREACH = 'endforeach';

    // switch statement
    public const T_SWITCH    = 'switch';

    public const T_CASE      = 'case';

    public const T_DEFAULT   = 'default';

    public const T_ENDSWITCH = 'endswitch';

    // special keywords
    public const T_BREAK    = 'break';

    public const T_CONTINUE = 'continue';

    public const ALONE_TOKENS = [
        self::T_ELSE,
        self::T_BREAK,
        self::T_CONTINUE,
        self::T_DEFAULT,
        self::T_ENDIF,
        self::T_ENDFOR,
        self::T_ENDFOREACH,
        self::T_ENDSWITCH,
    ];

    // control block keywords
    public const BLOCK_NAMES = [
        // special keywords
        self::T_BREAK,
        self::T_CONTINUE,
        // switch
        self::T_SWITCH,
        self::T_CASE,
        self::T_DEFAULT,
        self::T_ENDSWITCH,
        // for, foreach
        self::T_FOREACH,
        self::T_ENDFOREACH,
        self::T_FOR,
        self::T_ENDFOR,
        // if-else
        self::T_IF,
        self::T_ELSEIF,
        self::T_ELSE,
        self::T_ENDIF,
    ];

    /**
     * will auto add char `:` on statement end.
     */
    public const CAN_FIX_TOKENS = [
        self::T_IF,
        self::T_ELSEIF,
        self::T_ELSE,
        self::T_FOR,
        self::T_FOREACH,
        self::T_CASE,
        self::T_SWITCH
    ];

    /**
     * @return string
     */
    public static function getBlockNamePattern(): string
    {
        // ~^(if|elseif|else|endif|for|endfor|foreach|endforeach)~
        return self::buildDirectivePattern(self::BLOCK_NAMES);
    }

    /**
     * @param string[] $names
     *
     * @return string
     */
    public static function buildDirectivePattern(array $names): string
    {
        if (!$names) {
            return '';
        }

        return '~^(' . implode('|', $names) . ')[^\w-]~';
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public static function tryAloneToken(string $str): string
    {
        return in_array($str, self::ALONE_TOKENS, true) ? $str : '';
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isAloneToken(string $type): bool
    {
        return in_array($type, self::ALONE_TOKENS, true);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function canAutoFixed(string $type): bool
    {
        return in_array($type, self::CAN_FIX_TOKENS, true);
    }
}
