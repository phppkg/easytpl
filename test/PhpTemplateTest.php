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

    public function testRenderFile_tplDir():void
    {
        $t = PhpTemplate::new();

        $tplFile = 'gen-go-funcs.php';
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $e = $this->runAndGetException(function (PhpTemplate $t, $tplFile, $tplVars) {
            $t->renderFile($tplFile, $tplVars);
        }, $t, $tplFile, $tplVars);
        $this->assertEquals('no such template file: gen-go-funcs.php', $e->getMessage());

        $t->setTplDir(__DIR__ . '/testdata');
        $result = $t->renderFile($tplFile, $tplVars);
        vdump($result);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('<?', $result);
    }
}
