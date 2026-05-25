<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;

final class UserTypeTest extends TestCase{
	public function testAssert(): void
    {
        $this->assertSame("1", "1");
    }

    public function testException(): void
    {
        $this->expectException(Exception::class);
	throw new Exception();
    }
}
?>

