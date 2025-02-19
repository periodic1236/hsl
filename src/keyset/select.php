<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Keyset;

use namespace HH\Lib\C;

/**
 * Returns a new keyset containing only the elements of the first Traversable
 * that do not appear in any of the other ones.
 *
 * Time complexity: O(n + m), where n is size of `$first` and m is the combined
 * size of `$second` plus all the `...$rest`
 * Space complexity: O(n + m), where n is size of `$first` and m is the combined
 * size of `$second` plus all the `...$rest` -- note that this is bigger than
 * O(n)
 */
<<__Pure, __AtMostRxAsArgs>>
function diff<Tv1 as arraykey, Tv2 as arraykey>(
  <<__OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv1> $first,
  <<__OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv2> $second,
  Container<Tv2> ...$rest
): keyset<Tv1> {
  /* HH_FIXME[4276] optimized for Containers but others still work overall */
  if (!$first) {
    return keyset[];
  }
  /* HH_FIXME[4276] optimized for Containers but others still work overall */
  if (!$second && !$rest) {
    return keyset($first);
  }
  $union = !$rest
    ? keyset($second)
    : union($second, ...$rest);
  return filter(
    $first,
    $value ==> !C\contains_key($union, $value),
  );
}
/**
 * Returns a new keyset containing all except the first `$n` elements of
 * the given Traversable.
 *
 * To take only the first `$n` elements, see `Keyset\take()`.
 *
 * Time complexity: O(n), where n is the size of `$traversable`
 * Space complexity: O(n), where n is the size of `$traversable`
 */
<<__Pure, __AtMostRxAsArgs>>
function drop<Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $traversable,
  int $n,
): keyset<Tv> {
  invariant($n >= 0, 'Expected non-negative N, got %d.', $n);
  $result = keyset[];
  $ii = -1;
  foreach ($traversable as $value) {
    $ii++;
    if ($ii < $n) {
      continue;
    }
    $result[] = $value;
  }
  return $result;
}

/**
 * Returns a new keyset containing only the values for which the given predicate
 * returns `true`. The default predicate is casting the value to boolean.
 *
 * To remove null values in a typechecker-visible way, see `Keyset\filter_nulls()`.
 *
 * Time complexity: O(n * p), where p is the complexity of `$value_predicate`
 * Space complexity: O(n)
 */
<<__Pure, __AtMostRxAsArgs>>
function filter<Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $traversable,
  <<__AtMostRxAsFunc>>
  ?(function(Tv): bool) $value_predicate = null,
): keyset<Tv> {
  $value_predicate ??= \HH\Lib\_Private\boolval<>;
  $result = keyset[];
  foreach ($traversable as $value) {
    if ($value_predicate($value)) {
      $result[] = $value;
    }
  }
  return $result;
}

/**
 * Returns a new keyset containing only non-null values of the given
 * Traversable.
 *
 * Time complexity: O(n)
 * Space complexity: O(n)
 */
<<__Pure, __AtMostRxAsArgs>>
function filter_nulls<Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<?Tv> $traversable,
): keyset<Tv> {
  $result = keyset[];
  foreach ($traversable as $value) {
    if ($value !== null) {
      $result[] = $value;
    }
  }
  return $result;
}

/**
 * Returns a new keyset containing only the values for which the given predicate
 * returns `true`.
 *
 * If you don't need access to the key, see `Keyset\filter()`.
 *
 * Time complexity: O(n * p), where p is the complexity of `$predicate`
 * Space complexity: O(n)
 */
<<__Pure, __AtMostRxAsArgs>>
function filter_with_key<Tk, Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\KeyedTraversable::class)>>
  KeyedTraversable<Tk, Tv> $traversable,
  <<__AtMostRxAsFunc>>
  (function(Tk, Tv): bool) $predicate,
): keyset<Tv> {
  $result = keyset[];
  foreach ($traversable as $key => $value) {
    if ($predicate($key, $value)) {
      $result[] = $value;
    }
  }
  return $result;
}

/**
 * Returns a new keyset containing the keys of the given KeyedTraversable,
 * maintaining the iteration order.
 */
<<__Pure, __AtMostRxAsArgs>>
function keys<Tk as arraykey, Tv>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\KeyedTraversable::class)>>
  KeyedTraversable<Tk, Tv> $traversable,
): keyset<Tk> {
  $result = keyset[];
  foreach ($traversable as $key => $_) {
    $result[] = $key;
  }
  return $result;
}

/**
 * Returns a new keyset containing only the elements of the first Traversable
 * that appear in all the other ones.
 *
 * Time complexity: O(n + m), where n is size of `$first` and m is the combined
 * size of `$second` plus all the `...$rest`
 * Space complexity: O(n), where n is size of `$first`
 */
<<__Pure, __AtMostRxAsArgs>>
function intersect<Tv as arraykey>(
  <<__OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $first,
  <<__OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $second,
  Container<Tv> ...$rest
): keyset<Tv> {
  /* HH_FIXME[4276] optimized for Containers but others still work overall */
  if (!$first || !$second) {
    return keyset[];
  }
  $intersection = keyset($first);
  $rest[] = $second;
  foreach ($rest as $traversable) {
    $next_intersection = keyset[];
    foreach ($traversable as $value) {
      if (C\contains_key($intersection, $value)) {
        $next_intersection[] = $value;
      }
    }
    if (!$next_intersection) {
      return keyset[];
    }
    $intersection = $next_intersection;
  }
  return $intersection;
}

/**
 * Returns a new keyset containing the first `$n` elements of the given
 * Traversable.
 *
 * If there are duplicate values in the Traversable, the keyset may be shorter
 * than the specified length.
 *
 * To drop the first `$n` elements, see `Keyset\drop()`.
 *
 * Time complexity: O(n), where n is `$n`
 * Space complexity: O(n), where n is `$n`
 */
<<__Pure, __AtMostRxAsArgs>>
function take<Tv as arraykey>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $traversable,
  int $n,
): keyset<Tv> {
  if ($n === 0) {
    return keyset[];
  }
  invariant($n > 0, 'Expected non-negative N, got %d.', $n);
  $result = keyset[];
  $ii = 0;
  foreach ($traversable as $value) {
    $result[] = $value;
    $ii++;
    if ($ii === $n) {
      break;
    }
  }
  return $result;
}
