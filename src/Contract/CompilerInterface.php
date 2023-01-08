<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl\Contract;

/**
 * interface CompilerInterface
 *
 * @author inhere
 */
interface CompilerInterface
{
    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): static;

    /**
     * @return $this
     */
    public function disableEchoFilter(): static;

    /**
     * @param string $name Directive name.
     * @param callable $handler
     * @param bool $unwrap Dont wrap PHP tag after handled result.
     *
     * @return static
     */
    public function addDirective(string $name, callable $handler, bool $unwrap = false): static;

    /**
     * compile template contents to raw PHP template codes
     *
     * @param string $tplCode
     *
     * @return string
     */
    public function compile(string $tplCode): string;

    /**
     * compile template file contents to raw PHP template codes
     *
     * @param string $tplFile
     *
     * @return string
     */
    public function compileFile(string $tplFile): string;
}
