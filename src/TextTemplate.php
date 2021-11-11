<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl;

/**
 * class TextTemplate
 *
 * @author inhere
 */
class TextTemplate extends EasyTemplate
{
    /**
     * @var string[]
     */
    protected array $allowExt = ['.php', '.tpl'];

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        // use raw echo for text template
        $this->getCompiler()->disableEchoFilter();
    }
}
