<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PhpPkg\EasyTpl\TextTemplate;

/**
 * class TextTemplateTest
 *
 * @author inhere
 */
class TextTemplateTest extends BaseTestCase
{
    public function testCompileCode_noEchoFilter(): void
    {
        $t = TextTemplate::new();

        $code = '{{ $name | upper }}';
        $out = '<?= strtoupper($name) ?>';
        $this->assertEquals($out, $t->compileCode($code));
        $this->assertEquals('INHERE', $t->renderString($code, [
            'name' => 'inhere',
        ]));
    }
}
