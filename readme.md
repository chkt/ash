#ash
## A basic expression solver

Ash is an expression solver using basic arithmetic expressions in a sandboxed environment.
It is meant to offer a convenient way to programmatically access properties in hierarchical data.

### Install
#### Composer

Ash is available through [composer](https://getcomposer.org/).

##### Command line

```bash
$ php composer.phar install chkt/ash
``` 

##### composer.json

```json
{
  "require" : {
    "chkt/eve" : "<version>"
  }
}
```

#### Manual installation

Alternatively you can clone the [github repository](https://github.com/chkt/ash) into a location of your liking.
Afterwards you will have to register the [PSR-4](https://www.php-fig.org/psr/psr-4/) namespace `ash`
within php yourself.

```bash
$ git clone https://github.com/chkt/ash.git
``` 

Ash depends on [chkt/eve](https://github.com/chkt/eve). 
When cloning manually you will have to ensure that package is also available 
and it's PSR-4 namespace is registered yourself.

### Creating a solver

```php
  $baseFactory = new \eve\common\factory\BaseFactory();
  $parserFactory = new \ash\ParserFactory($baseFactory);
```

The parserFactory requires an instance of [\eve\common\factory\IBaseFactory](https://github.com/chkt/eve/blob/master/source/common/factory/IBaseFactory.php).
You can use the [bundled factory](https://github.com/chkt/eve/blob/master/source/common/factory/BaseFactory.php)
or implement your own.

```php
  $parser = $parserFactory->produce();
  $solver = $parser->parse('a + b');
```

Parsing the expression `a + b` will return the solver object for this expression.

```php
  $result = $solver->resolve([ 
    'a' => 1,
    'b' => 2 
  ]);
```

The solver will resolve this expression for any data provided to it's `->resolve()` method.

## Supported syntax

The expression syntax broadly follows c-style syntax conventions, with strong nods to the
functional paradigm of javascript.

Ash is not meant to be comprehensive and is therefore deliberately limited to evaluating expressions. 
There are no statements, no assignments, no way to define functions or compose data, 
and no way to mutate any data within its scope through the syntax.

Everything accessible within the solver's scope needs to be provided by the host program,
making it impossible to access any part of the host system within an expression 
when that access has not been deliberately provided.

#### Scalar values

Currently supported are floating point numbers in basic `1.0` and exponent notation `1.0e-1`, 
and integers in basic `1`, binary `0b1` and hexadecimal `0x1` notation.

Any number starting with `0x` or `0b` will be treated as an integer, 
any other number containing a dot `0.0` will be treated as a float.
All remaining numbers will be treated as integers.

Support for booleans, strings and typecasting is planned. 

#### Arithmetic operations

Currently ash supports arithmetic expressions for addition `a + b`,
substraction `a - b`, multiplication `a * b`, division `a / b` and
modulo `a % b` as well as bracketed expressions `a * (b + c)`.

All arithmetic operations will convert boolean arguments to the integer values `0` and `1`.
Integers will be cast to `float` if at least one operand is a floating point number.
Divisions by zero `1 / 0` will return `INF` and `-INF` respectively,
modulo zero `1 % 0` will return `NAN` irrespective of input types.

Support for comparisons, prefix `+` and `-`, `typeof`, the exponentiation operator
and logic operations is planned. 

#### Property accesses

Property accesses are done using the `.` operator for static access `foo.bar`, and
square brackets for computed access `foo[bar]`.
Static and computed accesses can be freely mixed `foo[bar].baz[qux][qux]`.

#### Calls

Anything that resolves to a `callable` within php can be called through the solver
using the round brackets `foo()`.
Arguments are separated by commas `foo(bar, baz)`.
Nested `foo(bar(), baz())` and chained calls `foo()()` are allowed.
The return values of calls can be freely used in any subsequent operation `foo().bar + baz()`. 
