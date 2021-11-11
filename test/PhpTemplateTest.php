<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PhpPkg\EasyTpl\PhpTemplate;
use function vdump;

/**
 * class TextTemplateTest
 */
class PhpTemplateTest extends BaseTestCase
{
    public function testRenderFile():void
    {
        $t = new PhpTemplate();

        $tplFile = $this->getTestTplFile('testdata/gen-go-funcs.php');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        vdump($result);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('<?', $result);
    }
}
