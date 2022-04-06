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
 * Interface TemplateInterface
 *
 * @author inhere
 * @package PhpPkg\EasyTpl\Contract
 */
interface TemplateInterface
{
    /**
     * Render template file and output
     *
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return void
     */
    public function render(string $tplFile, array $tplVars): void;

    /**
     * Render template file to string
     *
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string;

    /**
     * Render template text to string
     *
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string;
}
