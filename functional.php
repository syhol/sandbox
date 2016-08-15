<?php

namespace Prelude;

use Closure;
use Exception;
use Generator;
use ReflectionFunction;
use ReflectionMethod;

function ary($collection) {
    if (is_array($collection)) {
        return $collection;
    } elseif (is_string($collection)) {
        return str_split($collection);
    } elseif ($collection instanceof Arrayable) {
        return $collection->toArray();
    } elseif ($collection instanceof \Generator) {
        $array = [];
        while ($collection->valid()) {
            $array[] = $collection->current();
            $collection->next();
        }
        return $array;
    } elseif ($collection instanceof \Iterator) {
        return iterator_to_array($collection);
    }

    throw new Exception('Could not convert to array');
}

function col($collection, array $array) {
    if (is_string($collection)) {
        return implode('', $array);
    } elseif ($collection instanceof Arrayable) {
        return $collection->fromArray($array);
    }

    return $array;
}

function append($item, $collection) {
    $array = ary($collection);
    array_push($array, $item);
    return col($collection, $array);
}

function prepend($item, $collection) {
    $array = ary($collection);
    array_unshift($array, $item);
    return col($collection, $array);
}

function foldr(callable $callable, $initial, $collection) {
    foreach (ary($collection) as $item) {
        $initial = $callable($item, $initial);
    }
    return $initial;
}

function foldl(callable $callable, $initial, $array) {
    return foldr(flip($callable), $initial, $array);
}

function map(callable $callable, $collection) {
    $array = ary($collection);
    foreach ($array as $key => $value) {
        $array[$key] = $callable($value);
    }
    return col($collection, $array);
}

function filter(callable $callable, $collection) {
    $array = [];
    foreach ($collection as $key => $value) {
        if($callable($value)) $array[$key] = $value;
    }
    return col($collection, $array);
}

function head($array) {
    return take(1, $array);
}

function last($array) {
    return take(-1, $array);
}

function tail($array) {
    return drop(1, $array);
}

function init($array) {
    return drop(-1, $array);
}

function reverse($array) {
    return foldr('Prelude\prepend', [], $array);
}

function concat($collection) {
    return col($collection, array_merge(...ary($collection)));
}

function any(callable $callable, $array) {
    return count(filter($callable, $array)) > 0;
}

function all(callable $callable, $array) {
    return count(filter($callable, $array)) === count($array);
}

function contains($item, $iterator) {
    return any(partial('Prelude\equals', $item), $iterator);
}

function equals($a, $b) {
    return $a === $b;
}

function null($item) {
    return ! $item;
}

function id($item) {
    return $item;
}

function constant($item) {
    return function() use ($item) { return $item; };
}

function insert($index, $value, $array) {
    return $array[$index] = $value;
}

function exists($index, $array) {
    return isset($array[$index]);
}

function delete($index, $array) {
    unset($array[$index]);
    return $array;
}

function pick($index, $array) {
    return $array[$index];
}

function pluck($index, $collection) {
    return map(partial('pick', $index), $collection);
}

function take($size, $collection) {
    if ($collection instanceof Generator) {
        if ($size < 0) throw new Exception('Can\'t take items from end of Generators');
        $count = 0;
        $array = [];
        while ($count++ < $size) {
            $collection->next();
            $array[] = $collection->current();
        }
        return $array;
    }

    return $size > 0
        ? col($collection, array_slice(ary($collection), 0, $size))
        : col($collection, array_slice(ary($collection), $size));
}

function drop($size, $collection) {
    return $size > 0
        ? col($collection, array_slice(ary($collection), $size))
        : col($collection, array_slice(ary($collection), 0, $size));
}

function zip(...$arrays) {
    $zipped = [];
    $matrix = map(partial('Prelude\take', min(map('count', $arrays))), $arrays);
    while(!all('Prelude\not', $matrix)) {
        $zipped[] = concat(map('Prelude\head', $matrix));
        $matrix = map('Prelude\tail', $matrix);
    }
    return $zipped;
}

function map_to_tuple($array) {
    return zip(array_keys(ary($array)), ary($array));
}

function tuple_to_map($array) {
    $keys = concat(map('Prelude\head', $array));
    $values = concat(map('Prelude\last', $array));
    return array_combine($keys, $values);
}

// String

function chars($chars) {
    return str_split($chars);
}

function unchars($chars) {
    return implode('', $chars);
}

function words($words) {
    return explode(' ', $words);
}

function unwords($words) {
    return implode(' ', $words);
}

function lines($lines) {
    return explode(PHP_EOL, $lines);
}

function unlines($lines) {
    return implode(PHP_EOL, $lines);
}

// Infinite List

function times(callable $callable, $size) {
    $count = 0;
    while ($count++ < $size) yield $callable();
}

function iterate(callable $callable, $initial) {
    while(true) yield $initial = $callable($initial);
}

function repeat($item) {
    while (true) yield $item;
}

function replicate($item, $size) {
    $count = 0;
    while ($count++ < $size) yield $item;
}

function cycle($list) {
    while (true) foreach ($list as $item) yield $item;
}

// PHP Specials

function compare_ord(Ord $a, Ord $b) {
    return $a->compare($b);
}

function flip(callable $callable) {
    return function(...$params) use ($callable) {
        return $callable(...array_reverse($params));
    };
}

function splat(callable $callable) {
    return function($params) use ($callable) {
        return $callable(...$params);
    };
}

function unsplat(callable $callable) {
    return function(...$params) use ($callable) {
        return $callable($params);
    };
}

function compose(callable $callable1, callable $callable2) {
    return function(...$params) use ($callable1, $callable2) {
        return $callable1($callable2(...$params));
    };
}

