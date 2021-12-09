<?php declare(strict_types=1);

namespace PhpPkg\EasyTpl\Extended;

use PhpPkg\EasyTpl\Contract\CompilerInterface;
use PhpPkg\EasyTpl\EasyTemplate;
use function sprintf;
use function trim;

/**
 * Class ExtendedTemplate TODO
 *
 * @package PhpPkg\EasyTpl
 */
class ExtendedTemplate extends EasyTemplate
{
    public const BLOCK_VAR_PREFIX = 'TPL_BLOCK_';

    private $currentBlock = '';

    protected function init(CompilerInterface $compiler): void
    {
        parent::init($compiler);

        $compiler
            ->addDirective('extend', function (string $tplName) {
                return ExtendLoader::new()->handle($tplName);
            })
            ->addDirective('extends', function () {

            })
            ->addDirective('block', function (string $body) {
                $this->currentBlock = trim($body, '() ');

                return sprintf('$this->startBlock(%s);', $this->currentBlock);
            })
            ->addDirective('endblock', function () {
                return '$this->endBlock();';
            });
    }

    protected function startBlock(string $name): void
    {

    }

    protected function endBlock(): void
    {

    }
}