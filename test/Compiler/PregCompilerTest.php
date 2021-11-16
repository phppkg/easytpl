<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest\Compiler;

use PhpPkg\EasyTpl\Compiler\PregCompiler;
use PhpPkg\EasyTpl\Compiler\Token;
use PhpPkg\EasyTplTest\BaseTestCase;
use function preg_match;

/**
 * class PregCompilerTest
 *
 * @author inhere
 */
class PregCompilerTest extends BaseTestCase
{
    public function testCompile_empty_noTag():void
    {
        $p = new PregCompiler();

        $compiled = $p->compile('');
        $this->assertEquals('', $compiled);

        $compiled = $p->compile('no tpl tags');
        $this->assertEquals('no tpl tags', $compiled);
    }

    public function testToken_getBlockNamePattern():void
    {
        $tests = [
            // if
            ['if ', 'if'],
            ['if(', 'if'],
            // - error
            ['if', ''],
            ['if3', ''],
            ['ifa', ''],
            ['ifA', ''],
            ['if_', ''],
            ['if-', ''],
            // foreach
            ['foreach ', 'foreach'],
            ['foreach(', 'foreach'],
            // - error
            ['foreach', ''],
            ['foreachA', ''],
            // special
            ['break ', 'break'],
            ['default ', Token::T_DEFAULT],
            ['continue ', Token::T_CONTINUE],
            // - error
            ['break', ''],
            ['default', ''],
            ['continue', ''],
        ];

        $pattern = Token::getBlockNamePattern();
        foreach ($tests as [$in, $out]) {
            $ret = preg_match($pattern, $in, $matches);
            if ($out) {
                $this->assertEquals(1, $ret);
                $this->assertEquals($out, $matches[1]);
            } else {
                $this->assertEquals(0, $ret);
            }
        }
    }

    public function testCompileCode_inline_echo():void
    {
        $p = new PregCompiler();

        $tests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            ['{{ $name }}', '<?= $name ?>'],
            ['{{ $name; }}', '<?= $name; ?>'],
            ['{{ $name ?: "inhere" }}', '<?= $name ?: "inhere" ?>'],
            ['{{ $name ?? "inhere" }}', '<?= $name ?? "inhere" ?>'],
            ['{{ $name ?? "inhere"; }}', '<?= $name ?? "inhere"; ?>'],
            ['{{ some_func() }}', '<?= some_func() ?>'],
            ['{{ some_func(); }}', '<?= some_func(); ?>'],
            ['{{ $this->include("header.tpl") }}', '<?= $this->include("header.tpl") ?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }

        $tplCode = <<<'TPL'

{{= $ctx.pkgName ?? "org.example.entity" }}

TPL;
        $compiled = $p->compile($tplCode);
        // vdump($tplCode, $compiled);
        $this->assertNotEmpty($compiled);
        $this->assertEquals(<<<'CODE'

<?= $ctx['pkgName'] ?? "org.example.entity" ?>

CODE
            ,$compiled);

        $tplCode = <<<'TPL'
{{= $ctx->pkgName ?? "org.example.entity" }}
TPL;
        $compiled = $p->compile($tplCode);
        // vdump($tplCode, $compiled);
        $this->assertNotEmpty($compiled);
        $this->assertEquals(<<<'CODE'
<?= $ctx->pkgName ?? "org.example.entity" ?>
CODE
            ,$compiled);
    }

    public function testCompile_inline_echo_with_filters():void
    {
        $p = new PregCompiler();

        $tests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            ['{{ $name | ucfirst }}', '<?= htmlspecialchars((string)ucfirst($name)) ?>'],
            [
                '{{ $name ?: "inhere" | substr:0,3 }}',
                '<?= htmlspecialchars((string)substr($name ?: "inhere", 0,3)) ?>'
            ],
            ['{{ some_func() | raw }}', '<?= some_func() ?>'],
            ['{{ $this->include("header.tpl") }}', '<?= $this->include("header.tpl") ?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_disableEchoFilter():void
    {
        $p = new PregCompiler();
        $p->disableEchoFilter();

        $tests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            ['{{ $name | ucfirst }}', '<?= ucfirst($name) ?>'],
            [
                '{{ $name ?: "inhere" | substr:0,3 }}',
                '<?= substr($name ?: "inhere", 0,3) ?>'
            ],
            [
                '{{ $name | append:, }}',
                "<?= append(\$name, ',') ?>"
            ],
            ['{{ some_func() | raw }}', '<?= some_func() ?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_comments():void
    {
        $p = new PregCompiler();
        $p->disableEchoFilter();

        $tests = [
            ['{{# echo "ab" #}}', ''],
            ['{{# 
         multi
         line
        comments     #}}hi', 'hi'],
            [
                '{{# comments #}}{{ $name ?: "inhere" | substr:0,3 }}',
                '<?= substr($name ?: "inhere", 0,3) ?>'
            ],
            // invalid
            ['{{# echo "ab" # }}', '<?= # echo "ab" # ?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_customDirective():void
    {
        $p = new PregCompiler();
        $p->addDirective('include', function (string $body, string $name) {
            return '$this->' . $name . $body;
        });

        $tests = [
            ['{{ include("header.tpl") }}', '<?php $this->include("header.tpl") ?>'],
            ['{{ include("header.tpl", [
   "key1" => "value1",
]) }}', '<?php
$this->include("header.tpl", [
   "key1" => "value1",
])
?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_if_block():void
    {
        $p = new PregCompiler();

        $tests = [
            [
                '{{if ($a < 4) }} hi {{endif}}',
                '<?php if ($a < 4): ?> hi <?php endif ?>',
            ],
            [
                '{{if ($a < 4) { }} hi {{ } }}',
                '<?php if ($a < 4) { ?> hi <?php } ?>',
            ],
            [
                '{{if ($a < 4) }}
hi
{{endif}}',
                '<?php if ($a < 4): ?>
hi
<?php endif ?>',
            ],
            [
                '<?php if ($a < 4): ?> hi <?php endif ?>', // raw
                '<?php if ($a < 4): ?> hi <?php endif ?>',
            ],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_for():void
    {
        $p = new PregCompiler();

        $code = <<<'CODE'
{{ for ($i=0; $i< 5; $i++) }}
    line: {{ $i }}
{{ endfor }}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php for ($i=0; $i< 5; $i++): ?>
    line: <?= $i ?>
<?php endfor ?>
CODE
            ,$compiled);
    }

    public function testCompile_foreach():void
    {
        $p = new PregCompiler();

        $code = <<<'CODE'
{{ foreach($tags as $tag) }}
- {{ $tag }}

{{ endforeach }}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php foreach($tags as $tag): ?>
- <?= $tag ?>

<?php endforeach ?>
CODE
            ,$compiled);
    }

    public function testCompile_ml_define():void
    {
        $p = new PregCompiler();

        $code = <<<'CODE'
{{

$a = random_int(1, 10);
}}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php $a = random_int(1, 10); ?>
CODE
            ,$compiled);

        $code = <<<'CODE'
{{
// comments
$a = random_int(1, 10);
}}
CODE;
        $compiled = $p->compile($code);
        $this->assertEquals(<<<'CODE'
<?php
// comments
$a = random_int(1, 10);
?>
CODE
            ,$compiled);
    }

}
