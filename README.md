# Convert Legacy PHPUnit @expectedException

Simple CLI tool used to convert deprecated PHPUnit's @expectedException, @expectedExceptionMessage, etc.
comment annotation into recommended `$this->expectException*`.

This tool is mostly based on [PHP-Parser](https://github.com/nikic/php-parser)
and a bit of magic to handle white spaces.

Before:

```php
/**
 * Let's test the core!
 *
 * @expectedException \ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException
 * @expectedExceptionCode 123
 * @expectedExceptionMessage This is unacceptable
 * @expectedExceptionMessageRegExp /You shouldn't mix message and regexp/
 */
public function testSomeCoreMethod(): void
{
    $this->assertTrue(true);
}
```

After:

```php
/**
 * Let's test the core!
 */
public function testSomeCoreMethod(): void
{
    $this->expectException(\ArthurHoaro\ConvertLegacyPHPUnitExpect\ConverterException::class);
    $this->expectExceptionCode(123);
    $this->expectExceptionMessage('This is unacceptable');
    $this->expectExceptionMessageMatches('/You shouldn\'t mix message and regexp/');

    $this->assertTrue(true);
}
```

## Usage

Clone the repository locally, then run the CLI tool.

```
usage: run.php [<options>]

Convert legacy PHPUnit @expectedException to $this->expectException and
associated messages.

OPTIONS
  --dry-run         Display changes without altering path files.
  --help, -?        Display this help.
  --legacy-regexp   Convert @expectedExceptionMessageRegExp to
                    expectExceptionMessageRegExp instead of
                    expectExceptionMessageMatches.
  --path, -p        PHPUnit tests folder (absolute or relative to this script.
```

## Example

```shell
git clone http://github.com/ArthurHoaro/convert-legacy-phpunit-expect.git
cd convert-legacy-phpunit-expect

# With dry run option, it won't change your files
./run.php --path="/path/to/your/project/tests/folder" --dry-run

# If the command output is satisfaying, re-run without dry mode
./run.php --path="/path/to/your/project/tests/folder"
```

## Use case

Successfully used on [Shaarli](https://github.com/shaarli/Shaarli) code-base.

> Finished!
>
> Files processed: 109
> Files converted: 21

See [shaarli/Shaarli#1572](https://github.com/shaarli/Shaarli/pull/1572).

## License

[MIT](LICENSE.md)
