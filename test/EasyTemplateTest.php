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
    private $tplVars = [
        'int' => 23,
        'str' => 'a string',
        'arr' => [
             'inhere',
             20,
        ],
        'map' => [
            'name' => 'inhere',
            'age'  => 20,
        ],
    ];

    public function testCompileCode_check(): void
    {
        $t2 = new EasyTemplate();

        $compiled = $t2->compileCode('');
        $this->assertEquals('', $compiled);

        $compiled = $t2->compileCode('no tpl tags');
        $this->assertEquals('no tpl tags', $compiled);
    }

    public function testV2RenderFile_use_echo_foreach(): void
    {
        $t = new EasyTemplate();

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
        $t = new EasyTemplate();

        $tplFile = $this->getTestTplFile('testdata/use_all_token.tpl');
        $result = $t->renderFile($tplFile, $this->tplVars);

        $this->assertNotEmpty($result);
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

    public function testAddFilters(): void
    {
        $t = new EasyTemplate();
        $t->addFilters([
            'upper' => 'strtoupper',
            'myFilter' => function (string $str) {
                return substr($str, 3);
            },
        ]);

        $code = '{{ $name | upper }}';
        $out = '<?= strtoupper(htmlspecialchars($name)) ?>';
        $this->assertEquals($out, $t->compileCode($code));

        $code = '{{ $name | myFilter }}';
        $out = <<<'CODE'
<?= $this->applyFilter('myFilter', htmlspecialchars($name)) ?>
CODE;
        $this->assertEquals($out, $t->compileCode($code));

        $code = '{{ $name | upper | myFilter }}';
        $out = <<<'CODE'
<?= $this->applyFilter('myFilter', strtoupper(htmlspecialchars($name))) ?>
CODE;
        $this->assertEquals($out, $t->compileCode($code));
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
        $out = '<?= strtoupper(htmlspecialchars($name)) ?>';
        $this->assertEquals($out, $t->compileCode($code));
    }

    public function testRenderFile_ifElse(): void
    {
        $t = new EasyTemplate();

        $tplFile = $this->getTestTplFile('testdata/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        vdump($tplFile);

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }

    public function testRenderFile_foreach(): void
    {
        $t = new EasyTemplate();

        $tplFile = $this->getTestTplFile('testdata/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }
}
