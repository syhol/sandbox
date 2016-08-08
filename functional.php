<?php

namespace Prelude;

function pipe(callable $callable1, callable $callable2) {
  return function() use ($callable2, $callable1) { 
    return $callable1(call_user_func_array($callable2, func_get_args()));
  };
}

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

function partial_left($param, callable $callable) {
  return function () use($param, $callable) {
    return call_user_func_array($callable, array_merge([$param], func_get_args());
  };
}

function partial_right($param, callable $callable) {
  return function () use($param, $callable) {
    return call_user_func_array($callable, array_merge(func_get_args(), [$param]);
  };
}

function take($size, $array) {
  return array_slice($array, 0, $size);
}

function drop($size, $array) {
  return array_slice($array, $size);
}

function not($item) {
  return ! $item
}

function null($item) {
  return empty($item)
}

function zip() {
  $zipped = [];
  $matrix = map(partial_left('take' max(map('length', func_get_args()))), func_get_args());
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