function partial(callable $callable, ...$params) {
    return function (...$more) use($params, $callable) {
        return $callable(...array_merge($params, $more));
    };
}

function apply(callable $callable, ...$params) {
    return $callable(...$params);
}

function method($method) {
    return function ($object) use($method) {
        return [$object, $method];
    };
}

function reflect_callable(callable $callable) {
    $callable = (is_string($callable) && strpos($callable, '::') !== false)
        ? explode('::', $callable, 2)
        : $callable;

    if (is_array($callable) && count($callable) === 2) {
        list($class, $method) = array_values($callable);

        if (is_string($class) && ! method_exists($class, $method)) {
            $method = '__callStatic';
        }
        if (is_object($class) && ! method_exists($class, $method)) {
            $method = '__call';
        }
        return new ReflectionMethod($class, $method);
    } elseif ($callable instanceof Closure || is_string($callable)) {
        return new ReflectionFunction($callable);
    } elseif (is_object($callable) && method_exists($callable, '__invoke')) {
        return new ReflectionMethod($callable, '__invoke');
    }

    throw new Exception('Could not parse function');
}

function curry(callable $callable, $count = null) {
    $count = is_null($count) ? reflect_callable($callable)->getNumberOfRequiredParameters() : $count;
    return $count === 0 ? $callable : function (...$params) use($callable, $count) {
        $apply = partial($callable, ...$params);
        return count($params) >= $count ? $apply() : curry($apply, $count - count($params));
    };
}

class Foo { public function bar($name, $box) { echo $name . $box . PHP_EOL; } }

// Interfaces

interface Arrayable
{
    public function toArray();
    public function fromArray(array $array);
}
interface Eq
{
    public function equal(Eq $other);
    public function notEqual(Eq $other);
}

interface Ord extends Eq
{
    public function compare(Ord $other);
    public function lt(Ord $other);
    public function lte(Ord $other);
    public function gt(Ord $other);
    public function gte(Ord $other);
    public function max(Ord $other);
    public function min(Ord $other);
}

interface Show
{
    public function __toString();
}

interface Read
{
    public function read($string);
}

interface Enum
{
    public function succ();
    public function pred();
    public function range();
}

interface Bounded
{
    public function minBound();
    public function maxBound();
}

interface Monoid
{
    public function emptyValue();
    public function append($value);
    public function concat($value);
}

interface Functor
{
    public function map(callable $callable);
    public function pure($value);
}

interface Applicative extends Functor
{
    public function apply(Functor $callable);
}

interface Monad extends Applicative
{
    public function bind(callable $callable);
}

interface Comonad extends Applicative
{
    public function duplicate();
    public function extend(callable $callable);
    public function extract();
}

interface Foldable extends \Countable
{
    public function fold();
    public function foldMap(callable $callable);
    public function foldr(callable $callable, $initial);
    public function foldl(callable $callable, $initial);
    public function null();
    public function elem($element);
    public function maximum();
    public function minimum();
    public function sum();
    public function product();
    public function toArray();
}

abstract class Maybe implements Monad, Comonad
{
    public function pure($value)
    {
        return new Just($value);
    }

    public function extend(callable $callable)
    {
        return $this->pure(call_user_func($callable, $this));
    }

    public function duplicate()
    {
        return $this->pure($this);
    }

    static public function just($value)
    {
        return new Just($value);
    }

    static public function nothing()
    {
        return new Nothing();
    }
}

class Just extends Maybe
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Just constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function map(callable $callable)
    {
        return new Just(call_user_func($callable, $this->value));
    }

    public function apply(Functor $callable)
    {
        return $callable->map(function($function) {
            return call_user_func($function, $this->value);
        });
    }

    public function bind(callable $callable)
    {
        return call_user_func($callable, $this->value);
    }

    public function extract()
    {
        return $this->value;
    }
}

class Nothing extends Maybe
{
    public function map(callable $callable)
    {
        return new Nothing();
    }

    public function apply(Functor $callable)
    {
        return new Nothing();
    }

    public function bind(callable $callable)
    {
        return new Nothing();
    }

    public function extract()
    {
        return null;
    }
}

class Collection implements Monad, Monoid, Foldable
{
    /**
     * @var array
     */
    private $values;

    /**
     * Just constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function pure($value)
    {
        return new Collection($value);
    }

    public function map(callable $callable)
    {
        return $this->pure(array_map($callable, $this->values));
    }

    public function apply(Functor $callable)
    {
        return $callable->map(function($function) {
            return array_map($function, $this->values);
        });
    }

    public function bind(callable $callable)
    {
        return array_map($callable, $this->values);
    }

    public function emptyValue()
    {
        return $this->pure([]);
    }

    public function append($value)
    {
        return $this->pure(array_merge([$value], $this->values));
    }

    public function concat($value)
    {
        return $this->pure(array_merge($value, $this->values));
    }

    public function count()
    {
        return count($this->values);
    }

    public function fold()
    {
        return $this->foldMap(function ($a) { return $a; });
    }

    public function foldMap(callable $callable)
    {
        $appender = function ($a, $b) { return $a->append($b); };
        return $this->map($callable)->foldl($appender, $this->emptyValue());
    }

    public function foldr(callable $callable, $initial)
    {
        return array_reduce($this->values, $callable, $initial);
    }

    public function foldl(callable $callable, $initial)
    {
        return array_reduce($this->values, flip($callable), $initial);
    }

    public function null()
    {
        return empty($this->values);
    }

    public function elem($element)
    {
        return in_array($element, $this->values);
    }

    public function maximum()
    {
        return max($this->values);
    }

    public function minimum()
    {
        return min($this->values);
    }

    public function sum()
    {
        return array_sum($this->values);
    }

    public function product()
    {
        return array_product($this->values);
    }

    public function toArray()
    {
        return $this->values;
    }
}
