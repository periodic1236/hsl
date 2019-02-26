<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

/**
 * C is for Containers. This file contains functions that ask
 * questions of (i.e. introspect) containers and traversables.
 */
namespace HH\Lib\C;

/**
 * Returns true if the given predicate returns true for any element of the
 * given Traversable. If no predicate is provided, it defaults to casting the
 * element to bool.
 *
 * If you're looking for `C\none`, use `!C\any`.
 */
<<__Rx, __AtMostRxAsArgs>>
function any<T>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<T> $traversable,
  <<__AtMostRxAsFunc>>
  ?(function(T): bool) $predicate = null,
): bool {
  $predicate ??= fun('\\HH\\Lib\\_Private\\boolval');
  foreach ($traversable as $value) {
    if ($predicate($value)) {
      return true;
    }
  }
  return false;
}

/**
 * Returns true if the given Traversable contains the value. Strict equality is
 * used.
 */
<<__Rx, __AtMostRxAsArgs>>
function contains<T>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<T> $traversable,
  T $value,
): bool {
  if ($traversable is keyset<_>) {
    return contains_key($traversable, $value);
  }
  foreach ($traversable as $v) {
    if ($value === $v) {
      return true;
    }
  }
  return false;
}

/**
 * Returns true if the given KeyedContainer contains the key.
 */
<<__Rx>>
function contains_key<Tk, Tv>(
  /* HH_FIXME[4110] Tk needs to be constrained to arraykey */
  <<__MaybeMutable>> KeyedContainer<Tk, Tv> $container,
  Tk $key,
): bool {
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  /* HH_IGNORE_ERROR[4110] array_key_exists expects arraykey */
  return \array_key_exists($key, $container);
}

/**
 * Returns the number of elements in the given Container.
 */
<<__Rx>>
function count<T>(
  <<__MaybeMutable>> Container<T> $container,
): int {
  /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  return \count($container);
}

/**
 * Returns true if the given predicate returns true for every element of the
 * given Traversable. If no predicate is provided, it defaults to casting the
 * element to bool.
 *
 * If you're looking for `C\all`, this is it.
 */
<<__Rx, __AtMostRxAsArgs>>
function every<T>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<T> $traversable,
  <<__AtMostRxAsFunc>>
  ?(function(T): bool) $predicate = null,
): bool {
  $predicate ??= fun('\\HH\\Lib\\_Private\\boolval');
  foreach ($traversable as $value) {
    if (!$predicate($value)) {
      return false;
    }
  }
  return true;
}

/**
 * Returns whether the given Container is empty.
 */
<<__Rx>>
function is_empty<T>(
  Container<T> $container,
): bool {
  return !$container;
}
