<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest;

use PhpPkg\EasyTpl\SimpleTemplate;

/**
 * class SimpleTemplateTest
 *
 * @author inhere
 * @date 2022/12/30
 */
class SimpleTemplateTest extends BaseTestCase
{
    public function testSimpleTemplate(): void
    {
        $st = SimpleTemplate::new();
        $st->setGlobalVars(['age' => 300]);

        $tpl = <<<TPL
Hi, my name is {{ name }}, age is {{ age }}.
first tag: {{ tags.0 }}
user info: {{ user }}
TPL;

        $vars = [
            'name' => 'inhere',
            'tags' => ['php'],
            'user' => ['sex' => 'man'],
        ];
        $str = $st->renderString($tpl, $vars);
        $this->assertStringContainsString('name is inhere', $str);
        $this->assertStringContainsString('age is 300', $str);
        $this->assertStringContainsString('first tag: php', $str);
        $this->assertStringContainsString('user info: {sex: man}', $str);

        $str = $st->renderFile($this->getTestdataPath('simple.tpl'), $vars);
        $this->assertStringContainsString('name is inhere', $str);
        $this->assertStringContainsString('age is 300', $str);
        $this->assertStringContainsString('first tag: php', $str);
        $this->assertStringContainsString('user sex: man', $str);
    }

    public function testSimpleTemplate_withFilters(): void
    {
        $st = SimpleTemplate::new();
        $vs = [
            'age'  => 300,
            'name' => 'inhere',
            'tags' => ['php'],
            'user' => ['sex' => 'man'],
        ];

        $tpl = <<<TPL
Hi, my name is {{ name | substr:0,3 | upper }}, age is {{ age }}.
first tag: {{ tags.0 | upper}}
user info: {{ user }}
TPL;

        $str = $st->renderString($tpl, $vs);
        $this->assertStringContainsString('name is INH', $str);
        $this->assertStringContainsString('age is 300', $str);
        $this->assertStringContainsString('first tag: PHP', $str);
        $this->assertStringContainsString('user info: {sex: man}', $str);

    }
}
