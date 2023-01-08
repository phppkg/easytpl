<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PhpPkg\EasyTpl\EasyTemplate;
use Toolkit\FsUtil\File;
use function substr;
use function vdump;

/**
 * class EasyTemplateTest
 */
class EasyTemplateTest extends BaseTestCase
{
    private function newTemplate(): EasyTemplate
    {
        return new EasyTemplate([
            'tmpDir' => $this->getTestdataPath('easy-caches'),
            'tplDir' => __DIR__ . '/testdata/easy',
        ]);
    }

    public function testCompileCode_check(): void
    {
        $t2 = $this->newTemplate();

        $compiled = $t2->compileCode('');
        $this->assertEquals('', $compiled);

        $compiled = $t2->compileCode('no tpl tags');
        $this->assertEquals('no tpl tags', $compiled);
    }

    public function testV2RenderFile_use_echo_foreach(): void
    {
        $t = $this->newTemplate();

        $tplFile = $this->getTestTplFile('testdata/use_echo_foreach.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }

    public function testCompileFile_use_echo_foreach(): void
    {
        $t = new EasyTemplate();

        $tplFile = $this->getTestTplFile('testdata/use_echo_foreach.tpl');
        $phpFile = $t->compileFile($tplFile);

        $this->assertNotEmpty($phpFile);

        $genCode = File::readAll($phpFile);

        $this->assertStringContainsString('<?php', $genCode);
        $this->assertStringContainsString('<?=', $genCode);
        $this->assertStringNotContainsString('{{', $genCode);
        $this->assertStringNotContainsString('}}', $genCode);
        // vdump($genCode);
    }

    public function testCompileFile_use_all_token(): void
    {
        $t = new EasyTemplate();

        $tplFile = $this->getTestTplFile('testdata/use_all_token.tpl');
        $phpFile = $t->compileFile($tplFile);

        $this->assertNotEmpty($phpFile);

        $genCode = File::readAll($phpFile);
        vdump($genCode);

        $this->assertStringContainsString('<?php', $genCode);
        $this->assertStringContainsString('<?=', $genCode);
        $this->assertStringNotContainsString('{{', $genCode);
        $this->assertStringNotContainsString('}}', $genCode);
    }

    public function testRenderFile_use_all_token(): void
    {
        $t = $this->newTemplate();

        $tplFile = $this->getTestTplFile('testdata/use_all_token.tpl');
        $result = $t->renderFile($tplFile, $this->tplVars);

        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('{{', $result);
        vdump($result);
    }

    public function testRender_array_value_use_keyPath(): void
    {
        $t = new EasyTemplate();
        // inline
        $code = '
{{= $ctx.pkgName ?? "org.example.entity" }}
';

        $tplVars = ['ctx' => ['pkgName' => 'MyPKG']];
        $result = $t->renderString($code, $tplVars);
        // vdump($result);
        $this->assertEquals("\nMyPKG\n", $result);
    }

    public function testPhpFuncAsFilter_compile_render(): void
    {
        $t = new EasyTemplate();

        $code = '{{ 34.5 | ceil }}';
        $this->assertEquals('<?= htmlspecialchars((string)ceil(34.5)) ?>', $t->compileCode($code));
        $this->assertEquals('35', $t->renderString($code));
    }

    public function testAddFilters_compile_render(): void
    {
        $t = new EasyTemplate();
        $t->addFilters([
            'upper' => 'strtoupper',
            'myFilter' => function (string $str) {
                return substr($str, 3);
            },
        ]);

        $code = '{{ $name | upper }}';
        $out = '<?= htmlspecialchars((string)strtoupper($name)) ?>';
        $this->assertEquals($out, $t->compileCode($code));
        $this->assertEquals('INHERE', $t->renderString($code, [
            'name' => 'inhere',
        ]));

        $code = '{{ $name | myFilter }}';
        $out = <<<'CODE'
<?= htmlspecialchars((string)$this->applyFilter('myFilter', $name)) ?>
CODE;
        $this->assertEquals($out, $t->compileCode($code));
        $this->assertEquals('ere', $t->renderString($code, [
            'name' => 'inhere',
        ]));

        $code = '{{ $name | upper | myFilter }}';
        $out = <<<'CODE'
<?= htmlspecialchars((string)$this->applyFilter('myFilter', strtoupper($name))) ?>
CODE;
        $this->assertEquals($out, $t->compileCode($code));
        $this->assertEquals('ERE', $t->renderString($code, [
            'name' => 'inhere',
        ]));
    }

    public function testAddFilter_setFilterSep(): void
    {
        $t = new EasyTemplate();
        $t->addFilter('upper', 'strtoupper');
        $t->setFilterSep(' | ');

        // bad
        $code = '{{ $name |upper }}';
        $out = '<?= $name |upper ?>';
        $this->assertEquals($out, $t->compileCode($code));

        // goods
        $code = '{{ $name | upper }}';
        $out = '<?= htmlspecialchars((string)strtoupper($name)) ?>';
        $this->assertEquals($out, $t->compileCode($code));
    }

    public function testRender_define(): void
    {
        $t = new EasyTemplate();

        $tplCode = <<<'CODE'
{{
  $name = 'inhere';
}}
{{ $name | upper }}
CODE;
        $result = $t->renderString($tplCode);
        $this->assertNotEmpty($result);
        $this->assertEquals('INHERE', $result);
    }

    public function testRenderFile_ifElse(): void
    {
        $t = $this->newTemplate();

        $tplFile = $this->getTestTplFile('testdata/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }

    public function testRender_foreach(): void
    {
        $t = $this->newTemplate();

        $tplCode = <<<'CODE'
My name is {{ $name | strtoupper }},
My develop tags:
{{ foreach($tags as $tag) }}
- {{ $tag }}

{{ endforeach }}
CODE;

        $result = $t->renderString($tplCode, [
            'name' => 'inhere',
            'tags' => ['php', 'go', 'java'],
        ]);
        $this->assertNotEmpty($result);
        $this->assertEquals('My name is INHERE,
My develop tags:
- php
- go
- java
', $result);

        vdump($result);

        // render file
        $tplFile = $this->getTestTplFile('testdata/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }

    public function testRender_include_file(): void
    {
        $t = $this->newTemplate();

        $result = $t->renderFile('home', ['name' => 'inhere']);
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString('include', $result);
        $this->assertStringContainsString('inhere', $result);
        $this->assertStringContainsString('Title: Use Include Example', $result);
        // vdump($result);
    }

    public function testEasy_textTemplate(): void
    {
        $t = EasyTemplate::textTemplate();

        $code = '{{ $name | upper }}';
        $out = '<?= strtoupper($name) ?>';
        $this->assertEquals($out, $t->compileCode($code));
        $this->assertEquals('INHERE', $t->renderString($code, [
            'name' => 'inhere',
        ]));
    }

    public function testEasy_useLayout01(): void
    {
        $tplFile = $this->getTestdataPath('easy/home01.tpl');

        $t = $this->newTemplate();
        $s = $t->render($tplFile);
        vdump($s);
        $this->assertNotEmpty($s);
    }

    public function testEasy_useLayout02(): void
    {
        $tplFile = $this->getTestdataPath('easy/home02.tpl');

        $t = $this->newTemplate();
        $s = $t->render($tplFile, $this->tplVars);
        $this->assertNotEmpty($s);
    }
}
