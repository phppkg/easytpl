<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PhpPkg\EasyTpl\ExtendTemplate;

/**
 * class ExtendTemplateTest
 *
 * @author inhere
 * @date 2022/12/30
 */
class ExtendTemplateTest extends BaseTestCase
{
    private function newTemplate(): ExtendTemplate
    {
        return new ExtendTemplate([
            'tmpDir' => $this->getTestdataPath('extend-caches'),
        ]);
    }

    public function testExtend_renderFile(): void
    {
        $tplFile = $this->getTestdataPath('extend/home.tpl');

        $et  = $this->newTemplate();
        $str = $et->renderFile($tplFile);

        $this->assertStringContainsString('on layout: block header;', $str);
        $this->assertStringContainsString('on home: block body;', $str);
        $this->assertStringContainsString('on home: block footer;', $str);

        $tplFile = $this->getTestdataPath('extend/home1.tpl');

        $str = $et->renderFile($tplFile, $this->tplVars);
        $this->assertStringContainsString('on layout: block header - name: inhere.', $str);
        $this->assertStringContainsString('on home: block body - age: 20.', $str);
        $this->assertStringContainsString('on home: block footer - city: chengdu.', $str);
    }

    public function testExtend_renderString(): void
    {
        $et = $this->newTemplate();

        $tplCode = <<<TPL
{{ block 'first' }}
content int first block.
{{ endblock }}
TPL;

        $str = $et->renderString($tplCode);
        $this->assertEquals("content int first block.\n", $str);

        $tplCode = <<<TPL
{{ block 'first' }}
my name is {{ name }}.
{{ endblock }}
TPL;

        $str = $et->renderString($tplCode, $this->tplVars);
        $this->assertEquals("my name is inhere1.\n", $str);

        // inline
        $tplCode = "{{ block 'first' }}my name is {{ name }}.{{ endblock }}";

        $str = $et->renderString($tplCode, $this->tplVars);
        $this->assertEquals("my name is inhere1.", $str);
    }

    public function testExtend_renderString_error(): void
    {
        $et = $this->newTemplate();

        // not end block
        $ex = $this->runAndGetException(function () use ($et) {
            $tpl1 = <<<TPL
{{ block 'first' }}
{{ block 'second' }}
TPL;
            $et->renderString($tpl1);
        });
        $this->assertEquals("current in the block 'first', cannot start new block 'second'", $ex->getMessage());

        // not start block
        $ex = $this->runAndGetException(function () use ($et) {
            $tpl1 = <<<TPL
{{ endblock }}
TPL;
            $et->renderString($tpl1);
        });
        $this->assertEquals("missing block start statement before call endblock.", $ex->getMessage());

    }
}
