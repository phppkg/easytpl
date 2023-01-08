<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest\Compiler;

use PhpPkg\EasyTpl\Compiler\PregCompiler;
use PhpPkg\EasyTpl\Compiler\Token;
use PhpPkg\EasyTplTest\BaseTestCase;
use function preg_match;
use function vdump;

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
            // for
            ['for ', Token::T_FOR],
            ['endfor ', Token::T_ENDFOR],
            ['endfor;', Token::T_ENDFOR],
            // foreach
            ['foreach ', 'foreach'],
            ['foreach(', Token::T_FOREACH],
            ['endforeach ', Token::T_ENDFOREACH],
            ['endforeach;', Token::T_ENDFOREACH],
            ['endforeach; ', Token::T_ENDFOREACH],
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
        // vdump($pattern);
        foreach ($tests as [$in, $out]) {
            $ret = preg_match($pattern, $in, $matches);
            if ($out) {
                $this->assertEquals(1, $ret, "in: $in, out: $out");
                $this->assertEquals($out, $matches[1]);
            } else {
                $this->assertEquals(0, $ret, "in: $in");
            }
        }
    }

    public function testCompileCode_inline_echo():void
    {
        $p = new PregCompiler();

        $this->assertNotEquals('<?= $name ?>', $p->compile('{{= name }}'));

        $tests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            // const
            ['{{ PHP_OS }}', '<?= PHP_OS ?>'],
            ['{{= PHP_OS }}', '<?= PHP_OS ?>'],
            ["{{\n PHP_OS }}", "<?= PHP_OS ?>"],
            ['{{ echo PHP_OS }}', '<?php echo PHP_OS ?>'],
            ['{{ __LINE__ }}', '<?= __LINE__ ?>'],
            ['{{= __LINE__ }}', '<?= __LINE__ ?>'],
            ['{{ echo __LINE__ }}', '<?php echo __LINE__ ?>'],
            ['{{ SomeClass::NAME }}', '<?= SomeClass::NAME ?>'],
            ['{{ echo SomeClass::NAME }}', '<?php echo SomeClass::NAME ?>'],
            // prop
            ['{{ SomeClass::$name }}', '<?= SomeClass::$name ?>'],
            // var
            ['{{ name }}', '<?= $name ?>'],
            ['{{ $name }}', '<?= $name ?>'],
            ['{{= $name }}', '<?= $name ?>'],
            ['{{ echo $name }}', '<?php echo $name ?>'],
            ['{{ $name; }}', '<?= $name; ?>'],
            ['{{ $name ?: "inhere" }}', '<?= $name ?: "inhere" ?>'],
            ['{{ $name ?? "inhere" }}', '<?= $name ?? "inhere" ?>'],
            ['{{ $name ?? "inhere"; }}', '<?= $name ?? "inhere"; ?>'],
            // sub key
            ['{{ ctx.pkgName }}', '<?= $ctx[\'pkgName\'] ?>'],
            ['{{ $ctx.pkgName }}', '<?= $ctx[\'pkgName\'] ?>'],
            ['{{ $ctx.pkgName ?? "default" }}', '<?= $ctx[\'pkgName\'] ?? "default" ?>'],
            ['{{ ctx.pkg-name }}', '<?= $ctx[\'pkg-name\'] ?>'],
            ['{{ $ctx.pkg-name }}', '<?= $ctx[\'pkg-name\'] ?>'],
            ['{{ ctx.pkg_name }}', '<?= $ctx[\'pkg_name\'] ?>'],
            ['{{ $ctx.pkg_name }}', '<?= $ctx[\'pkg_name\'] ?>'],
            // multi parts
            ['{{ ctx.top1.pkg-name }}', '<?= $ctx[\'top1\'][\'pkg-name\'] ?>'],
            ['{{ $ctx.top1.pkg-name }}', '<?= $ctx[\'top1\'][\'pkg-name\'] ?>'],
            ['{{ $ctx.top1.pkgName }}', '<?= $ctx[\'top1\'][\'pkgName\'] ?>'],
            ['{{ ctx.top-node.pkg-name }}', '<?= $ctx[\'top-node\'][\'pkg-name\'] ?>'],
            ['{{ $ctx.top-node.pkg-name }}', '<?= $ctx[\'top-node\'][\'pkg-name\'] ?>'],
            // func
            ['{{ some_func() }}', '<?= some_func() ?>'],
            ['{{ some_func(); }}', '<?= some_func(); ?>'],
            ['{{ SomeClass::func(); }}', '<?= SomeClass::func(); ?>'],
            ['{{ $this->include("header.tpl") }}', '<?= $this->include("header.tpl") ?>'],
            // more
            [
                '{{= $ctx.pkgName ?? "org.example.entity" }}',
                '<?= $ctx[\'pkgName\'] ?? "org.example.entity" ?>'
            ],
            [
                '{{= $ctx->pkgName ?? "org.example.entity" }}',
                '<?= $ctx->pkgName ?? "org.example.entity" ?>'
            ],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_inline_echo_with_filters():void
    {
        $p = new PregCompiler();

        $tests = [
            ['{{ "a" . "b" }}', '<?= "a" . "b" ?>'],
            ['{{ name | ucfirst }}', '<?= htmlspecialchars((string)ucfirst($name)) ?>'],
            ['{{ $name | ucfirst }}', '<?= htmlspecialchars((string)ucfirst($name)) ?>'],
            ['{{ user.name | ucfirst }}', '<?= htmlspecialchars((string)ucfirst($user[\'name\'])) ?>'],
            ['{{ user.first-name | ucfirst }}', '<?= htmlspecialchars((string)ucfirst($user[\'first-name\'])) ?>'],
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

    public function testCompile_customDirective_include():void
    {
        $p = new PregCompiler();
        $p->addDirective('include', function (string $body, string $name) {
            return '$this->' . $name . $body;
        });
        $p->addDirective('endblock', function (string $body) {
            vdump($body);
            return '$this->endBlock();';
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
            ['{{ endblock }}', '<?php $this->endBlock(); ?>'],
            ['{{ endblock; }}', '<?php $this->endBlock(); ?>'],
        ];
        foreach ($tests as [$in, $out]) {
            $this->assertEquals($out, $p->compile($in));
        }
    }

    public function testCompile_has_comments():void
    {
        $p = new PregCompiler();

        $str = <<<'TXT'
{{# comments #}} hello
TXT;
        $out = $p->compile($str);
        $this->assertStringNotContainsString('{{#', $out);
        $this->assertStringNotContainsString('comments', $out);
        $this->assertEquals(' hello', $out);

        $str = <<<'TXT'
{{# multi
 line
  comments
#}} hello
TXT;
        $out = $p->compile($str);
        $this->assertStringNotContainsString('{{#', $out);
        $this->assertStringNotContainsString('comments', $out);
        $this->assertEquals(' hello', $out);

        $str = <<<'TXT'
{{ foreach ($vars as $var): }}
{{#
comments
 newInfo.incr{{ $var | ucfirst }}({{ $var }});
#}}
public void {{ $var | ucfirst }}(Integer value) {
    {{ $var }} += value;
}
{{ endforeach }}
TXT;
        $out = $p->compile($str);
        $this->assertStringNotContainsString('{{#', $out);
        $this->assertStringNotContainsString('comments', $out);
    }

}
