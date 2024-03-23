<?php declare(strict_types=1);

namespace PhpPkg\EasyTplTest\Compiler;

use PhpPkg\EasyTpl\Compiler\CompileUtil;
use PhpPkg\EasyTplTest\BaseTestCase;

/**
 * class CompileUtilTest
 *
 * @author inhere
 * @date 2022/12/30
 */
class CompileUtilTest extends BaseTestCase
{
    public function testCanAddVarPrefix(): void
    {
        $this->assertTrue(CompileUtil::canAddVarPrefix('abc'));
        $this->assertTrue(CompileUtil::canAddVarPrefix('top.abc'));
        $this->assertTrue(CompileUtil::canAddVarPrefix('top.sub-key'));

        $this->assertFalse(CompileUtil::canAddVarPrefix("top['abc']"));
        $this->assertFalse(CompileUtil::canAddVarPrefix('abc()'));
        $this->assertFalse(CompileUtil::canAddVarPrefix('PHP_OS'));
        $this->assertFalse(CompileUtil::canAddVarPrefix('__LINE__'));
    }

    public function testPathToArrayAccess(): void
    {
        $this->assertEquals('$varName', CompileUtil::toArrayAccessStmt('varName'));
        $this->assertEquals("= varName", CompileUtil::toArrayAccessStmt('= varName'));

        $this->assertEquals("\$ctx['top']['sub']", CompileUtil::toArrayAccessStmt('ctx.top.sub'));
        $this->assertEquals("\$ctx['top']['sub']", CompileUtil::toArrayAccessStmt('$ctx.top.sub'));

        // with fallback statement
        $ret = <<<EOT
\$ctx['top']['sub'] ?? "fallback"
EOT;

        $this->assertEquals($ret, CompileUtil::toArrayAccessStmt('ctx.top.sub ?? "fallback"'));
        $this->assertEquals($ret, CompileUtil::toArrayAccessStmt('$ctx.top.sub ?? "fallback"'));

        $ret = <<<EOT
= \$ctx['top']['sub'] ?? "fallback"
EOT;
        $this->assertEquals($ret, CompileUtil::toArrayAccessStmt('= $ctx.top.sub ?? "fallback"'));

        $ret = <<<EOT
= \$ctx['top']['sub'] ?? "fall.back"
EOT;
        $this->assertEquals($ret, CompileUtil::toArrayAccessStmt('= $ctx.top.sub ?? "fall.back"'));
    }
}
