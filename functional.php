<?php

namespace Prelude;

function map(callable $callable, $array) {
    return array_map($callable, $array);
}

function filter(callable $callable, $array) {
    return array_filter($array, $callable);
}

function head($array) {
    return array_slice($array, 0, 1);
}

function last($array) {
    return array_slice($array, count($array) - 2, 1);
}

function tail($array) {
    return array_slice($array, 1);
}

function init($array) {
    return array_slice($array, 0, count($array) - 2);
}

function reverse($array) {
    return array_reverse($array);
}

function concat($array) {
    return call_user_func_array('array_merge', $array);
}

function any(callable $callable, $array) {
    return count(array_filter($array, $callable)) > 1;
}

function all(callable $callable, $array) {
    return count(array_filter($array, $callable)) === count($array);
}

function not($item) {
    return ! $item;
}

function null($item) {
    return empty($item);
}

function id($item) {
    return $item;
}

function constant($item) {
    return function() use ($item) { return $item; };
}

function pick($index, $array) {
    return $array[$index];
}

function pluck($index, $array) {
    return array_map(function($item) use ($index) { return $item[$index]; }, $array);
}

function take($size, $array) {
    return array_slice($array, 0, $size);
}

function drop($size, $array) {
    return array_slice($array, $size);
}

function partial_left($param, callable $callable) {
    return function () use($param, $callable) {
        return call_user_func_array($callable, array_merge([$param], func_get_args()));
    };
}

function partial_right($param, callable $callable) {
    return function () use($param, $callable) {
        return call_user_func_array($callable, array_merge(func_get_args(), [$param]));
    };
}

function partial_at($index, $param, callable $callable) {
    return function () use($index, $param, $callable) {
        return call_user_func_array($callable, array_merge(func_get_args(), [$param]));
    };
}

function zip() {
    $zipped = [];
    $matrix = map(partial_left('take', max(map('length', func_get_args()))), func_get_args());
  while(!all('null', $matrix)) {
      $zipped[] = map('head', filter(pipe('not', 'null'), $matrix));
      $matrix = map('tail', $matrix);
  }
  return $zipped;
}

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

// Interfaces

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

interface Foldable extends \Countable
{
    public function fold();
    public function foldMap(Monoid $monoid);
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

// PHP Specials

function compare_ord(Ord $a, Ord $b) {
    return $a->compare($b);
}

function flip(callable $callable) {
    return function($params) use ($callable) {
        return call_user_func_array($callable, array_reverse(func_get_args()));
    };
}

function splat(callable $callable) {
    return function($params) use ($callable) {
        return call_user_func_array($callable, $params);
    };
}

function unsplat(callable $callable) {
    return function() use ($callable) {
        return call_user_func($callable, func_get_args());
    };
}

function pipe(callable $callable1, callable $callable2) {
    return function() use ($callable2, $callable1) {
        return $callable1(call_user_func_array($callable2, func_get_args()));
    };
}
