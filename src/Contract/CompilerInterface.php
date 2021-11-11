<?php declare(strict_types=1);

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
    public function setOpenCloseTag(string $open, string $close): self;

    /**
     * @return $this
     */
    public function disableEchoFilter(): self;

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
