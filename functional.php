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

function tail($array) {
  return array_slice($array, 1);
}

function last($array) {
  return array_slice($array, count($array) - 2, 1);
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
  return array_map($array, function($item) use ($index) { return $item[$index]; };
}

function partial_left(callable $callable, $param) {
  return function () use($callable, $param) {
    return call_user_func_array($callable, array_merge([$param], func_get_args());
  };
}

function partial_right(callable $callable, $param) {
  return function () use($callable, $param) {
    return call_user_func_array($callable, array_merge(func_get_args(), [$param]);
  };
}

function take($size, $array) {
  array_slice($array, 0, $size);
}

function drop() {
  array_slice($array, $size);
}

function zip() {
  
}

function unzip() {

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
