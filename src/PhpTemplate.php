<?php declare(strict_types=1);
/**
 * This file is part of phppkg/easytpl.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace PhpPkg\EasyTpl;

use InvalidArgumentException;
use PhpPkg\EasyTpl\Concern\AbstractTemplate;
use RuntimeException;
use Throwable;
use Toolkit\FsUtil\Dir;
use Toolkit\Stdlib\OS;
use function array_merge;
use function date;
use function dirname;
use function extract;
use function file_exists;
use function file_put_contents;
use function md5;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function sprintf;
use const EXTR_OVERWRITE;

/**
 * Class PhpTemplate
 *
 * @author inhere
 */
class PhpTemplate extends AbstractTemplate
{
    /**
     * The cache dir for generated temp php file
     *
     * @var string
     */
    public string $tmpDir = '';

    /**
     * The current auto generated temp php file
     *
     * @var string
     */
    private string $tmpPhpFile = '';

    /**
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars = []): string
    {
        $tempFile = $this->genTmpPhpFile($tplCode);

        return $this->doRenderFile($tempFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars = []): string
    {
        $this->curTplFile = $tplFile = $this->findTplFile($tplFile);

        return $this->doRenderFile($tplFile, $tplVars);
    }

    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    protected function doRenderFile(string $tplFile, array $tplVars): string
    {
        if (!file_exists($tplFile)) {
            throw new InvalidArgumentException('no such template file:' . $tplFile);
        }

        if ($this->globalVars) {
            $tplVars = array_merge($this->globalVars, $tplVars);
        }

        ob_start();
        extract($tplVars, EXTR_OVERWRITE);
        try {
            require $tplFile;
            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $hashId template contents hashID
     * @param string $prefix
     *
     * @return string
     */
    protected function tmpFilepath(string $hashId, string $prefix = ''): string
    {
        $prefix  = $prefix ?: 'phpTpl';
        $tmpDir  = $this->tmpDir ?: OS::getTempDir() . '/php-tpl-code';
        $tmpFile = sprintf('%s/%s_%s.php', $tmpDir, $prefix, $hashId);

        $this->tmpPhpFile = $tmpFile;
        return $tmpFile;
    }

    /**
     * Generate tmp php template file
     *
     * @param string $phpCode
     * @param string $tmpFile
     *
     * @return string
     */
    protected function genTmpPhpFile(string $phpCode, string $tmpFile = ''): string
    {
        if (!$tmpFile) {
            $tmpFile = $this->tmpFilepath(md5($phpCode));
        }

        if (!file_exists($tmpFile)) {
            Dir::create(dirname($tmpFile));

            $date = date('Y/m/d H:i:s');
            $num = file_put_contents($tmpFile, $phpCode . "\n<?php // generated on $date ?>");
            if ($num < 1) {
                throw new RuntimeException('write contents to tmp file failed');
            }
        }

        return $tmpFile;
    }

    /**
     * @return string
     */
    public function getTmpPhpFile(): string
    {
        return $this->tmpPhpFile;
    }
}
