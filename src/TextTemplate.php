<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl;

/**
 * class TextTemplate
 * - will disable echo filter for default.
 *
 * @author inhere
 */
class TextTemplate
{
    /**
     * @param array $config
     *
     * @return EasyTemplate
     */
    public static function new(array $config = []): EasyTemplate
    {
        return EasyTemplate::newTexted($config);
    }
}
