<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Compiler;

use function addslashes;
use function in_array;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function str_contains;
use function strlen;
use function substr;
use function trim;

/**
 * class PregCompiler - compile template code to php code
 *
 * @author inhere
 */
class PregCompiler extends AbstractCompiler
{
    // add slashes tag name
    protected string $openTagE = '\{\{';

    protected string $closeTagE = '\}\}';

    /**
     * like :
     *  ~^(break|continue|switch|case|default|endswitch|foreach|endforeach|for|endfor|if|elseif|else|endif)[^\w-]~
     *
     * @var string
     */
    protected string $blockPattern = '';

    protected string $directivePattern = '';

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): static
    {
        parent::setOpenCloseTag($open, $close);

        $this->openTagE  = addslashes($open);
        $this->closeTagE = addslashes($close);

        return $this;
    }

    /**
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

        $openTagE  = $this->openTagE;
        $closeTagE = $this->closeTagE;

        // comments block. `{{# comments #}}`
        if (str_contains($tplCode, "$this->openTag#")) {
            $tplCode = preg_replace("~$openTagE#.+?#$closeTagE~s", '', $tplCode);
        }

        $this->buildMatchPatterns();

        $flags = 0;
        // $flags = PREG_OFFSET_CAPTURE;
        // $flags = PREG_PATTERN_ORDER | PREG_SET_ORDER;

        // TIP: `.+` -> `.+?`
        // `?` - 非贪婪匹配; 若不加，会导致有多个相同标签时，第一个开始会匹配到最后一个的关闭
        return preg_replace_callback(
            "~$openTagE\s*(.+?)$closeTagE~s", // Amixu, iu, s
            function (array $matches) {
                // empty line, keep it.
                if (!$trimmed = trim($matches[1])) {
                    return $matches[1];
                }

                return $this->parseCodeBlock($trimmed);
            },
            $tplCode,
            -1,
            $count,
            $flags
        );
    }

    /**
     * build match patterns
     *
     * @return void
     */
    protected function buildMatchPatterns(): void
    {
        $this->blockPattern = Token::getBlockNamePattern();

        $this->directivePattern = Token::buildDirectivePattern($this->directiveNames);
    }

    /**
     * parse code block string.
     *
     * ### code blocks
     *
     * control stmt block
     *
     * - '=': echo
     * - 'if'
     * - 'for'
     * - 'foreach'
     * - 'switch'
     * - ... more php keywords
     *
     * ### directives
     *
     * custom add added directives
     *
     * - 'include'
     * - 'block'
     *
     * @param string $trimmed trimmed block string.
     *
     * @return string
     */
    public function parseCodeBlock(string $trimmed): string
    {
        // special '}' -  end char for `if, for, foreach, switch`
        if ($trimmed === '}') {
            return self::PHP_TAG_OPEN . ' } ' . self::PHP_TAG_CLOSE;
        }

        $directive = '';
        $unwrapRet = false;
        $isInline  = !str_contains($trimmed, "\n");

        // default is define statement.
        $type  = Token::T_DEFINE;
        $open  = self::PHP_TAG_OPEN . ($isInline ? ' ' : "\n");
        $close = ($isInline ? ' ' : "\n") . self::PHP_TAG_CLOSE;

        // echo statement
        $endChar = '';
        // 'echo keywords' in echo statement
        $eKws = '';
        if ($trimmed[0] === '=') {
            $eKws = '=';
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO;
        } elseif (str_starts_with($trimmed, 'echo ')) {
            // echo statement
            $eKws = 'echo ';
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_OPEN . ' ';
        } elseif ($isInline && ($tryType = Token::tryAloneToken($trimmed))) {
            // special alone token: break, default, continue, endXX
            $type = $tryType;
            $open = self::PHP_TAG_OPEN . ' ';

            // auto append end char ':'
            if ($type === Token::T_ELSE) {
                $close = ': ' . self::PHP_TAG_CLOSE;
            }

            // TIP: $trimmed . ' ' - always end with an [^\w-] char.
        } elseif (preg_match($this->blockPattern, $trimmed . ' ', $matches)) {
            // control block: if, for, foreach, define vars, etc
            $type = $matches[1];
            $open = self::PHP_TAG_OPEN . ' ';

            // auto fix pad some chars.
            if (Token::canAutoFixed($type)) {
                $endChar = $trimmed[strlen($trimmed) - 1];

                // not in raw php code AND end char != :
                if ($endChar !== '}' && $endChar !== '{' && $endChar !== ';' && $endChar !== ':') {
                    $close = ': ' . self::PHP_TAG_CLOSE;
                }
            }

            // TIP: $trimmed . ' ' - always end with an [^\w-] char.
        } elseif ($this->directivePattern && preg_match($this->directivePattern, $trimmed . ' ', $matches)) {
            // support user add special directives.
            $directive = $type = $matches[1];
            $handlerFn = $this->customDirectives[$directive];
            $unwrapRet = in_array($directive, $this->unwrapDirectives, true);

            $trimmed = $handlerFn(substr($trimmed, strlen($directive)), $directive);
        } elseif ($isInline && !str_contains($trimmed, '=')) {
            // inline and not define expr, default as echo expr.
            $type = Token::T_ECHO;
            $open = self::PHP_TAG_ECHO1 . ' ';
        }

        // not need continue handle
        if ($directive || Token::isAloneToken($type)) {
            return $unwrapRet ? $trimmed : $open . $trimmed . $close;
        }

        // inline echo support filters
        if ($isInline && $type === Token::T_ECHO) {
            $endChar = $endChar ?: $trimmed[strlen($trimmed) - 1];

            // with filters
            if ($endChar !== ';' && str_contains($trimmed, $this->filterSep)) {
                $echoBody = substr($trimmed, strlen($eKws));
                $trimmed  = $eKws . $this->parseInlineFilters($echoBody);
            } elseif (CompileUtil::canAddVarPrefix($trimmed)) {
                // auto append var prefix: $
                $trimmed = self::VAR_PREFIX . $trimmed;
            }
        }

        // handle quick access array key.
        // - convert $ctx.top.sub to $ctx['top']['sub']
        $trimmed = CompileUtil::toArrayAccessStmt($trimmed);

        return $open . $trimmed . $close;
    }

}
